<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $fillable = ['code', 'name', 'description', 'is_active'];

    public function types()
    {
        return $this->hasMany(AssetType::class);
    }
}
