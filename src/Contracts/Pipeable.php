<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents a collection that supports pipeline, tap, conditional and
 * iteration helpers.
 */
interface Pipeable
{
    public function each(callable $callback): static;

    public function eachSpread(callable $callback): static;

    public function pipe(callable $callback): mixed;

    public function pipeInto(string $class): mixed;

    public function pipeThrough(array $pipes): mixed;

    public function tap(callable $callback): static;

    public function when(bool|callable $condition, callable $then, ?callable $else = null): static;

    public function unless(bool|callable $condition, callable $then, ?callable $else = null): static;

    public function whenEmpty(callable $then, ?callable $else = null): static;

    public function whenNotEmpty(callable $then, ?callable $else = null): static;

    public function unlessEmpty(callable $then, ?callable $else = null): static;

    public function unlessNotEmpty(callable $then, ?callable $else = null): static;

    public function ensure(string|array $types): static;
}
