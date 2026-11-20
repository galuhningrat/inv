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
        'verified_by',
        'verified_at',
        'verification_notes',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'cost' => 'decimal:2',
        'verified_at' => 'datetime',
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
        $last = self::withTrashed()->orderBy('maintenance_id', 'desc')->first();

        if ($last) {
            $lastNumber = (int) substr($last->maintenance_id, 4);
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

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Helper untuk badge status
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Menunggu Verifikasi' => 'pending',
            'Diterima' => 'borrowed',
            'Dalam Proses' => 'borrowed',
            'Menunggu Komponen' => 'maintenance',
            'Selesai' => 'available',
            default => 'pending',
        };
    }

    public function isAwaitingVerification(): bool
    {
        return $this->status === 'Menunggu Verifikasi';
    }

    protected static function booted()
    {
        // 1. Log saat tiket baru dibuat
        static::created(function ($maintenance) {
            MaintenanceLog::create([
                'maintenance_id' => $maintenance->id,
                'user_id' => auth()->id() ?? 1,
                'action' => 'created',
                'new_status' => $maintenance->status,
                'notes' => 'Laporan kerusakan dibuat.',
            ]);
        });

        // 2. Log saat status berubah (verifikasi / proses / selesai)
        static::updated(function ($maintenance) {
            if ($maintenance->isDirty('status')) {
                MaintenanceLog::create([
                    'maintenance_id' => $maintenance->id,
                    'user_id' => auth()->id() ?? 1,
                    'action' => 'status_changed',
                    'old_status' => $maintenance->getOriginal('status'),
                    'new_status' => $maintenance->status,
                    'notes' => $maintenance->verification_notes ?? 'Status diperbarui.',
                ]);
            }
        });
    }

    // Tambahkan relasi di bagian bawah model
    public function logs()
    {
        return $this->hasMany(MaintenanceLog::class)->latest();
    }
}
