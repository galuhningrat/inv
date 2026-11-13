<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'maintenance_id',
        'asset_id',
        'type',
        'maintenance_date',
        'cost',
        'description',
        'technician',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($maintenance) {
            if (empty($maintenance->maintenance_id)) {
                $maintenance->maintenance_id = self::generateMaintenanceId();
            }
        });
    }

    public static function generateMaintenanceId()
    {
        $lastMaintenance = self::orderBy('maintenance_id', 'desc')->first();

        if ($lastMaintenance) {
            $lastNumber = (int) substr($lastMaintenance->maintenance_id, 4);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "MNT-$newNumber";
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}