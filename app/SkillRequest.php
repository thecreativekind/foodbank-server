<?php

namespace App;

use App\Bank;
use Illuminate\Database\Eloquent\Model;

class SkillRequest extends Model
{
    protected $fillable = ['bank_id', 'data'];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
