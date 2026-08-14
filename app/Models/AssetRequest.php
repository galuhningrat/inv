<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class AssetRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_id',
        'requester_id',
        'unit_id',
        'period_month',
        'period_year',
        'jenis_barang',
        'kategori_barang',
        'alasan_pengajuan',
        'related_asset_id',
        'priority',
        'reason',
        'status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'approved_by',
        'approved_at',
        'approval_notes',
        'confirmed_by',
        'confirmed_at',
        'confirmation_notes',
        'disbursed_by',
        'disbursed_at',
        'disbursement_notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'period_month' => 'integer',
        'period_year' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->request_id)) {
                $request->request_id = self::generateRequestId();
            }
            if (empty($request->period_month)) {
                $request->period_month = now()->month;
            }
            if (empty($request->period_year)) {
                $request->period_year = now()->year;
            }
        });
    }

    public static function generateRequestId()
    {
        return DB::transaction(function () {
            $last = self::where('request_id', 'LIKE', 'REQ-%')
                ->lockForUpdate()
                ->orderByRaw("CAST(SUBSTRING(request_id FROM '[0-9]+$') AS INTEGER) DESC")
                ->first();

            $lastNumber = 0;
            if ($last) {
                preg_match('/(\d+)$/', $last->request_id, $matches);
                $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;
            }

            return sprintf('REQ-%03d', $lastNumber + 1);
        });
    }

    // Relasi
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
    public function disburser()
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function relatedAsset()
    {
        return $this->belongsTo(Asset::class, 'related_asset_id');
    }
    public function items()
    {
        return $this->hasMany(AssetRequestItem::class);
    }

    // Cek apakah sudah ada pengajuan di bulan ini untuk unit tertentu
    public static function hasRequestThisMonth($unitId)
    {
        return self::where('unit_id', $unitId)
            ->where('period_month', now()->month)
            ->where('period_year', now()->year)
            ->exists();
    }

    // Total estimasi harga (hanya item yang disetujui)
    public function getApprovedTotalAttribute()
    {
        return $this->items
            ->where('approval_status', 'approved')
            ->sum(fn($item) => $item->subtotal);
    }

    // Total estimasi harga (semua item)
    public function getTotalEstimatedPriceAttribute()
    {
        return $this->items->sum(fn($item) => $item->subtotal);
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }

    // Hitung jumlah item berdasarkan status approval
    public function getApprovalSummaryAttribute()
    {
        return [
            'pending' => $this->items->where('approval_status', 'pending')->count(),
            'approved' => $this->items->where('approval_status', 'approved')->count(),
            'rejected' => $this->items->where('approval_status', 'rejected')->count(),
            'deferred' => $this->items->where('approval_status', 'deferred')->count(),
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'Pending' => 'Menunggu Verifikasi PJ Pengadaan',
            'Diverifikasi' => 'Menunggu Persetujuan Ketua STTI',
            'Disetujui' => 'Menunggu Konfirmasi Dana Cair oleh Keuangan',
            'Dana Cair' => 'Menunggu Konfirmasi Penerimaan Barang oleh PJ Pengadaan',
            'Dikonfirmasi' => 'Menunggu Registrasi Aset oleh Sarpras',
            'Diterima' => 'Aset Telah Terdaftar ke Inventaris',
            'Ditolak' => 'Pengajuan Ditolak',
            default => $this->status,
        };
    }
}