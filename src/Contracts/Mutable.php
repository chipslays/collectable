<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents a collection that supports mutable stack/queue-like operations.
 */
interface Mutable
{
    public function push(mixed $value): static;

    public function prepend(mixed $value, mixed $key = null): static;

    public function insert(int $index, mixed $value): static;

    public function pop(): mixed;

    public function shift(): mixed;

    public function splice(int $offset, ?int $length = null, array $replacement = []): static;

    public function clear(): static;
}
