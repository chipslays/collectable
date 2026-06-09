<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents a collection that supports debugging helpers.
 */
interface Debuggable
{
    public function dump(): static;

    public function dd(): never;
}
