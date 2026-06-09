# Collection

A flexible PHP collection with dot-notation and wildcard path support.

---

Native PHP array functions are a mess — `array_map`, `array_filter`, `usort`, `array_walk` all have inconsistent argument order, most of them don't chain, and processing nested data means writing loops inside loops. Laravel's Collection solves this elegantly, but drags the entire framework with it. Collection is a standalone, zero-dependency package that works in any PHP project.

---

## Features

- **Dot-notation & wildcards.**
  - Access, write, filter, and remove deeply nested data with paths like `users.*.emails.*.address` — no more chaining `foreach` or unpacking nested arrays by hand. Wildcards work across `get`, `set`, `remove`, and most other methods, and you can key results by any field with `*[id]` syntax.
- **100+ methods, one fluent interface.**
  - Filtering (`where`, `whereIn`, `whereBetween`, `whereContains`, `whereMatches`), transformation (`map`, `evolve`, `mapPath`, `flatMap`, `scan`), aggregates (`sum`, `avg`, `median`, `standardDeviation`, `percentage`), multi-column sorting, pagination, set operations (`diff`, `intersect`, `zip`, `crossJoin`, `symmetricDiff`) — everything chains, everything returns a collection.
- **Contracts for every concern.**
  - Fourteen focused interfaces — `Filterable`, `Sortable`, `Aggregatable`, `Sliceable` and more — let you type-hint against exactly what a function needs instead of depending on the concrete class. Better for testing, better for boundaries, better for static analysis.
- **Explicit about mutation.**
  - Transforming methods return a new collection. Mutating methods (`push`, `pop`, `splice`, `transform`) are named and documented to make it obvious when you're changing state in place.
- **Pipelines built in.**
  - `pipe`, `pipeThrough`, `tap`, `when`, `unless`, `whenEmpty` let you build readable processing chains without intermediate variables or broken-up logic.
- **Macros.**
  - Add your own methods to every collection at runtime — no subclassing required.
- **ArrayAccess with dot-notation.**
  - `$c['user.address.city']` just works.

---

## Installation

Install package via Composer:

```bash
composer require collectable/collection
```

## Usage

```php
use Collectable\Collection;

$helloWorld = new Collection(['hello', 'world'])
    ->map(fn($word) => strtoupper($word))
    ->join(' '); // "HELLO WORLD"
```

You also can use helper function `collection()`:

```php
$helloWorld = collection(...)->map(...)->join(...);
```

---

## Table of Contents

- [Creating a Collection](#creating-a-collection)
- [Reading & Writing Data](#reading--writing-data)
- [Checking Existence](#checking-existence)
- [Finding Items](#finding-items)
- [Filtering](#filtering)
- [Mapping & Transforming](#mapping--transforming)
- [Sorting](#sorting)
- [Grouping & Keying](#grouping--keying)
- [Aggregates](#aggregates)
- [Slicing & Pagination](#slicing--pagination)
- [Set Operations](#set-operations)
- [Iteration & Pipelines](#iteration--pipelines)
- [Mutation](#mutation)
- [Serialization](#serialization)
- [Debugging](#debugging)
- [Dot-Notation & Wildcards](#dot-notation--wildcards)
- [Wildcard with a custom key](#wildcard-with-a-custom-key-field)
- [ArrayAccess Support](#arrayaccess-support)
- [Contracts](#contracts)
- [Macros](#macros)

---

## Creating a Collection

### `make(array $items, string $wildcard = '*', string $delimiter = '.')`
Create a collection from an array.
```php
$c = Collection::make(['apple', 'banana', 'cherry']);
```

### `wrap(mixed $value)`
Wrap any value into a collection. If it is already a collection it is returned as-is.
```php
Collection::wrap('hello');       // ['hello']
Collection::wrap([1, 2, 3]);    // [1, 2, 3]
Collection::wrap($collection);  // same collection
```

### `unwrap(mixed $value)`
Convert a collection (or array) back to a plain PHP array.
```php
Collection::unwrap($collection); // plain array
Collection::unwrap([1, 2, 3]);  // [1, 2, 3]
```

### `times(int $n, callable $callback)`
Generate a collection of `$n` items produced by a callback (index starts at 1).
```php
Collection::times(5, fn($i) => $i * 2);
// [2, 4, 6, 8, 10]
```

### `range(int|float $from, $to, $step = 1)`
Create a collection of numbers in a range.
```php
Collection::range(1, 5);           // [1, 2, 3, 4, 5]
Collection::range(0, 1, 0.25);    // [0, 0.25, 0.5, 0.75, 1.0]
```

### `fromJson(string $json)`
Create a collection from a JSON string.
```php
$c = Collection::fromJson('{"name":"Alice","age":30}');
$c->get('name'); // 'Alice'
```

---

## Reading & Writing Data

### `get(?string $path, mixed $default = null)`
Retrieve a value by dot-notation path. Supports wildcards.
```php
$c = Collection::make(['user' => ['name' => 'Alice', 'age' => 30]]);

$c->get('user.name');           // 'Alice'
$c->get('user.email', 'n/a'); // 'n/a'  (default)
```

### `set(string $path, mixed $value)`
Set a value at a dot-notation path. Creates intermediate arrays as needed.
```php
$c->set('user.email', 'alice@example.com');
$c->set('user.address.city', 'Moscow');
```

### `put(string $path, mixed $value)`
Alias for `set()`.
```php
$c->put('name', 'Bob');
```

### `getOrPut(string $path, mixed $default)`
Return the value if it exists; otherwise store and return `$default`.
`$default` can be a callable — it is only called when the key is missing.
```php
$token = $c->getOrPut('cache.token', fn() => bin2hex(random_bytes(16)));
```

### `pull(string $path, mixed $default = null)`
Retrieve the value and then remove it from the collection.
```php
$token = $c->pull('user.token'); // gets value and removes the key
```

### `remove(string $path)` / `forget(string $path)`
Remove an item by dot-notation path. Both methods are identical.
```php
$c->remove('user.password');
$c->forget('user.token');
```

### `all()` / `toArray()`
Return all items as a plain PHP array.
```php
$array = $c->all();
```

### `keys(?string $path = null)`
Return all top-level keys, or keys of the array at a given path.
```php
$c->keys();          // ['user', 'meta']
$c->keys('user');    // ['name', 'age']
```

### `values()`
Return a new collection with consecutive integer keys (re-indexed).
```php
Collection::make(['a' => 1, 'b' => 2])->values()->all();
// [1, 2]
```

### `collect(?string $path)`
Retrieve a path and wrap the result in a new collection — useful for chaining.
```php
$c->collect('users')->where('active', true)->pluck('name');
```

---

## Checking Existence

### `has(string $path)`
Check whether a path exists and the value is **not null**.
```php
$c->has('user.name');       // true
$c->has('user.nickname');   // false
```

### `hasKey(string $path)`
Check whether a path exists (even if the value is `null`).
```php
$c->hasKey('user.deleted_at'); // true even when value is null
```

### `hasAny(string ...$paths)`
Return `true` if **at least one** of the given paths exists.
```php
$c->hasAny('email', 'phone'); // true if either exists
```

### `hasAll(string ...$paths)`
Return `true` only if **all** of the given paths exist.
```php
$c->hasAll('id', 'name', 'email');
```

### `isEmpty()` / `isNotEmpty()`
Check whether the collection has no items / has at least one item.
```php
$c->isEmpty();    // false
$c->isNotEmpty(); // true
```

### `isList()`
Check whether the items form a plain indexed array (0, 1, 2, …).
```php
Collection::make([1, 2, 3])->isList();           // true
Collection::make(['a' => 1, 'b' => 2])->isList(); // false
```

### `isAssoc()`
Check whether the collection is an associative array (not a list).
```php
Collection::make(['a' => 1])->isAssoc(); // true
```

### `count(?string $path = null)`
Count top-level items, or items at a given path.
```php
$c->count();          // number of top-level items
$c->count('users');   // count of the 'users' array
```

---

## Finding Items

### `first(?callable $callback, mixed $default = null)`
Return the first item, or the first item satisfying a callback.
```php
$c->first();                            // first item
$c->first(fn($u) => $u['active']);      // first active user
$c->first(fn($u) => $u['age'] > 18, 'guest'); // with default
```

### `last(?callable $callback, mixed $default = null)`
Return the last item, or the last item satisfying a callback.
```php
$c->last();
$c->last(fn($u) => $u['active']);
```

### `firstOrFail(?callable $callback)`
Return the first matching item or throw `\RuntimeException` if none found.
```php
$user = $users->firstOrFail(fn($u) => $u['id'] === 42);
```

### `firstWhere(string $path, $operatorOrValue, $value = null)`
Return the first item where the value at `$path` matches.
```php
$users->firstWhere('role', 'admin');
$users->firstWhere('age', '>=', 18);
```

### `firstKey(?callable $callback)` / `lastKey(?callable $callback)`
Return the key of the first (or last) matching item.
```php
$c->firstKey();                           // key of first item
$c->firstKey(fn($v) => $v['active']);     // key of first active item
```

### `value(string $path, mixed $default = null)`
Get the value at `$path` from the **first** item in the collection.
```php
$users->value('email');      // email of the first user
```

### `find(string $path, mixed $value, mixed $default = null)`
Return the first item where `$path === $value`.
```php
$users->find('id', 42);
$users->find('email', 'alice@example.com');
```

### `search(mixed $valueOrCallback)`
Return the key of the first matching item, or `false`.
```php
$c->search('Alice');
$c->search(fn($v) => $v['active']);
```

### `sole(string|callable|null $pathOrCallback = null, mixed $value = null)`
Return the single matching item or throw if there are zero or multiple matches.
```php
$user = $users->sole('id', 42);
```

### `hasSole(...)`
Return `true` if exactly one item matches (boolean variant of `sole()`).
```php
$users->hasSole('role', 'superadmin');
```

### `after(mixed $valueOrCallback, mixed $default = null)`
Return the item immediately **after** the given value.
```php
Collection::make([1, 2, 3, 4])->after(2); // 3
```

### `before(mixed $valueOrCallback, mixed $default = null)`
Return the item immediately **before** the given value.
```php
Collection::make([1, 2, 3, 4])->before(3); // 2
```

---

## Filtering

### `filter(string|callable $pathOrCallback, mixed $value = null)`
Keep only items that satisfy a callback or a path condition.
```php
$c->filter(fn($u) => $u['active']);
$c->filter('active');            // items where active is truthy
$c->filter('role', 'admin');    // items where role === 'admin'
```

### `reject(string|callable $pathOrCallback, mixed $value = null)`
Keep items that do **not** satisfy the condition (inverse of `filter()`).
```php
$c->reject('active');
$c->reject('role', 'guest');
```

### `where(string $path, $operatorOrValue, $value = null)`
Filter by a dot-notation path with an optional comparison operator.

Supported operators: `=`, `==`, `===`, `!=`, `!==`, `<>`, `>`, `>=`, `<`, `<=`
```php
$users->where('active');
$users->where('role', 'admin');          // loose ==
$users->where('age', '>=', 18);
$users->where('status', '===', 1);      // strict
```

### `whereIn(string $path, array $values)` / `whereNotIn(string $path, array $values)`
Filter by whether the path value is (or is not) in a list.
```php
$users->whereIn('role', ['admin', 'editor']);
$users->whereNotIn('status', ['banned', 'inactive']);
```

### `whereNull(?string $path)` / `whereNotNull(?string $path)`
Filter items where the path value is (or is not) `null`.
```php
$users->whereNull('deleted_at');     // soft-deleted
$users->whereNotNull('deleted_at'); // active
```

### `whereBetween(string $path, array $values)` / `whereNotBetween(...)`
Filter by an inclusive range `[$min, $max]`.
```php
$products->whereBetween('price', [10, 100]);
$users->whereNotBetween('age', [18, 65]);
```

### `whereContains(string $path, string $needle, bool $ignoreCase = false)`
Filter items where the string value at `$path` contains `$needle`.
```php
$users->whereContains('name', 'ali');
$users->whereContains('email', '@gmail', true); // case-insensitive
```

### `whereStartsWith(string $path, string $prefix, bool $ignoreCase = false)`
Filter items where the string value starts with a prefix.
```php
$users->whereStartsWith('name', 'Al');
```

### `whereEndsWith(string $path, string $suffix, bool $ignoreCase = false)`
Filter items where the string value ends with a suffix.
```php
$users->whereEndsWith('email', '@gmail.com');
```

### `whereMatches(string $path, string $regex)`
Filter items where the string value matches a regular expression.
```php
$users->whereMatches('code', '/^[A-Z]{2}\d{3}$/');
```

### `whereInstanceOf(string|array $class, ?string $path = null)`
Filter items by class or interface.
```php
$items->whereInstanceOf(User::class);
$items->whereInstanceOf([Admin::class, Moderator::class]);
```

### `whereStrict(string $path, mixed $value)`
Strict `===` version of `where()`.
```php
$users->whereStrict('status', 1); // int 1 only, not '1'
```

### `unique(string|callable|null $by = null)` / `uniqueStrict(...)`
Remove duplicate items. Optionally group by a path or callback.
```php
$c->unique();
$users->unique('email');
$users->unique(fn($u) => $u['domain']);
```

### `duplicates(string|callable|null $by = null)` / `duplicatesStrict(...)`
Return only the duplicate items.
```php
$users->duplicates('email'); // users with a repeated email
```

---

## Mapping & Transforming

### `map(callable $callback)`
Apply a callback to every item. Returns a **new** collection.
```php
$prices = $c->map(fn($item) => $item['price'] * 1.2);
```

### `filterMap(callable $callback)`
Map and discard `null`/`false` results in a single pass.
```php
$emails = $users->filterMap(fn($u) => $u['active'] ? $u['email'] : null);
```

### `mapWithKeys(callable $callback)`
Rebuild the collection as key -> value pairs.
```php
$lookup = $users->mapWithKeys(fn($u) => [$u['id'] => $u['name']]);
// [1 => 'Alice', 2 => 'Bob']
```

### `mapKeys(callable $callback)`
Transform only the keys, keeping values unchanged.
```php
$c->mapKeys(fn($k) => strtoupper($k));
```

### `mapToGroups(callable $callback)`
Map items to `[$key => $value]` pairs, then group by key.
```php
$c->mapToGroups(fn($u) => [$u['department'] => $u['name']]);
```

### `mapSpread(callable $callback)` / `eachSpread(callable $callback)`
Spread each sub-array item as separate arguments.
```php
Collection::make([[1, 2], [3, 4]])->mapSpread(fn($a, $b) => $a + $b);
// [3, 7]
```

### `mapInto(string $class)`
Map items into instances of `$class` (passes each item to the constructor).
```php
$objects = $items->mapInto(ProductDTO::class);
```

### `mapBy(string|callable $key)` / `keyBy(...)`
Re-key the collection by a path or callback (duplicate keys overwrite).
```php
$byId = $users->mapBy('id');
// [1 => [...user1...], 2 => [...user2...]]
```

### `mapPath(string $path, callable $callback)`
Transform only the value at a specific path inside each item.
```php
$c->mapPath('price', fn($p) => round($p * 1.2, 2));
$c->mapPath('meta.slug', 'strtolower');
```

### `evolve(array $transformers)`
Apply multiple path transformations in a single call.
```php
$c->evolve([
    'price'     => fn($p) => round($p * 1.2, 2),
    'meta.slug' => 'strtolower',
    'name'      => 'trim',
]);
```

### `transform(callable $callback)`
Like `map()` but mutates the collection **in-place**.
```php
$c->transform(fn($v) => $v * 2);
```

### `flatMap(callable $callback)`
Map each item and collapse the results one level.
```php
$emails = $users->flatMap(fn($u) => $u['emails']);
```

### `pluck(string $path, ?string $key = null)`
Extract a single field from every item.
```php
$names = $users->pluck('name');
$byId  = $users->pluck('name', 'id'); // [1 => 'Alice', 2 => 'Bob']
```

### `select(array $fields)`
Pick multiple fields from each item, supporting dot-notation paths.
```php
$users->select(['id', 'name', 'user.email']);
```

### `only(array $keys)`
Return a new collection with only the specified top-level keys.
```php
$c->only(['id', 'name']);
```

### `except(array $keys)`
Return a new collection with the specified top-level keys removed.
```php
$c->except(['password', 'token']);
```

### `flatten(int|float $depth = INF)`
Flatten nested arrays into a single indexed array.
```php
Collection::make([[1, [2, 3]], [4]])->flatten();
// [1, 2, 3, 4]

Collection::make([[1, [2, 3]], [4]])->flatten(1);
// [1, [2, 3], 4]
```

### `collapse()`
Collapse one level of nested arrays into a single flat array.
```php
Collection::make([[1, 2], [3, 4]])->collapse()->all();
// [1, 2, 3, 4]
```

### `dot(string $prefix = '')`
Flatten a nested array to dot-notation key -> value pairs.
```php
Collection::make(['user' => ['name' => 'Alice', 'age' => 30]])->dot()->all();
// ['user.name' => 'Alice', 'user.age' => 30]
```

### `undot()`
Convert dot-notation keys back to a nested array.
```php
Collection::make(['user.name' => 'Alice', 'user.age' => 30])->undot()->all();
// ['user' => ['name' => 'Alice', 'age' => 30]]
```

### `flip()`
Swap keys and values.
```php
Collection::make(['a' => 1, 'b' => 2])->flip()->all();
// [1 => 'a', 2 => 'b']
```

### `reverse(bool $preserveKeys = false)`
Return items in reverse order.
```php
Collection::make([1, 2, 3])->reverse()->all(); // [3, 2, 1]
```

### `transpose()`
Transpose a matrix — rows become columns.
```php
Collection::make([[1, 2, 3], [4, 5, 6]])->transpose()->all();
// [[1, 4], [2, 5], [3, 6]]
```

### `scan(callable $callback, mixed $initial = null)`
Running fold — like `reduce()` but returns every intermediate value.
```php
Collection::make([1, 2, 3, 4])->scan(fn($carry, $v) => $carry + $v, 0)->all();
// [1, 3, 6, 10]
```

### `reduce(callable $callback, mixed $initial = null)`
Reduce the collection to a single value.
```php
$total = $c->reduce(fn($carry, $item) => $carry + $item, 0);
```

### `toTree(string $idKey, string $parentKey, string $childrenKey = 'children', $rootId = null)`
Build a nested tree structure from a flat list.
```php
$tree = $categories->toTree('id', 'parent_id');
// Each item gets a 'children' key containing its sub-items
```

---

## Sorting

### `sort(?callable $callback = null)`
Sort items. With no callback uses natural ordering.
```php
$c->sort();
$c->sort(fn($a, $b) => $a <=> $b);
```

### `sortDesc(?callable $callback = null)`
Sort in descending order.
```php
$c->sortDesc();
```

### `sortBy(string|callable|array $by, bool $desc = false)`
Sort items by a dot-notation path, callback, or multiple columns.
```php
$users->sortBy('name');
$users->sortBy('age', desc: true);
$products->sortBy(['category', 'price' => 'desc']);
```

### `sortByDesc(string|callable $by)`
Shorthand for `sortBy($by, desc: true)`.
```php
$products->sortByDesc('price');
```

### `sortKeys(?callable $callback = null)` / `ksort(?callable $callback = null)`
Sort by key.
```php
$c->sortKeys();
```

### `sortKeysDesc()`
Sort by key in descending order.
```php
$c->sortKeysDesc();
```

### `sortKeysUsing(callable $comparator)`
Sort by key using a custom comparator.
```php
$c->sortKeysUsing('strnatcasecmp');
```

---

## Grouping & Keying

### `groupBy(string|callable $by)`
Group items into sub-collections by a path or callback.
```php
$byRole = $users->groupBy('role');
// ['admin' => Collection, 'editor' => Collection]

$users->groupBy(fn($u) => $u['active'] ? 'active' : 'inactive');
```

### `countBy(string|callable|null $by = null)`
Count occurrences per group.
```php
$c->countBy();              // count each unique value
$users->countBy('role');   // ['admin' => 2, 'editor' => 5]
```

### `partition(callable $callback)`
Split the collection into two: passing and failing items.
```php
[$admins, $others] = $users->partition(fn($u) => $u['role'] === 'admin');
```

---

## Aggregates

### `sum(string|callable|null $pathOrCallback = null)`
Sum all items, a plucked path, or a computed value.
```php
$c->sum();
$orders->sum('total');
$orders->sum(fn($o) => $o['price'] * $o['qty']);
```

### `product(string|callable|null $pathOrCallback = null)`
Multiply all items together.
```php
$c->product();
$items->product('qty');
```

### `avg(string|callable|null $pathOrCallback = null)` / `average(...)`
Return the average value.
```php
$scores->avg('score');
```

### `min(string|callable|null $pathOrCallback = null)` / `max(...)`
Return the minimum or maximum value.
```php
$products->min('price');
$products->max('price');
```

### `minBy(string|callable $by)` / `maxBy(string|callable $by)`
Return the **item** with the minimum or maximum value at a path.
```php
$cheapest = $products->minBy('price');  // returns the whole item
$priciest = $products->maxBy('price');
```

### `median(?string $path = null)`
Return the median value.
```php
$scores->median('score');
```

### `mode(?string $path = null)`
Return the most frequently occurring value(s).
```php
$c->mode('score'); // Collection of the most common scores
```

### `standardDeviation(?string $path = null, bool $sample = false)`
Return the population (or sample) standard deviation.
```php
$scores->standardDeviation('score');
$scores->standardDeviation('score', sample: true);
```

### `percentage(callable $callback, int $precision = 2)`
Return the percentage of items passing a test (0–100.0).
```php
$users->percentage(fn($u) => $u['active']); // e.g. 75.50
```

### `contains(mixed $pathOrValueOrCallback, mixed $value = null)`
Check if the collection contains a value, or a path matches a value.
```php
$c->contains('Alice');
$users->contains('role', 'admin');
$c->contains(fn($v) => $v > 10);
```

### `doesntContain(...)` / `some(...)` / `containsStrict(...)`
Variants of `contains()` — inverse, alias, and strict comparison.
```php
$c->doesntContain('admin');
$users->some('role', 'admin');
$users->containsStrict('status', 1);
```

### `every(string|callable $pathOrCallback, mixed $value = null)`
Check if **all** items satisfy a condition.
```php
$users->every(fn($u) => $u['active']);
$users->every('role', 'admin');
```

---

## Slicing & Pagination

### `slice(int $offset, ?int $length = null)`
Return a sub-range of items.
```php
$c->slice(0, 10);  // first 10 items
$c->slice(-3);     // last 3 items
```

### `take(int $n)`
Return the first `$n` (or last, if negative) items.
```php
$c->take(3);   // first 3
$c->take(-2);  // last 2
```

### `skip(int $n)`
Skip the first `$n` items.
```php
$c->skip(2); // [3, 4, 5] from [1, 2, 3, 4, 5]
```

### `forPage(int $page, int $perPage)`
Return items for a specific page (1-based).
```php
$c->forPage(2, 15); // items 16–30
```

### `paginate(int $perPage, int $page = 1)`
Return a pagination envelope with metadata.
```php
$result = $users->paginate(15, 2);
// ['data' => Collection, 'total' => 100, 'per_page' => 15,
//  'current_page' => 2, 'last_page' => 7, 'from' => 16, 'to' => 30]
```

### `chunk(int $size)`
Split the collection into chunks of `$size`. Returns a collection of collections.
```php
$c->chunk(3); // [[1,2,3], [4,5,6], [7]]
```

### `chunkWhile(callable $callback)`
Chunk items into consecutive groups while the condition holds.
```php
Collection::make([1, 2, 3, 7, 8, 11])
    ->chunkWhile(fn($v, $prev) => $v === $prev + 1);
// [[1,2,3], [7,8], [11]]
```

### `sliding(int $size, int $step = 1)`
Create a sliding window over the collection.
```php
Collection::make([1, 2, 3, 4, 5])->sliding(3)->map->all();
// [[1,2,3], [2,3,4], [3,4,5]]
```

### `split(int $n)` / `splitIn(int $n)`
Split into `$n` roughly equal groups.
```php
Collection::make([1, 2, 3, 4, 5])->split(3);
// [[1, 2], [3, 4], [5]]
```

### `nth(int $step, int $offset = 0)`
Return every `$step`-th item.
```php
$c->nth(2);       // 1st, 3rd, 5th …
$c->nth(3, 1);   // 2nd, 5th, 8th …
```

### `pad(int $size, mixed $value = null)`
Pad the collection to `$size` with a value. Negative pads at the start.
```php
Collection::make([1, 2])->pad(5, 0)->all(); // [1, 2, 0, 0, 0]
Collection::make([1, 2])->pad(-5, 0)->all(); // [0, 0, 0, 1, 2]
```

---

## Set Operations

### `merge(array|Collection $items)`
Merge another array or collection (top-level).
```php
$c->merge(['d', 'e']);
```

### `mergeRecursive(array|Collection $items)`
Deep-merge arrays — repeated string keys produce nested arrays.
```php
$c->mergeRecursive(['meta' => ['extra' => true]]);
```

### `replace(array|Collection $items)` / `replaceRecursive(...)`
Replace values using PHP's `array_replace` semantics.
```php
$c->replace([0 => 'x']);
$c->replaceRecursive(['user' => ['name' => 'Bob']]);
```

### `concat(array|Collection $items)`
Append all values, always re-indexing (numeric keys never overwrite).
```php
$c->concat(['d', 'e']);
```

### `union(array|Collection $items)`
Fill in missing keys from `$items` without overwriting existing ones.
```php
$c->union(['a' => 1, 'b' => 99]); // 'b' added only if not present
```

### `diff(array|Collection $items)`
Return items not present in `$items`.
```php
Collection::make([1, 2, 3, 4])->diff([2, 4])->all(); // [1, 3]
```

### `diffAssoc(...)` / `diffKeys(...)`
Diff comparing both key+value or keys only.
```php
$c->diffAssoc(['a' => 1]);
$c->diffKeys(['a' => 'x', 'c' => 'y']);
```

### `diffUsing(array|Collection $items, callable $comparator)`
Diff using a custom comparator function.
```php
$objects->diffUsing($other, fn($a, $b) => $a->id <=> $b->id);
```

### `intersect(array|Collection $items)`
Return items present in **both** collections.
```php
Collection::make([1, 2, 3])->intersect([2, 3, 4])->all(); // [2, 3]
```

### `intersectByKeys(...)` / `intersectAssoc(...)` / `intersectUsing(...)`
Intersection variants by keys, key+value, or custom comparator.

### `symmetricDiff(array|Collection $items)`
Return items present in one collection but not both (A ∖ B) ∪ (B ∖ A).
```php
$old->symmetricDiff($new); // items that were added or removed
```

### `zip(array ...$arrays)`
Pair items from multiple arrays together.
```php
Collection::make([1, 2, 3])->zip(['a', 'b', 'c'])->all();
// [[1,'a'], [2,'b'], [3,'c']]
```

### `unzip()`
Unzip a collection of tuples into separate collections (inverse of `zip()`).
```php
[$numbers, $letters] = Collection::make([[1,'a'],[2,'b']])->unzip();
```

### `crossJoin(array ...$arrays)`
Return the cartesian product.
```php
Collection::make([1, 2])->crossJoin(['a', 'b'])->all();
// [[1,'a'], [1,'b'], [2,'a'], [2,'b']]
```

### `combine(array|Collection $values)`
Use this collection as keys and `$values` as values.
```php
Collection::make(['a', 'b'])->combine([1, 2])->all();
// ['a' => 1, 'b' => 2]
```

### `multiply(int $times)`
Repeat every item `$times` times.
```php
Collection::make([1, 2])->multiply(3)->all(); // [1, 2, 1, 2, 1, 2]
```

### `shuffle()`
Return a new collection with items in random order.
```php
$c->shuffle();
```

### `random(int $n = 1)`
Return `$n` random items. Returns the item directly when `$n === 1`.
```php
$c->random();   // one random item
$c->random(3);  // Collection of 3 random items
```

---

## Iteration & Pipelines

### `each(callable $callback)`
Iterate over every item. Return `false` to stop early.
```php
$c->each(function ($value, $key) {
    echo "$key: $value\n";
});
```

### `eachSpread(callable $callback)`
Iterate by spreading each sub-array as arguments.
```php
Collection::make([['Alice', 30], ['Bob', 25]])
    ->eachSpread(fn($name, $age) => print "$name is $age\n");
```

### `takeUntil(mixed $valueOrCallback)`
Take items until the condition is met.
```php
Collection::make([1, 2, 3, 4, 5])->takeUntil(fn($v) => $v > 3)->all();
// [1, 2, 3]
```

### `takeWhile(mixed $valueOrCallback)`
Take items while the condition holds.
```php
Collection::make([1, 2, 3, 4, 5])->takeWhile(fn($v) => $v < 4)->all();
// [1, 2, 3]
```

### `skipUntil(mixed $valueOrCallback)`
Skip items until the condition is met, then return the rest.
```php
Collection::make([1, 2, 3, 4, 5])->skipUntil(fn($v) => $v >= 3)->all();
// [3, 4, 5]
```

### `skipWhile(mixed $valueOrCallback)`
Skip items while the condition holds, then return the rest.
```php
Collection::make([1, 2, 3, 4, 5])->skipWhile(fn($v) => $v < 3)->all();
// [3, 4, 5]
```

### `pipe(callable $callback)`
Pass the collection to a callback and return whatever the callback returns.
```php
$total = $c->pipe(fn($c) => $c->sum('price'));
```

### `pipeInto(string $class)`
Pass the collection as the first argument to a class constructor.
```php
$presenter = $c->pipeInto(UserPresenter::class);
```

### `pipeThrough(array $pipes)`
Pass the collection through an ordered array of callables.
```php
$result = $c->pipeThrough([
    fn($c) => $c->filter('active'),
    fn($c) => $c->sortBy('name'),
    fn($c) => $c->values(),
]);
```

### `tap(callable $callback)`
Call a callback for side-effects, then return `$this` (stays in chain).
```php
$c->filter('active')
  ->tap(fn($c) => logger()->info('Active users: ' . $c->count()))
  ->sortBy('name');
```

### `when(bool|callable $condition, callable $then, ?callable $else = null)`
Execute `$then` when the condition is truthy.
```php
$c->when($isAdmin, fn($c) => $c->merge($adminItems));
$c->when(fn($c) => $c->isNotEmpty(), fn($c) => $c->sortBy('name'));
```

### `unless(bool|callable $condition, callable $then, ?callable $else = null)`
Execute `$then` when the condition is **falsy** (inverse of `when()`).
```php
$c->unless($c->isEmpty(), fn($c) => $c->sortBy('name'));
```

### `whenEmpty(callable $then)` / `whenNotEmpty(callable $then)`
Conditional execution based on whether the collection is empty.
```php
$c->whenEmpty(fn($c) => $c->push('default'));
$c->whenNotEmpty(fn($c) => $c->sortBy('name'));
```

### `ensure(string|array $types)`
Assert that every item is of the expected type; throw otherwise.
```php
$c->ensure('int');
$c->ensure(['int', 'float']);
$c->ensure(User::class);
```

---

## Mutation

### `push(mixed $value)`
Append a value to the end.
```php
$c->push('new item');
```

### `prepend(mixed $value, mixed $key = null)`
Add a value to the beginning.
```php
$c->prepend(0);
$c->prepend('home', 'first');
```

### `insert(int $index, mixed $value)`
Insert a value at a specific index (non-mutating, returns new collection).
```php
$c->insert(2, 'x');   // inject at position 2
$c->insert(-1, 'x');  // before the last item
```

### `pop()`
Remove and return the last item.
```php
$last = $c->pop();
```

### `shift()`
Remove and return the first item.
```php
$first = $c->shift();
```

### `splice(int $offset, ?int $length = null, array $replacement = [])`
Remove items starting at `$offset`, optionally replacing them.
Mutates the collection and returns the removed items as a new collection.
```php
$removed = $c->splice(2);            // removes from index 2 to end
$removed = $c->splice(1, 2);         // removes 2 items at index 1
$removed = $c->splice(1, 2, ['x']);  // removes 2 and inserts 'x'
```

### `clear()`
Remove all items from the collection.
```php
$c->clear();
```

### `copy()`
Return a deep clone of the collection.
```php
$clone = $c->copy();
```

### `join(string $glue, ?string $finalGlue = null)` / `implode(string $value, ?string $glue = null)`
Join all items into a string.
```php
$c->join(', ');                   // 'Alice, Bob, Carol'
$c->join(', ', ' and ');          // 'Alice, Bob and Carol'

$users->implode('name', ', ');    // pluck 'name' then join
$c->implode(', ');                // join scalar items
```

---

## Serialization

### `toJson(int $flags = 0)` / `toPrettyJson()`
Convert the collection to a JSON string.
```php
$c->toJson();
$c->toPrettyJson();
```

### `fromJson(string $json)` *(static)*
Create a collection from a JSON string.
```php
$c = Collection::fromJson('{"a":1,"b":2}');
```

### `__toString()`
Automatically called when the collection is cast to a string (returns JSON).
```php
echo $c; // prints JSON
```

---

## Debugging

### `dump()`
Dump the collection contents and return `$this` (stays in chain).
```php
$c->filter('active')->dump()->sortBy('name');
```

### `dd()`
Dump the collection and **terminate execution**.
```php
$c->dd();
```

---

## Dot-Notation & Wildcards

The collection understands dot-notation paths for deeply nested data and supports
`*` wildcards to operate on multiple items at once.

```php
$c = Collection::make([
    'users' => [
        ['id' => 1, 'name' => 'Alice', 'role' => 'admin',  'emails' => [['address' => 'alice@a.com']]],
        ['id' => 2, 'name' => 'Bob',   'role' => 'editor', 'emails' => [['address' => 'bob@b.com'], ['address' => 'bob2@b.com']]],
    ],
]);

// Read a single path
$c->get('users.0.name');            // 'Alice'

// Wildcard: get all names (keyed by array index)
$c->get('users.*.name');            // [0 => 'Alice', 1 => 'Bob']

// Nested wildcards: all email addresses
$c->get('users.*.emails.*.address');
// [0 => [0 => 'alice@a.com'], 1 => [0 => 'bob@b.com', 1 => 'bob2@b.com']]

// Set a value on every item
$c->set('users.*.active', true);

// Remove a field from every item
$c->remove('users.*.role');
```

### Wildcard with a custom key: `*[field]`

By default `*` uses the numeric array index as the result key.
You can instruct it to use the value of a specific **field** from each item instead
by writing `*[field]` in the path segment.

```php
// Get names keyed by user id instead of 0, 1, 2 …
$c->get('users.*[id].name');
// [1 => 'Alice', 2 => 'Bob']

// Nested wildcards — outer key comes from the user id
$c->get('users.*[id].emails.*.address');
// [1 => [0 => 'alice@a.com'], 2 => [0 => 'bob@b.com', 1 => 'bob2@b.com']]

```

> If the specified key field is missing from an item, that item falls back to its
> numeric index in the result.
>
> Note: the key field name in `*[field]` must be a simple top-level field (not a
> dot-notation path), because the dot delimiter is also used inside the brackets.

---

## ArrayAccess Support

The collection implements `ArrayAccess`, so you can use bracket syntax with dot-notation paths.

```php
$c['user.name'];                // same as get('user.name')
$c['user.name'] = 'Alice';     // same as set('user.name', 'Alice')
isset($c['user.name']);         // same as has('user.name')
unset($c['user.name']);         // same as remove('user.name')
```

---

## Contracts

Every aspect of `Collection` is described by a focused PHP interface in `Collectable\Contracts`.
You can type-hint against any single interface instead of the concrete class — useful for
testing, custom implementations, and keeping dependencies minimal.

```
Collectable\Contracts
├── Collectable          <- full contract (extends all below)
├── Accessable           <- get / set / has / remove / pull
├── Arrayable            <- all / values / keys / flatten / groupBy …
├── Queryable            <- first / last / find / search / sole …
├── Filterable           <- filter / reject / where* / unique …
├── Mappable             <- map / pluck / reduce / evolve / scan …
├── Aggregatable         <- sum / avg / min / max / every / countBy …
├── Sliceable            <- slice / take / chunk / paginate / sliding …
├── Combinable           <- merge / diff / intersect / zip / crossJoin …
├── Sortable             <- sort / sortBy / sortKeys …
├── Mutable              <- push / pop / shift / splice / insert …
├── Pipeable             <- pipe / tap / when / unless / ensure …
├── Jsonable             <- toJson / fromJson
└── Debuggable           <- dump / dd
```

`Collection` implements `Collectable`, which extends all of the above plus the
standard PHP interfaces (`ArrayAccess`, `Countable`, `IteratorAggregate`, `JsonSerializable`, `Stringable`).

### Type-hinting against a specific interface

Instead of depending on the concrete `Collection` class you can type-hint against
the narrowest interface that covers what you actually need.

```php
use Collectable\Contracts\Aggregatable;
use Collectable\Contracts\Filterable;
use Collectable\Contracts\Sortable;

// This function only needs to filter and sort — no concrete class required.
function getTopUsers(Filterable&Sortable $users, int $limit): array
{
    return $users
        ->where('active', true)
        ->sortByDesc('score')
        ->take($limit)
        ->all();
}

// Works with Collection out of the box:
getTopUsers(Collection::make($data), 10);
```

### Full contract reference

| Interface | Key methods |
|---|---|
| `Accessable` | `get`, `set`, `put`, `has`, `hasKey`, `hasAny`, `hasAll`, `remove`, `forget`, `pull`, `getOrPut` |
| `Arrayable` | `all`, `toArray`, `values`, `keys`, `count`, `isEmpty`, `isList`, `isAssoc`, `flatten`, `collapse`, `dot`, `undot`, `only`, `except`, `flip`, `reverse`, `groupBy`, `partition`, `join`, `implode`, `shuffle`, `random`, `multiply`, `copy`, `transpose`, `toTree` |
| `Queryable` | `first`, `last`, `firstOrFail`, `firstWhere`, `firstKey`, `lastKey`, `value`, `after`, `before`, `search`, `find`, `sole`, `hasSole` |
| `Filterable` | `filter`, `reject`, `where`, `whereIn`, `whereNotIn`, `whereNull`, `whereNotNull`, `whereBetween`, `whereNotBetween`, `whereStrict`, `whereInstanceOf`, `whereContains`, `whereStartsWith`, `whereEndsWith`, `whereMatches`, `unique`, `uniqueStrict`, `duplicates`, `duplicatesStrict` |
| `Mappable` | `map`, `filterMap`, `mapWithKeys`, `mapKeys`, `mapToGroups`, `mapSpread`, `mapInto`, `mapBy`, `keyBy`, `mapPath`, `evolve`, `transform`, `flatMap`, `pluck`, `select`, `scan`, `reduce` |
| `Aggregatable` | `sum`, `product`, `avg`, `average`, `min`, `max`, `minBy`, `maxBy`, `median`, `mode`, `standardDeviation`, `percentage`, `countBy`, `contains`, `doesntContain`, `some`, `containsStrict`, `every` |
| `Sliceable` | `slice`, `take`, `skip`, `forPage`, `paginate`, `chunk`, `chunkWhile`, `sliding`, `split`, `splitIn`, `nth`, `pad`, `takeUntil`, `takeWhile`, `skipUntil`, `skipWhile` |
| `Combinable` | `merge`, `mergeRecursive`, `replace`, `replaceRecursive`, `concat`, `union`, `diff`, `diffAssoc`, `diffKeys`, `diffUsing`, `intersect`, `intersectByKeys`, `intersectAssoc`, `intersectUsing`, `symmetricDiff`, `zip`, `unzip`, `crossJoin`, `combine` |
| `Sortable` | `sort`, `sortDesc`, `sortBy`, `sortByDesc`, `sortKeys`, `sortKeysDesc` |
| `Mutable` | `push`, `prepend`, `insert`, `pop`, `shift`, `splice`, `clear` |
| `Pipeable` | `each`, `eachSpread`, `pipe`, `pipeInto`, `pipeThrough`, `tap`, `when`, `unless`, `whenEmpty`, `whenNotEmpty`, `unlessEmpty`, `unlessNotEmpty`, `ensure` |
| `Jsonable` | `toJson`, `toPrettyJson`, `fromJson` |
| `Debuggable` | `dump`, `dd` |

---

## Macros

You can add your own methods to every collection at runtime via `macro()`.

```php
Collection::macro('toUpperCase', function () {
    return $this->map(fn($v) => strtoupper($v));
});

Collection::make(['hello', 'world'])->toUpperCase()->all();
// ['HELLO', 'WORLD']
```

See `Macroable` [the source code](src/Concerns/Macroable.php) for the full list of methods.
