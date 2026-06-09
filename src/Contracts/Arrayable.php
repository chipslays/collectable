<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents an object whose data can be retrieved as a plain PHP array
 * and introspected for shape / structure.
 */
interface Arrayable
{
    public function all(): array;

    public function toArray(): array;

    public function keys(?string $path = null): array;

    public function values(): static;

    public function collect(?string $path = null, mixed $default = null): static;

    public function isEmpty(): bool;

    public function isNotEmpty(): bool;

    public function isList(): bool;

    public function isAssoc(): bool;

    public function count(?string $path = null): int;

    public function flip(): static;

    public function reverse(bool $preserveKeys = false): static;

    public function flatten(int|float $depth = INF): static;

    public function collapse(): static;

    public function dot(string $prefix = ''): static;

    public function undot(): static;

    public function only(array $keys): static;

    public function except(array $keys): static;

    public function groupBy(string|callable $by): static;

    public function partition(callable $callback): array;

    public function join(string $glue = '', ?string $finalGlue = null): string;

    public function implode(string $value, ?string $glue = null): string;

    public function shuffle(): static;

    public function random(int $n = 1): mixed;

    public function multiply(int $times): static;

    public function copy(): static;

    public function transpose(): static;

    public function toTree(
        string $idKey,
        string $parentKey,
        string $childrenKey = 'children',
        mixed $rootId = null,
    ): static;
}


