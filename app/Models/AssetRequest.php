<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class AssetRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_id',
        'requester_id',
        'asset_name',
        'asset_type_id',
        'quantity',
        'estimated_price',
        'priority',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'estimated_price' => 'decimal:2',
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
        return DB::transaction(function () {
            $lastRequest = self::where('request_id', 'LIKE', 'REQ-%')
                ->lockForUpdate()
                ->orderByRaw("CAST(SUBSTRING(request_id FROM '[0-9]+$') AS INTEGER) DESC")
                ->first();

            $lastNumber = 0;
            if ($lastRequest) {
                preg_match('/(\d+)$/', $lastRequest->request_id, $matches);
                $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;
            }

            return sprintf('REQ-%03d', $lastNumber + 1);
        });
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }
}
