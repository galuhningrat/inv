<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['unit_id', 'name'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}