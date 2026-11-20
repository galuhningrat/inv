<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementOffer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_request_id',
        'vendor_name',
        'vendor_contact',
        'vendor_email',
        'vendor_phone',
        'offer_number',
        'offer_date',
        'valid_until',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'total_price',
        'notes',
        'quotation_file',
        'status',
        'submitted_by',
        'is_selected',
        'selection_reason',
        'selected_by',
        'selected_at'
    ];

    protected $casts = [
        'offer_date' => 'date',
        'valid_until' => 'date',
        'selected_at' => 'datetime',
        'is_selected' => 'boolean',
    ];

    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }

    public function items()
    {
        return $this->hasMany(ProcurementOfferItem::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function selector()
    {
        return $this->belongsTo(User::class, 'selected_by');
    }
}
