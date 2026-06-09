<?php

declare(strict_types=1);

namespace Collectable\Contracts;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Stringable;

/**
 * Full contract for a Collection.
 *
 * Composed from focused, single-responsibility interfaces:
 *
 *  - Arrayable      — shape, structure, and representation
 *  - Accessable     — dot-notation get / set / has / remove
 *  - Queryable      — first, last, find, search, sole …
 *  - Filterable     — filter, reject, where*, unique …
 *  - Mappable       — map, pluck, reduce, evolve …
 *  - Aggregatable   — sum, avg, min, max, contains, every …
 *  - Sliceable      — slice, take, chunk, paginate …
 *  - Combinable     — merge, diff, intersect, zip …
 *  - Sortable       — sort, sortBy, sortKeys …
 *  - Mutable        — push, pop, shift, splice …
 *  - Pipeable       — pipe, tap, when, unless, ensure …
 *  - Jsonable       — toJson, fromJson …
 *  - Debuggable     — dump, dd
 *
 * Plus the standard PHP interfaces:
 *  - ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Stringable
 */
interface Collectable extends
    Arrayable,
    Accessable,
    Queryable,
    Filterable,
    Mappable,
    Aggregatable,
    Sliceable,
    Combinable,
    Sortable,
    Mutable,
    Pipeable,
    Jsonable,
    Debuggable,
    ArrayAccess,
    Countable,
    IteratorAggregate,
    JsonSerializable,
    Stringable
{
    //
}
