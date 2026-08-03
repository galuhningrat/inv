<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class);
    }
}