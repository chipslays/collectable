<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents a collection that can be filtered and searched.
 */
interface Filterable
{
    public function filter(string|callable $pathOrCallback, mixed $value = null): static;

    public function reject(string|callable $pathOrCallback, mixed $value = null): static;

    public function where(string $path, mixed $operatorOrValue = null, mixed $value = null): static;

    public function whereIn(string $path, array $values, bool $strict = true): static;

    public function whereNotIn(string $path, array $values, bool $strict = true): static;

    public function whereNull(?string $path = null): static;

    public function whereNotNull(?string $path = null): static;

    public function whereStrict(string $path, mixed $value): static;

    public function whereInStrict(string $path, array $values): static;

    public function whereNotInStrict(string $path, array $values): static;

    public function whereBetween(string $path, array $values): static;

    public function whereNotBetween(string $path, array $values): static;

    public function whereInstanceOf(string|array $class, ?string $path = null): static;

    public function whereContains(string $path, string $needle, bool $ignoreCase = false): static;

    public function whereStartsWith(string $path, string $prefix, bool $ignoreCase = false): static;

    public function whereEndsWith(string $path, string $suffix, bool $ignoreCase = false): static;

    public function whereMatches(string $path, string $regex): static;

    public function unique(string|callable|null $by = null): static;

    public function uniqueStrict(string|callable|null $by = null): static;

    public function duplicates(string|callable|null $by = null): static;

    public function duplicatesStrict(string|callable|null $by = null): static;
}
