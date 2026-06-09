<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents a collection that supports slicing, pagination and windowing.
 */
interface Sliceable
{
    public function slice(int $offset, ?int $length = null): static;

    public function take(int $n): static;

    public function skip(int $n): static;

    public function forPage(int $page, int $perPage): static;

    public function paginate(int $perPage, int $page = 1): array;

    public function chunk(int $size): static;

    public function chunkWhile(callable $callback): static;

    public function sliding(int $size, int $step = 1): static;

    public function split(int $n): static;

    public function splitIn(int $n): static;

    public function nth(int $step, int $offset = 0): static;

    public function pad(int $size, mixed $value = null): static;

    public function takeUntil(mixed $valueOrCallback): static;

    public function takeWhile(mixed $valueOrCallback): static;

    public function skipUntil(mixed $valueOrCallback): static;

    public function skipWhile(mixed $valueOrCallback): static;
}
