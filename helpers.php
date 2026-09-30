<?php

declare(strict_types=1);

use Collectable\Collection;

if (! function_exists('collection')) {
    /**
     * Create a new Collection instance.
     *
     * @see Collectable\Collection
     *
     * @param array array|string|object $items Initial data
     * @param string $wildcard Wildcard segment token (default '*')
     * @param string $delimiter Path delimiter (default '.')
     */
    function collection(
        array|string|object $items = [],
        string $wildcard = '*',
        string $delimiter = '.'
    ): Collection {
        return new Collection($items, $wildcard, $delimiter);
    }
}

