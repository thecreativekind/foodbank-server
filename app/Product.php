<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    public function banks()
    {
        return $this->belongsToMany(Bank::class)->withTimestamps();
    }
}
