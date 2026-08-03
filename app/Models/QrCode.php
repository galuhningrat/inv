<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        $lastQrCode = self::orderBy('qr_code_id', 'desc')->first();

        if ($lastQrCode) {
            $lastNumber = (int) substr($lastQrCode->qr_code_id, 4);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "QCD-$newNumber";
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}