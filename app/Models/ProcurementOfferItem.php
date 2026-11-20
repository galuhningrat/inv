<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementOfferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_offer_id',
        'asset_request_item_id',
        'unit_price',
        'discount',
        'tax',
        'subtotal',
        'notes'
    ];

    public function offer()
    {
        return $this->belongsTo(ProcurementOffer::class, 'procurement_offer_id');
    }

    public function requestItem()
    {
        return $this->belongsTo(AssetRequestItem::class, 'asset_request_item_id');
    }
}
