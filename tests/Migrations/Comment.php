<?php

declare(strict_types=1);

namespace Migrations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Comment
{
    public static function run()
    {
        DB::schema()->create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('article_id');
            $table->string('body');
        });
    }
}
