<?php

declare(strict_types=1);

namespace Collectable\Contracts;

/**
 * Represents a collection that can be serialised to / from JSON.
 */
interface Jsonable
{
    public function toJson(int $flags = 0): string;

    public function toPrettyJson(): string;

    public static function fromJson(string $json, string $wildcard = '*', string $delimiter = '.'): static;
}
