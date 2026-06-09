<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents an object that can read, write and inspect values
 * via dot-notation and wildcard paths.
 */
interface Accessable
{
    public function get(?string $path = null, mixed $default = null): mixed;

    public function getOrPut(string $path, mixed $default): mixed;

    public function set(string $path, mixed $value = null): static;

    public function put(string $key, mixed $value): static;

    public function pull(string $path, mixed $default = null): mixed;

    public function has(string $path): bool;

    public function hasKey(string $path): bool;

    public function hasAny(string ...$paths): bool;

    public function hasAll(string ...$paths): bool;

    public function remove(string $path): static;

    public function forget(string $path): static;
}
