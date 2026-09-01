<?php

declare(strict_types=1);

namespace Migrations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class User
{
    public static function run()
    {
        DB::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
        });
        DB::schema()->create('article_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_id');
            $table->integer('user_id');
        });
    }
}
