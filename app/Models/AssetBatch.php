<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_type_id',
        'name',
        'brand',
        'model',
        'price',
        'purchase_date',
        'funding_source',
        'economic_life_years',
        'residual_value',
        'category_data',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'price' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'category_data' => 'array',
    ];

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
