<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LightReceiptLog extends Model
{
    use HasFactory;

    protected $table = 'light_receipt_logs';

    protected $fillable = [
        'asset_request_item_id',
        'received_quantity',
        'notes',
        'proof_file',
        'received_by',
    ];

    // Relasi ke item pengajuan aset
    public function assetRequestItem()
    {
        return $this->belongsTo(AssetRequestItem::class);
    }

    // Relasi ke user yang menerima
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
