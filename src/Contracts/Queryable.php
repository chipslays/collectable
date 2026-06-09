<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents a collection that can be queried for specific items.
 */
interface Queryable
{
    public function first(?callable $callback = null, mixed $default = null): mixed;

    public function last(?callable $callback = null, mixed $default = null): mixed;

    public function firstOrFail(?callable $callback = null): mixed;

    public function firstWhere(string $path, mixed $operatorOrValue = null, mixed $value = null): mixed;

    public function firstKey(?callable $callback = null): string|int|null;

    public function lastKey(?callable $callback = null): string|int|null;

    public function value(string $path, mixed $default = null): mixed;

    public function after(mixed $valueOrCallback, mixed $default = null): mixed;

    public function before(mixed $valueOrCallback, mixed $default = null): mixed;

    public function search(mixed $valueOrCallback, bool $strict = true): string|int|false;

    public function find(string $path, mixed $value, mixed $default = null): mixed;

    public function sole(string|callable|null $pathOrCallback = null, mixed $value = null): mixed;

    public function hasSole(string|callable|null $pathOrCallback = null, mixed $value = null): bool;
}
