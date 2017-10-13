<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'town', 'products', 'url', 'facebook', 'twitter',
        'address', 'add1', 'add2', 'add3', 'town', 'county', 'postcode', 'latitude', 'longitude',
    ];

    public function products()
    {
        return $this->hasOne(Product::class);
    }
}
