# Simple Eloquent

[![tests](https://github.com/sineld/simple-eloquent/actions/workflows/tests.yml/badge.svg)](https://github.com/sineld/simple-eloquent/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/sineld/simple-eloquent.svg?style=flat-square)](https://packagist.org/packages/sineld/simple-eloquent)
[![Total Downloads](https://img.shields.io/packagist/dt/sineld/simple-eloquent.svg?style=flat-square)](https://packagist.org/packages/sineld/simple-eloquent)
[![License](https://img.shields.io/github/license/sineld/simple-eloquent.svg?style=flat-square)](LICENSE.txt)

Hydration-free Eloquent queries. Add `simple()` to a query chain and results come back
as plain `stdClass` objects instead of hydrated models — with full relation and
pagination support — cutting query time and memory roughly in half on read-heavy pages.

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

Numbers from the original author's benchmarks:

| Query | `get()` | `simple()->get()` |
| :--- | ---: | ---: |
| 50 users, 3 nested relation trees | 0.62s / 6.0mb | 0.19s / 3.0mb |
| 20 models, 5-level relation | 1.48s / 28.5mb | 0.47s / 15.5mb |
| 1000 models, 2 relations | 0.22s / 2.0mb | 0.06s / 1.1mb |

## Requirements

- PHP 8.2+
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

CI runs the suite on PHP 8.2–8.4 against every supported Laravel release, plus a
weekly run against `laravel/framework@master` to catch next-major breakage months
before it lands.

## Credits

- [Andrey Volosyuk](https://github.com/andreyvolosyuk) — original author
- [Sinan Eldem](https://github.com/sineld) — fork maintainer
- [All contributors](https://github.com/sineld/simple-eloquent/graphs/contributors)

## License

MIT — see [LICENSE.txt](LICENSE.txt). Original copyright belongs to Andrey Volosyuk.
