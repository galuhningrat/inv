<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntangibleAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_code',
        'name',
        'category',
        'vendor',
        'price',
        'activation_date',
        'funding_source',
        'contract_number',
        'license_type',
        'expiry_date',
        'reminder_days',
        'quota',
        'unit_id',
        'pic_id',
        'access_url',
        'certificate_file',
        'product_key',
        'assigned_user_email',
        'status',
        'created_by',
        'asset_request_id',
    ];

    protected $casts = [
        'activation_date' => 'date',
        'expiry_date' => 'date',
        'price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->asset_code)) {
                $year = date('Y');
                $count = self::whereYear('created_at', $year)->withTrashed()->count() + 1;
                $item->asset_code = sprintf('NF/%s/%04d', $year, $count);
            }
        });
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }
}