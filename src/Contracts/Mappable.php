<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents a collection that can transform its items.
 */
interface Mappable
{
    public function map(callable $callback): static;

    public function filterMap(callable $callback): static;

    public function mapWithKeys(callable $callback): static;

    public function mapKeys(callable $callback): static;

    public function mapToGroups(callable $callback): static;

    public function mapSpread(callable $callback): static;

    public function mapInto(string $class): static;

    public function mapBy(string|callable $key): static;

    public function keyBy(string|callable $key): static;

    public function mapPath(string $path, callable $callback): static;

    public function evolve(array $transformers): static;

    public function transform(callable $callback): static;

    public function flatMap(callable $callback): static;

    public function pluck(string $path, ?string $key = null): static;

    public function select(array $fields): static;

    public function scan(callable $callback, mixed $initial = null): static;

    public function reduce(callable $callback, mixed $initial = null): mixed;
}
