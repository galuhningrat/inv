<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'item_type',
        'approval_status',
        'approval_notes',
        'approved_by',
        'approved_at',
        'rolled_from_item_id',
    ];

    protected $casts = [
        'estimated_price_per_unit' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->image)
            ? \Illuminate\Support\Facades\Storage::url($this->image)
            : asset('assets/logo-stti.png'),
        );
    }

    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rolledFrom()
    {
        return $this->belongsTo(self::class, 'rolled_from_item_id');
    }

    public function rolloverChildren()
    {
        return $this->hasMany(self::class, 'rolled_from_item_id');
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * ($this->estimated_price_per_unit ?? 0);
    }

    // Status approval label
    public function getApprovalStatusLabelAttribute()
    {
        return match ($this->approval_status) {
            'pending' => '⏳ Menunggu',
            'approved' => '✅ Disetujui',
            'rejected' => '❌ Ditolak',
            'deferred' => '⏳ Ditangguhkan',
            default => $this->approval_status,
        };
    }

    // Badge class untuk status approval
    public function getApprovalBadgeClassAttribute()
    {
        return match ($this->approval_status) {
            'pending' => 'pending',
            'approved' => 'available',
            'rejected' => 'maintenance',
            'deferred' => 'borrowed',
            default => '',
        };
    }
}