<?php

declare(strict_types=1);

/*
 * Reproduces the numbers in the README's "Real-world scenarios" section.
 *
 *     composer install && php benchmarks/bench.php
 *
 * Runs against an in-memory SQLite database seeded with 10,000 articles in 20
 * categories, so the comparison isolates hydration cost — the only thing this
 * package changes.
 */

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use Migrations\Migrator;

$database = new DB;
$database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
$database->bootEloquent();
$database->setAsGlobal();
Migrator::run();

const CATEGORIES = 20;
const ARTICLES = 10000;

foreach (range(1, CATEGORIES) as $i) {
    DB::table('categories')->insert([
        'id' => $i,
        'name' => 'Category '.$i,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}

foreach (array_chunk(range(1, ARTICLES), 500) as $chunk) {
    DB::table('articles')->insert(array_map(fn ($i) => [
        'id' => $i,
        'title' => 'Article title number '.$i,
        'category_id' => ($i % CATEGORIES) + 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ], $chunk));
}

function medianMs(callable $fn, int $iterations = 5): float
{
    $fn();
    $times = [];

    foreach (range(1, $iterations) as $ignored) {
        $start = hrtime(true);
        $result = $fn();
        $times[] = (hrtime(true) - $start) / 1e6;
        unset($result);
        gc_collect_cycles();
    }

    sort($times);

    return $times[intdiv(count($times), 2)];
}

function memoryMb(callable $fn): float
{
    gc_collect_cycles();
    $before = memory_get_usage();
    $result = $fn();
    $used = (memory_get_usage() - $before) / 1048576;
    unset($result);
    gc_collect_cycles();

    return $used;
}

$scenarios = [
    'index page: 50 articles + category, paginated' => [
        fn () => Article::with('category')->paginate(50),
        fn () => Article::simple()->with('category')->paginate(50),
    ],
    'API listing: 1,000 articles + category' => [
        fn () => Article::with('category')->limit(1000)->get(),
        fn () => Article::simple()->with('category')->limit(1000)->get(),
    ],
    'export: 10,000 articles + category' => [
        fn () => Article::with('category')->get(),
        fn () => Article::simple()->with('category')->get(),
    ],
    'dashboard feed: categories + articles (hasMany)' => [
        fn () => Category::with('articles')->get(),
        fn () => Category::simple()->with('articles')->get(),
    ],
];

printf("%-55s %12s %12s %9s\n", 'scenario', 'eloquent', 'simple()', 'speedup');

foreach ($scenarios as $label => [$eloquent, $simple]) {
    $eloquentMs = medianMs($eloquent);
    $simpleMs = medianMs($simple);
    printf("%-55s %9.1f ms %9.1f ms %8.1fx\n", $label, $eloquentMs, $simpleMs, $eloquentMs / $simpleMs);

    $eloquentMb = memoryMb($eloquent);
    $simpleMb = memoryMb($simple);
    printf("%-55s %9.1f mb %9.1f mb %8.1fx\n", '  memory', $eloquentMb, $simpleMb, $simpleMb > 0.0 ? $eloquentMb / $simpleMb : 0.0);
}
