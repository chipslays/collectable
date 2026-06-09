<?php

namespace Collectable\Contracts;

interface Sortable
{
    public function sort(?callable $callback = null): static;

    public function sortDesc(?callable $callback = null): static;

    public function sortBy(string|callable|array $by, bool $desc = false): static;

    public function sortByDesc(string|callable $by): static;

    public function sortKeys(?callable $callback = null): static;

    public function sortKeysDesc(): static;
}
