<?php

declare(strict_types=1);

namespace Migrations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class ArticleCategory
{
    public static function run()
    {
        DB::schema()->create('article_category', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('article_id');
            $table->unsignedInteger('cat_id');
        });
    }
}
