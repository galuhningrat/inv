<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'name',
        'asset_type_id',
        'brand',
        'serial_number',
        'price',
        'purchase_date',
        'location',
        'condition',
        'status',
        'image',
        'qr_code',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'price' => 'decimal:2',
    ];

    // ✅ PERBAIKAN: Gunakan DB transaction untuk prevent race condition
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (empty($asset->asset_id)) {
                $asset->asset_id = self::generateAssetId($asset->asset_type_id);
            }
        });
    }

    // ✅ PERBAIKAN: Method generateAssetId yang lebih robust
    public static function generateAssetId($assetTypeId)
    {
        return DB::transaction(function () use ($assetTypeId) {
            $type = AssetType::findOrFail($assetTypeId);
            $year = date('Y');
            $month = date('m');

            // Cari asset terakhir dengan lock untuk prevent race condition
            $lastAsset = self::where('asset_id', 'LIKE', "$year/$month/{$type->code}-%")
                ->lockForUpdate()
                ->orderBy('asset_id', 'desc')
                ->first();

            if ($lastAsset) {
                // Extract number dengan pattern yang lebih safe
                $parts = explode('-', $lastAsset->asset_id);
                $lastNumber = (int) end($parts);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            // Format dengan padding 4 digit
            $newNumberFormatted = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            return "$year/$month/{$type->code}-$newNumberFormatted";
        });
    }

    // Relasi
    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function qrCode()
    {
        return $this->hasOne(QrCode::class);
    }

    public function qrCodes()
    {
        return $this->hasMany(QrCode::class, 'asset_id');
    }
}