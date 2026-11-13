<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ServiceRequestItem extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'pemeliharaan' => 'Jasa Pemeliharaan & Perbaikan',
        'instalasi' => 'Jasa Instalasi & Pemasangan',
        'pelatihan' => 'Jasa Pelatihan & Sertifikasi',
        'sewa' => 'Jasa Sewa',
        'konsultasi' => 'Jasa Konsultasi',
    ];

    /**
     * Skema field variabel per kategori — dipakai untuk validasi dinamis
     * di controller & dokumentasi tim frontend.
     */
    public const SERVICE_DATA_SCHEMA = [
        'pemeliharaan' => [
            'asset_id' => ['type' => 'integer', 'required' => true],
            'maintenance_type' => ['type' => 'string',  'required' => true, 'in' => ['Preventif', 'Kuratif (Ringan)', 'Emergency']],
            'location' => ['type' => 'string',  'required' => true],
            'damage_description' => ['type' => 'string',  'required' => false],
            'start_date' => ['type' => 'date',    'required' => true],
            'end_date' => ['type' => 'date',    'required' => true],
            'notes' => ['type' => 'string',  'required' => false],
        ],
        'instalasi' => [
            'location' => ['type' => 'string',  'required' => true],
            'target_date' => ['type' => 'date',    'required' => true],
            'installation_scope' => ['type' => 'string',  'required' => false],
        ],
        'pelatihan' => [
            'participant_count' => ['type' => 'integer', 'required' => true, 'min' => 1],
            'certification_target' => ['type' => 'string',  'required' => true],
            'schedule_start' => ['type' => 'date',    'required' => true],
            'schedule_end' => ['type' => 'date',    'required' => true],
            'venue' => ['type' => 'string',  'required' => false],
        ],
        'sewa' => [
            'start_date' => ['type' => 'date',    'required' => true],
            'end_date' => ['type' => 'date',    'required' => true],
            'duration' => ['type' => 'integer', 'required' => true, 'min' => 1],
            'duration_unit' => ['type' => 'string',  'required' => true, 'in' => ['Hari', 'Minggu', 'Bulan', 'Tahun']],
            'rental_purpose' => ['type' => 'string',  'required' => false],
        ],
        'konsultasi' => [
            'topic' => ['type' => 'string',  'required' => true],
            'duration_days' => ['type' => 'integer', 'required' => true, 'min' => 1],
            'output_target' => ['type' => 'string',  'required' => false],
        ],
    ];

    protected $fillable = [
        'asset_request_id',
        'unit_id',
        'priority',
        'alasan_pengajuan',
        'reason',
        'service_category',
        'item_name',
        'specification',
        'quantity',
        'unit',
        'estimated_price_per_unit',
        'service_data',
        'approval_status',
        'approval_notes',
        'approved_by',
        'approved_at',
        'rolled_from_item_id',
        'attachment_file',
        // Fase 4: penyelesaian jasa
        'completed_at',
        'completion_notes',
        'bast_file',
        'executor',
        'actual_cost',
    ];

    protected $casts = [
        'service_data' => 'array',
        'estimated_price_per_unit' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ============ Relasi ============
    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function relatedAsset()
    {
        return $this->belongsTo(Asset::class, 'service_data->asset_id');
    }

    public function rolledFrom()
    {
        return $this->belongsTo(self::class, 'rolled_from_item_id');
    }

    // ============ Scope & Helper ============
    public function scopeReceivable($query)
    {
        return $query->whereNotIn('approval_status', ['rejected', 'deferred']);
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * ($this->estimated_price_per_unit ?? 0);
    }

    /**
     * Ambil satu nilai dari service_data dengan aman.
     */
    public function getServiceField(string $key, $default = null)
    {
        return data_get($this->service_data, $key, $default);
    }

    public function getApprovalStatusLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'pending' => '⏳ Menunggu',
            'approved' => '✅ Disetujui',
            'rejected' => '❌ Ditolak',
            'deferred' => '⏳ Ditangguhkan',
            default => $this->approval_status,
        };
    }

    public function getApprovalBadgeClassAttribute(): string
    {
        return match ($this->approval_status) {
            'pending' => 'pending',
            'approved' => 'available',
            'rejected' => 'maintenance',
            'deferred' => 'borrowed',
            default => '',
        };
    }

    // ============ Fase 4: Penyelesaian Jasa ============

    /**
     * Apakah jasa ini sudah ditandai selesai oleh Sarpras?
     */
    public function isCompleted(): bool
    {
        return ! is_null($this->completed_at);
    }

    /**
     * URL publik file BAST. Null kalau belum diupload atau file hilang dari storage.
     */
    protected function bastUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->bast_file && Storage::disk('public')->exists($this->bast_file)
                ? Storage::url($this->bast_file)
                : null,
        );
    }
}
