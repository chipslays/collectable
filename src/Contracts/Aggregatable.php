<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents a collection that provides aggregate / statistical operations.
 */
interface Aggregatable
{
    public function sum(string|callable|null $pathOrCallback = null): int|float;

    public function product(string|callable|null $pathOrCallback = null): int|float;

    public function avg(string|callable|null $pathOrCallback = null): float|null;

    public function average(string|callable|null $pathOrCallback = null): float|null;

    public function min(string|callable|null $pathOrCallback = null): mixed;

    public function max(string|callable|null $pathOrCallback = null): mixed;

    public function minBy(string|callable $by): mixed;

    public function maxBy(string|callable $by): mixed;

    public function median(?string $path = null): float|null;

    public function mode(?string $path = null): static;

    public function standardDeviation(?string $path = null, bool $sample = false): float|null;

    public function percentage(callable $callback, int $precision = 2): float;

    public function countBy(string|callable|null $by = null): static;

    public function contains(mixed $pathOrValueOrCallback, mixed $value = null): bool;

    public function doesntContain(mixed $pathOrValueOrCallback, mixed $value = null): bool;

    public function doesntContainStrict(string $path, mixed $value): bool;

    public function some(mixed $pathOrValueOrCallback, mixed $value = null): bool;

    public function containsStrict(string $path, mixed $value): bool;

    public function every(string|callable $pathOrCallback, mixed $value = null): bool;
}
