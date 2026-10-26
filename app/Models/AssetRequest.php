<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class AssetRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tipe dokumen:
     *  - 'aset' → dokumen hasil submit form Pengajuan Aset (dynamic repeater)
     *  - 'jasa' → dokumen hasil submit form Pengajuan Jasa (salah satu sub-kategori)
     */
    public const TYPE_ASET = 'aset';

    public const TYPE_JASA = 'jasa';

    protected $fillable = [
        'request_id',
        'request_type',
        'service_category',
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
            if (empty($request->request_type)) {
                $request->request_type = self::TYPE_ASET;
            }
        });
    }

    public static function generateRequestId()
    {
        return DB::transaction(function () {
            $last = self::withTrashed()
                ->where('request_id', 'LIKE', 'REQ-%')
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

    // ============================================================
    //  RELASI — User & Workflow
    // ============================================================

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

    // ============================================================
    //  RELASI — Item (Aset & Jasa)
    // ============================================================

    /**
     * Item aset (fisik / non-fisik) yang diajukan pada dokumen ini.
     */
    public function items()
    {
        return $this->hasMany(AssetRequestItem::class);
    }

    /**
     * Item jasa yang diajukan pada dokumen ini (Fase 3).
     */
    public function serviceItems()
    {
        return $this->hasMany(ServiceRequestItem::class);
    }

    /**
     * 🔥 POIN 16: Relasi ke aset non-fisik yang DITAUTKAN ke pengajuan ini.
     * Digunakan untuk mencegah duplikasi registrasi (1 item non-fisik
     * seharusnya hanya menghasilkan 1 record di intangible_assets).
     */
    public function intangibleAssets()
    {
        return $this->hasMany(IntangibleAsset::class, 'asset_request_id');
    }

    // ============================================================
    //  ACCESSOR — View Helpers
    // ============================================================

    public function getServiceTotalAttribute()
    {
        return $this->serviceItems->sum(fn ($item) => $item->subtotal);
    }

    public function getAllItemsAttribute()
    {
        return $this->items->concat($this->serviceItems);
    }

    public function getAssetItemsGroupedByUnitAttribute()
    {
        return $this->items->groupBy('unit_id');
    }

    public function getAllItemsGroupedByUnitAttribute()
    {
        return $this->all_items->groupBy('unit_id');
    }

    // ============================================================
    //  HELPERS — Tipe Dokumen
    // ============================================================

    public function isServiceRequest(): bool
    {
        return $this->request_type === self::TYPE_JASA;
    }

    public function isAssetRequest(): bool
    {
        return $this->request_type === self::TYPE_ASET;
    }

    // ============================================================
    //  AGREGAT — Total & Summary
    // ============================================================

    public static function hasRequestThisMonth($unitId)
    {
        return self::query()
            ->where('unit_id', $unitId)
            ->where('period_month', now()->month)
            ->where('period_year', now()->year)
            ->exists();
    }

    public function getApprovedTotalAttribute()
    {
        $assetApproved = $this->items
            ->where('approval_status', 'approved')
            ->sum(fn ($item) => $item->subtotal);

        $serviceApproved = $this->serviceItems
            ->where('approval_status', 'approved')
            ->sum(fn ($item) => $item->subtotal);

        return $assetApproved + $serviceApproved;
    }

    public function getTotalEstimatedPriceAttribute()
    {
        return $this->items->sum(fn ($item) => $item->subtotal)
            + $this->serviceItems->sum(fn ($item) => $item->subtotal);
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity') + $this->serviceItems->sum('quantity');
    }

    public function getApprovalSummaryAttribute()
    {
        $allItems = $this->items->concat($this->serviceItems);

        return [
            'pending' => $allItems->where('approval_status', 'pending')->count(),
            'approved' => $allItems->where('approval_status', 'approved')->count(),
            'rejected' => $allItems->where('approval_status', 'rejected')->count(),
            'deferred' => $allItems->where('approval_status', 'deferred')->count(),
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        if (
            $this->status === 'Pending' &&
            $this->items->contains(fn ($item) => ! is_null($item->rolled_from_item_id))
        ) {
            return 'Menunggu Verifikasi PJ Pengadaan untuk Pengadaan Bulan Berikutnya';
        }

        return match ($this->status) {
            'Pending' => 'Menunggu Verifikasi PJ Pengadaan',
            'Diverifikasi' => 'Menunggu Persetujuan Ketua STTI',
            'Disetujui' => 'Menunggu Konfirmasi Dana Cair oleh Keuangan',
            'Dana Cair' => 'Menunggu Konfirmasi Penerimaan Barang oleh PJ Pengadaan',
            'Dikonfirmasi' => 'Menunggu Registrasi Aset oleh Sarpras',
            'Diterima' => 'Aset Telah Terdaftar ke Inventaris',
            'Selesai' => 'Jasa Telah Selesai Dikerjakan',
            'Ditolak' => 'Pengajuan Ditolak',
            default => $this->status,
        };
    }
}
