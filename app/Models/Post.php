<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @mixin EloquentBuilder
 * @mixin QueryBuilder
 */
class Post extends Model
{
    use HasFactory;

    protected $guarded = []; // all fields are mass-writable
    public $timestamps = false; // we manage timestamps manually
    protected $primaryKey = 'id';

}
