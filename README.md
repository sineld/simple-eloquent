# Simple Eloquent

[![tests](https://github.com/sineld/simple-eloquent/actions/workflows/tests.yml/badge.svg)](https://github.com/sineld/simple-eloquent/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/sineld/simple-eloquent.svg?style=flat-square)](https://packagist.org/packages/sineld/simple-eloquent)
[![Total Downloads](https://img.shields.io/packagist/dt/sineld/simple-eloquent.svg?style=flat-square)](https://packagist.org/packages/sineld/simple-eloquent)
[![License](https://img.shields.io/github/license/sineld/simple-eloquent.svg?style=flat-square)](LICENSE.txt)

Hydration-free Eloquent queries. Add `simple()` to a query chain and results come back
as plain `stdClass` objects instead of hydrated models — with full relation and
pagination support. On read-heavy queries that is [4–12x faster with up to 2.5x less
memory](#real-world-scenarios), measured, not estimated.

> **Fork notice.** This is an actively maintained fork of
> [volosyuk/simple-eloquent](https://github.com/andreyvolosyuk/simple-eloquent) by
> **Andrey Volosyuk**, who designed and wrote the original package. The fork exists to
> ship support for new Laravel majors on release day (constraints already allow the
> next major, and CI tests weekly against the framework's master branch), along with
> modern tooling: PHPUnit 11, PHPStan, Pint, GitHub Actions.

## When to use it

Eloquent hydration is pure overhead when a page only *displays* data: index tables,
dashboards, exports, API listings. `simple()` skips model instantiation, casts,
accessors and events, returning raw attributes with relations attached. Keep using
plain Eloquent whenever you need mutators, casting, or to call methods on the model.

## Real-world scenarios

Every listing screen in a typical app is a hydration hotspot. The same query, one
`simple()` call apart:

**The admin index table** — 50 rows, a relation, pagination. The bread and butter
of every back office:

```php
// 0.7 ms → 0.2 ms, 4x faster
$articles = Article::with('category')->simple()->paginate(50);
```

**The JSON API endpoint** — a mobile app asks for 1,000 records; nobody will ever
call `save()` on them:

```php
// 8.3 ms → 0.9 ms, 9x faster, 2.5x less memory
return Article::with('category')->limit(1000)->simple()->get();
```

**The CSV/Excel export** — 10,000 rows streamed to a file. Hydrating models here
buys you nothing but a memory spike:

```php
// 82 ms → 8 ms, 10x faster, 15 MB → 6 MB
$rows = Article::with('category')->simple()->get();
```

**The dashboard widget** — categories with their articles via `hasMany`, rebuilt
on every page view:

```php
// 66 ms → 5 ms, 12x faster, half the memory
$feed = Category::with('articles')->simple()->get();
```

Measured on PHP 8.4 with an in-memory SQLite database (10,000 articles, 20
categories), median of 5 runs — so the numbers isolate exactly what this package
removes: hydration cost. Your absolute totals will include real query time on top,
but the saved milliseconds and megabytes come with you. Reproduce them yourself:

```bash
composer install && php benchmarks/bench.php
```

## Requirements

- PHP 8.2 – 8.5 (the upcoming PHP release is covered by a nightly CI job)
- Laravel 12 or 13 (constraints already allow 14 — it installs the day it ships)

## Installation

```bash
composer require sineld/simple-eloquent
```

## Usage

Add the trait to your model. For eager-loaded relations, add it to the related
models too:

```php
use Volosyuk\SimpleEloquent\SimpleEloquent;

class Department extends Model
{
    use SimpleEloquent;
}
```

Then put `simple()` anywhere in the query chain:

```php
$users = User::whereHas('units')
    ->with('units')
    ->withCount('units')
    ->limit(10)
    ->simple()
    ->get(); // Collection of stdClass, relations attached

$activeUser = User::simple()->where('is_active', 1)->first();

$page = Article::simple()->latest()->paginate(25);
```

After `simple()`, the familiar terminal methods return plain objects instead of
models: `get`, `first`, `firstOrFail`, `find`, `findOrFail`, `findMany`, `paginate`,
`simplePaginate`. The explicit variants (`getSimple()`, `firstSimple()`,
`findSimple()`, `paginateSimple()`, `allSimple()`, …) also exist if you prefer not to
toggle the builder.

Both `PDO::FETCH_OBJ` and `PDO::FETCH_ASSOC` fetch modes are supported — results
follow your connection's fetch mode.

### What you give up

Results are `stdClass` objects (or arrays), not models: no casts, no accessors or
appends, no `save()`/`update()`, no model events. That is exactly where the speed
comes from. Reach for it on read paths, not write paths.

## Upgrading from volosyuk/simple-eloquent

The namespace is unchanged (`Volosyuk\SimpleEloquent`), so the swap is only a
composer change — no code edits:

```bash
composer remove volosyuk/simple-eloquent
composer require sineld/simple-eloquent
```

## Development

```bash
composer test    # PHPUnit
composer lint    # Pint (check)
composer stan    # PHPStan level 5
composer check   # all of the above
```

CI runs the suite on PHP 8.2–8.5 against every supported Laravel release, plus a
weekly run against `laravel/framework@master` and PHP nightly to catch next-major
breakage months before it lands.

## Credits

- [Andrey Volosyuk](https://github.com/andreyvolosyuk) — original author
- [Sinan Eldem](https://github.com/sineld) — fork maintainer
- [All contributors](https://github.com/sineld/simple-eloquent/graphs/contributors)

## License

MIT — see [LICENSE.txt](LICENSE.txt). Original copyright belongs to Andrey Volosyuk.
