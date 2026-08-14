<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'type','category']; 

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class);
    }
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}