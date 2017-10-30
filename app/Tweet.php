<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['status'];
}
