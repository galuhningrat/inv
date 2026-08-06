<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class QrCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'qr_code_id',
        'asset_id',
        'code_content',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($qrCode) {
            if (empty($qrCode->qr_code_id)) {
                $qrCode->qr_code_id = self::generateQrCodeId();
            }
        });
    }

    public static function generateQrCodeId()
    {
        return DB::transaction(function () {
            $lastQrCode = self::where('qr_code_id', 'LIKE', 'QCD-%')
                ->lockForUpdate()
                ->orderByRaw("CAST(SUBSTRING(qr_code_id FROM '[0-9]+$') AS INTEGER) DESC")
                ->first();

            $lastNumber = 0;
            if ($lastQrCode) {
                preg_match('/(\d+)$/', $lastQrCode->qr_code_id, $matches);
                $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;
            }

            return sprintf('QCD-%03d', $lastNumber + 1);
        });
    }

    public static function generateCodeContent(string $prefix): string
    {
        $attempts = 0;
        do {
            $timestamp = base_convert(time() + $attempts, 10, 36);
            $random = strtoupper(\Illuminate\Support\Str::random(6));
            $code = "{$prefix}-{$timestamp}-{$random}";
            $exists = \App\Models\Asset::where('qr_code', $code)->exists();
            $attempts++;
        } while ($exists && $attempts < 10);

        return $code;
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
