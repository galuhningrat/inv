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
     *
     * Nilai ini disimpan di header supaya halaman riwayat (index) bisa
     * memfilter/menampilkan tab "Aset" vs "Jasa" tanpa harus join ke tabel item.
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
            // withTrashed() WAJIB: AssetRequest pakai SoftDeletes, dan
            // AssetRequestPolicy::delete() mengizinkan Sarpras/Admin menghapus
            // pengajuan berstatus Pending — soft-delete, bukan hilang permanen. Tanpa
            // withTrashed() di sini, REQ-XXX dengan nomor tertinggi yang kebetulan
            // terhapus akan terlewat dari pencarian "nomor terakhir", dan nomor yang
            // sama bisa dicoba dipakai lagi — bug yang persis sama yang baru saja
            // bikin "qr_codes_qr_code_id_unique" collide di modul Aset Fisik, cuma
            // di sini belum sempat terjadi karena belum ada yang menghapus request
            // dengan nomor tertinggi.
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
     * Setelah Fase 2, masing-masing item punya unit_id & priority sendiri.
     */
    public function items()
    {
        return $this->hasMany(AssetRequestItem::class);
    }

    /**
     * Item jasa yang diajukan pada dokumen ini (Fase 3).
     * Field variabel per sub-kategori tersimpan di kolom JSON service_data.
     */
    public function serviceItems()
    {
        return $this->hasMany(ServiceRequestItem::class);
    }

    /**
     * Total untuk pengajuan jasa (pakai serviceItems).
     * Override getter lama supaya view jasa tetap jalan tanpa refactor.
     */
    public function getServiceTotalAttribute()
    {
        return $this->serviceItems->sum(fn($item) => $item->subtotal);
    }

    // ============================================================
    //  ACCESSOR — View Helpers
    // ============================================================

    /**
     * Gabungan item aset + jasa. Berguna untuk halaman reviewer/show
     * yang perlu menampilkan semuanya dalam satu tabel.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllItemsAttribute()
    {
        return $this->items->concat($this->serviceItems);
    }

    /**
     * Group item aset berdasarkan Unit Pengaju — dipakai di halaman reviewer
     * (approval / show) supaya Ketua STTI bisa melihat "Header Lab Komputer"
     * dengan tabel item di bawahnya, terpisah dari "Header Lab Elektro".
     *
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection>
     */
    public function getAssetItemsGroupedByUnitAttribute()
    {
        return $this->items->groupBy('unit_id');
    }

    /**
     * Versi gabungan (aset + jasa) di-group by unit — untuk halaman ringkasan
     * yang menampilkan campuran item (jarang terjadi di satu request, tapi
     * berguna saat rollover membuat dokumen dengan tipe item beragam).
     */
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

    // Cek apakah sudah ada pengajuan di bulan ini untuk unit tertentu
    public static function hasRequestThisMonth($unitId)
    {
        return self::query()
            ->where('unit_id', $unitId)
            ->where('period_month', now()->month)
            ->where('period_year', now()->year)
            ->exists();
    }

    // Total estimasi harga (hanya item yang disetujui) — aset + jasa
    public function getApprovedTotalAttribute()
    {
        $assetApproved = $this->items
            ->where('approval_status', 'approved')
            ->sum(fn($item) => $item->subtotal);

        $serviceApproved = $this->serviceItems
            ->where('approval_status', 'approved')
            ->sum(fn($item) => $item->subtotal);

        return $assetApproved + $serviceApproved;
    }

    // Total estimasi harga (semua item) — aset + jasa
    public function getTotalEstimatedPriceAttribute()
    {
        return $this->items->sum(fn($item) => $item->subtotal)
            + $this->serviceItems->sum(fn($item) => $item->subtotal);
    }

    // Total quantity — aset + jasa
    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity') + $this->serviceItems->sum('quantity');
    }

    // Approval summary — aset + jasa
    public function getApprovalSummaryAttribute()
    {
        $allItems = $this->items->concat($this->serviceItems);

        return [php artisan view:clear
            'pending'  => $allItems->where('approval_status', 'pending')->count(),
            'approved' => $allItems->where('approval_status', 'approved')->count(),
            'rejected' => $allItems->where('approval_status', 'rejected')->count(),
            'deferred' => $allItems->where('approval_status', 'deferred')->count(),
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        if (
            $this->status === 'Pending' &&
            $this->items->contains(fn($item) => ! is_null($item->rolled_from_item_id))
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
