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
<<<<<<< HEAD
        'unit_data',
=======
        'unit_data', // 🔥 TAMBAHKAN INI
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    ];

    protected $casts = [
        'expired_at' => 'date',
<<<<<<< HEAD
        'unit_data' => 'array',
=======
        'unit_data' => 'array', // 🔥 TAMBAHKAN INI
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (empty($asset->asset_id)) {
<<<<<<< HEAD
                $assetTypeId = $asset->asset_type_id ?? $asset->batch?->asset_type_id;
                $unitId = $asset->unit_id;
                $assetName = $asset->batch?->name ?? 'ASSET';

                if ($assetTypeId) {
                    $asset->asset_id = self::generateAssetId($assetTypeId, $unitId, $assetName);
=======
                // Fallback jika asset_type_id tidak ada, kita coba ambil dari batch
                $assetTypeId = $asset->asset_type_id ?? $asset->batch?->asset_type_id;

                if ($assetTypeId) {
                    $asset->asset_id = self::generateAssetId($assetTypeId);
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
                }
            }
        });
    }

<<<<<<< HEAD
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

            // 🔥 LOGIKA PEMOTONGAN NAMA ASET 🔥
            // 1. Ubah ke huruf kapital
            $cleanName = strtoupper($assetName);

            // 2. Pisahkan berdasarkan karakter non-alfanumerik (spasi, kurung, strip, slash, dll)
            // Karakter pemisah ini otomatis dihapus dari hasil akhir
            $parts = preg_split('/[^A-Z0-9]+/', $cleanName);

            $shortenedParts = [];
            foreach ($parts as $part) {
                if ($part !== '') {
                    // 3. Ambil maksimal 4 huruf dari setiap kata
                    $shortenedParts[] = substr($part, 0, 4);
                }
            }

            // 4. Gabungkan kembali dengan underscore
            $nameString = implode('_', $shortenedParts);
            if (empty($nameString)) {
                $nameString = 'ASSET'; // Fallback jika nama kosong/hanya simbol
            }

            // Format Prefix: 2026/11/TI/KOM/LAPT_ASUS_DESK_LQQD-
            $prefix = "$year/$month/$unitCode/{$type->code}/$nameString-";

            // Cari aset terakhir dengan prefix yang sama
            $lastAsset = self::withTrashed()
                ->where('asset_id', 'LIKE', "$prefix%")
=======
    public static function generateAssetId($assetTypeId)
    {
        return DB::transaction(function () use ($assetTypeId) {
            $type = AssetType::findOrFail($assetTypeId);
            $year = date('Y');
            $month = date('m');

            $lastAsset = self::withTrashed()
                ->where('asset_id', 'LIKE', "$year/$month/{$type->code}-%")
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
                ->lockForUpdate()
                ->orderBy('asset_id', 'desc')
                ->first();

            if ($lastAsset) {
<<<<<<< HEAD
                $idParts = explode('-', $lastAsset->asset_id);
                $lastNumber = (int) end($idParts);
=======
                $parts = explode('-', $lastAsset->asset_id);
                $lastNumber = (int) end($parts);
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $newNumberFormatted = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

<<<<<<< HEAD
            return $prefix . $newNumberFormatted;
=======
            return "$year/$month/{$type->code}-$newNumberFormatted";
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
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
<<<<<<< HEAD
                if (file_exists(public_path('assets/products/' . $filename))) {
                    return asset('assets/products/' . $filename);
=======
                if (file_exists(public_path('assets/products/'.$filename))) {
                    return asset('assets/products/'.$filename);
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
                }

                return asset('assets/logo-stti.png');
            },
        );
    }

<<<<<<< HEAD
    /**
     * Accessor untuk nama aset (diambil dari relasi batch)
     */
    public function getNameAttribute()
    {
        return $this->batch->name ?? '-';
    }

    /**
     * Accessor untuk merek aset (diambil dari relasi batch)
     */
    public function getBrandAttribute()
    {
        return $this->batch->brand ?? '-';
    }

=======
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    // Relasi
    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function batch()
    {
        return $this->belongsTo(AssetBatch::class, 'asset_batch_id');
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function qrCode()
    {
        return $this->hasOne(QrCode::class);
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function qrCodes()
    {
        return $this->hasMany(QrCode::class, 'asset_id');
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function penanggungJawab()
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function location_ref()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function replaces()
    {
        return $this->belongsTo(Asset::class, 'replaces_asset_id');
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function replacement()
    {
        return $this->hasOne(Asset::class, 'replaces_asset_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'Diganti');
    }
<<<<<<< HEAD
=======

>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
    public function scopeReplaced($query)
    {
        return $query->where('status', 'Diganti');
    }
}
