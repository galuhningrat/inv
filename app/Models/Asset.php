<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_batch_id',
        'asset_id',
        'serial_number',
        'location',
        'condition',
        'status',
        'image',
        'qr_code',
        'penanggung_jawab_id',
        'asset_request_id',
        'unit_id',
        'location_id',
        'location_detail',
        'expired_at',
        'replaces_asset_id',
        'unit_data',
    ];

    protected $casts = [
        'expired_at' => 'date',
        'unit_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (empty($asset->asset_id)) {
                $assetTypeId = $asset->asset_type_id ?? $asset->batch?->asset_type_id;
                $unitId = $asset->unit_id;
                $assetName = $asset->batch?->name ?? 'ASSET';

                if ($assetTypeId) {
                    $asset->asset_id = self::generateAssetId($assetTypeId, $unitId, $assetName);
                }
            }
        });
    }

    /**
     * 🔥 Generate format ID Aset baru: Tahun/Bulan/KodeUnit/KodeJenis/NAMA_SINGKAT-0001
     */
    public static function generateAssetId($assetTypeId, $unitId = null, $assetName = 'ASSET')
    {
        return DB::transaction(function () use ($assetTypeId, $unitId, $assetName) {
            $type = AssetType::findOrFail($assetTypeId);

            // Ambil kode unit dari relasi atau default ke 'UMUM'
            $unit = $unitId ? Unit::find($unitId) : null;
            $unitCode = $unit ? $unit->code : 'UMUM'; // Kode unit TIDAK dipotong

            $year = date('Y');
            $month = date('m');

            // Logika Pemotongan Nama Aset
            $cleanName = strtoupper($assetName);
            $parts = preg_split('/[^A-Z0-9]+/', $cleanName); // Pisahkan berdasarkan karakter non-alfanumerik

            $shortenedParts = [];
            foreach ($parts as $part) {
                if ($part !== '') {
                    $shortenedParts[] = substr($part, 0, 4); // Ambil maks 4 huruf
                }
            }

            $nameString = implode('_', $shortenedParts);
            if (empty($nameString)) {
                $nameString = 'ASSET';
            }

            $prefix = "$year/$month/$unitCode/{$type->code}/$nameString-";

            // Cari aset terakhir dengan prefix yang sama
            $lastAsset = self::withTrashed()
                ->where('asset_id', 'LIKE', "$prefix%")
                ->lockForUpdate()
                ->orderBy('asset_id', 'desc')
                ->first();

            if ($lastAsset) {
                $idParts = explode('-', $lastAsset->asset_id);
                $lastNumber = (int) end($idParts);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $newNumberFormatted = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            return $prefix . $newNumberFormatted;
        });
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->image) {
                    return asset('assets/logo-stti.png');
                }

                if (Str::startsWith($this->image, ['http://', 'https://'])) {
                    return $this->image;
                }

                if (Storage::disk('public')->exists($this->image)) {
                    return Storage::url($this->image);
                }

                $filename = basename($this->image);
                if (file_exists(public_path('assets/products/' . $filename))) {
                    return asset('assets/products/' . $filename);
                }

                return asset('assets/logo-stti.png');
            },
        );
    }

    public function getNameAttribute()
    {
        return $this->batch->name ?? '-';
    }

    public function getBrandAttribute()
    {
        return $this->batch->brand ?? '-';
    }

    // Relasi
    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }
    public function batch()
    {
        return $this->belongsTo(AssetBatch::class, 'asset_batch_id');
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
    public function penanggungJawab()
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }
    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function location_ref()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    public function replaces()
    {
        return $this->belongsTo(Asset::class, 'replaces_asset_id');
    }
    public function replacement()
    {
        return $this->hasOne(Asset::class, 'replaces_asset_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'Diganti');
    }
    public function scopeReplaced($query)
    {
        return $query->where('status', 'Diganti');
    }
}
