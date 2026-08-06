<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_id',
        'requester_id',
        'jenis_barang',
        'kategori_barang',
        'alasan_pengajuan',
        'related_asset_id',
        'priority',
        'reason',
        'status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'approved_by',
        'approved_at',
        'approval_notes',
        'confirmed_by',
        'confirmed_at',
        'confirmation_notes',
        'disbursed_by',
        'disbursed_at',
        'disbursement_notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->request_id)) {
                $request->request_id = self::generateRequestId();
            }
        });
    }

    public static function generateRequestId()
    {
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $last = self::where('request_id', 'LIKE', 'REQ-%')
                ->lockForUpdate()
                ->orderByRaw("CAST(SUBSTRING(request_id FROM '[0-9]+$') AS INTEGER) DESC")
                ->first();

            $lastNumber = 0;
            if ($last) {
                preg_match('/(\d+)$/', $last->request_id, $matches);
                $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;
            }

            return sprintf('REQ-%03d', $lastNumber + 1);
        });
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function relatedAsset()
    {
        return $this->belongsTo(Asset::class, 'related_asset_id');
    }

    public function items()
    {
        return $this->hasMany(AssetRequestItem::class);
    }

    public function getTotalEstimatedPriceAttribute()
    {
        return $this->items->sum(fn($item) => $item->subtotal);
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }
    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
    public function disburser()
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }
}