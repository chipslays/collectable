<?php

declare(strict_types=1);

namespace Collectable\Concerns;

use BadMethodCallException;
use Closure;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

/**
 * Adds run-time macro (extension) support to any class.
 *
 * Usage:
 *   Collection::macro('toUpper', fn() => $this->map(strtoupper(...)));
 *   Collection::mixin(new MyCollectionMixin());
 *   Collection::hasMacro('toUpper');   // true
 *   Collection::flushMacros();
 *
 * Macro registry is isolated per class, so subclasses never share
 * macros with their parents unless explicitly registered.
 */
trait Macroable
{
    /**
     * Per-class macro registry.
     * Keyed by class-string to prevent inheritance bleed.
     *
     * @var array<class-string, array<string, callable>>
     */
    private static array $macros = [];

    /**
     * Returns a reference to the current class's macro registry.
     *
     * @return array<string, callable>
     */
    private static function &macroRegistry(): array
    {
        // self:: (not static::) ensures one physical property slot in the trait.
        // static::class isolates entries per concrete class.
        self::$macros[static::class] ??= [];

        return self::$macros[static::class];
    }

    /**
     * Register a custom macro (extension method) on the class.
     *
     * @param-closure-this static $macro
     *
     * @example Collection::macro('toUpper', fn() => $this->map(strtoupper(...)));
     */
    public static function macro(string $name, callable|object $macro): void
    {
        self::macroRegistry()[$name] = $macro;
    }

    /**
     * Mix all public/protected methods of $mixin into the class as macros.
     *
     * Each qualifying method on $mixin must return a Closure — that Closure
     * becomes the macro body. Magic methods (__construct etc.) are skipped.
     *
     * @param bool $replace Whether to overwrite already-registered macros.
     *
     * @throws \ReflectionException
     * @throws UnexpectedValueException if a mixin method doesn't return a Closure
     *
     * @example Collection::mixin(new MyCollectionMixin());
     */
    public static function mixin(object $mixin, bool $replace = true): void
    {
        $methods = (new ReflectionClass($mixin))->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            // Skip magic methods and methods inherited from parent classes
            if (
                str_starts_with($method->getName(), '__')
                || $method->getDeclaringClass()->getName() !== $mixin::class
            ) {
                continue;
            }

            if (!$replace && static::hasMacro($method->getName())) {
                continue;
            }

            // Required for protected methods on PHP < 8.1
            $method->setAccessible(true);
            $macro = $method->invoke($mixin);

            if (!$macro instanceof Closure) {
                throw new UnexpectedValueException(sprintf(
                    'Mixin method %s::%s must return a Closure, got %s.',
                    $mixin::class,
                    $method->getName(),
                    get_debug_type($macro),
                ));
            }

            static::macro($method->getName(), $macro);
        }
    }

    /**
     * Check whether a macro with the given name has been registered.
     */
    public static function hasMacro(string $name): bool
    {
        return array_key_exists($name, self::macroRegistry());
    }

    /**
     * Remove all registered macros for the current class.
     */
    public static function flushMacros(): void
    {
        self::$macros[static::class] = [];
    }

    /**
     * Handle calls to registered macros on an instance.
     *
     * Closures are automatically bound to $this so they can access
     * collection internals like a regular method.
     * Static closures (static fn() => ...) are bound without $this.
     *
     * @throws BadMethodCallException when the macro does not exist
     */
    public function __call(string $method, array $parameters): mixed
    {
        if (!static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.',
                static::class,
                $method,
            ));
        }

        $macro = self::macroRegistry()[$method];

        if ($macro instanceof Closure) {
            try {
                // bindTo returns null for static closures — treat as error
                $macro = $macro->bindTo($this, static::class)
                    ?? throw new RuntimeException();
            } catch (Throwable) {
                // Fallback: bind scope only, no $this (static closure)
                $macro = $macro->bindTo(null, static::class);
            }
        }

        return $macro(...$parameters);
    }

    /**
     * Handle static calls to registered macros.
     *
     * @throws BadMethodCallException when the macro does not exist
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        if (!static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.',
                static::class,
                $method,
            ));
        }

        $macro = self::macroRegistry()[$method];

        if ($macro instanceof Closure) {
            try {
                $macro = $macro->bindTo(null, static::class)
                    ?? throw new RuntimeException();
            } catch (Throwable) {
                // Closure refuses binding entirely — call as-is
                // (e.g. closures from readonly classes in future PHP)
            }
        }

        return $macro(...$parameters);
    }
}
