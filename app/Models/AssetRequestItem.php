<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_request_id',
        'asset_type_id',
        'item_name',
        'specification',
        'quantity',
        'unit',
        'estimated_price_per_unit',
        'image',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->image)
            ? \Illuminate\Support\Facades\Storage::url($this->image)
            : asset('assets/logo-stti.png'),
        );
    }

    protected $casts = [
        'estimated_price_per_unit' => 'decimal:2',
    ];

    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * ($this->estimated_price_per_unit ?? 0);
    }
}
