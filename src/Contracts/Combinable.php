<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents a collection that supports set-theory operations
 * (diff, intersect, zip, combine, etc.).
 */
interface Combinable
{
    public function merge(array|Arrayable $items): static;

    public function mergeRecursive(array|Arrayable $items): static;

    public function replace(array|Arrayable $items): static;

    public function replaceRecursive(array|Arrayable $items): static;

    public function concat(array|Arrayable $items): static;

    public function union(array|Arrayable $items): static;

    public function diff(array|Arrayable $items): static;

    public function diffAssoc(array|Arrayable $items): static;

    public function diffKeys(array|Arrayable $items): static;

    public function diffUsing(array|Arrayable $items, callable $comparator): static;

    public function intersect(array|Arrayable $items): static;

    public function intersectByKeys(array|Arrayable $items): static;

    public function intersectAssoc(array|Arrayable $items): static;

    public function intersectUsing(array|Arrayable $items, callable $comparator): static;

    public function symmetricDiff(array|Arrayable $items): static;

    public function zip(array ...$arrays): static;

    public function unzip(): array;

    public function crossJoin(array ...$arrays): static;

    public function combine(array|Arrayable $values): static;
}
