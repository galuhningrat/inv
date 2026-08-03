<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (empty($asset->asset_id)) {
                $asset->asset_id = self::generateAssetId($asset->asset_type_id);
            }
        });
    }

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
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->image) {
                    return asset('assets/logo-stti.png');
                }

                // Kasus data lama dari seeder yang masih pakai URL eksternal
                if (Str::startsWith($this->image, ['http://', 'https://'])) {
                    return $this->image;
                }

                // Kasus 1: gambar hasil upload form, tersimpan di storage disk 'public'
                if (Storage::disk('public')->exists($this->image)) {
                    return Storage::url($this->image);
                }

                // Kasus 2: gambar dari seeder, fisik di public/assets/products/
                $filename = basename($this->image);
                if (file_exists(public_path('assets/products/' . $filename))) {
                    return asset('assets/products/' . $filename);
                }

                // Fallback terakhir kalau benar-benar tidak ditemukan
                return asset('assets/logo-stti.png');
            },
        );
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
