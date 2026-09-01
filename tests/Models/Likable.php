<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Volosyuk\SimpleEloquent\SimpleEloquent;

class Likable extends Model
{
    use SimpleEloquent;

    protected $table = 'likable';

    protected $guarded = [];
}
