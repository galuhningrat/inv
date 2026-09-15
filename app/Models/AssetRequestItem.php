<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AssetRequestItem extends Model
{
    use HasFactory;

    // Satuan untuk item Fisik. Dipakai sebagai isi dropdown "Satuan" di form
    // pengajuan (requests.create) — dipisah dari satuan Non-Fisik karena istilah
    // yang relevan sangat berbeda (Pcs/Unit vs Lisensi/Account/Subscription).
    public const UNITS_FISIK = ['Pcs', 'Unit', 'Set', 'Buah', 'Lembar', 'Meter', 'Roll', 'Paket'];

    // Satuan untuk item Non-Fisik (lisensi, akun, dsb). Selalu diakhiri opsi
    // "Lainnya" di UI supaya user bisa mengisi manual jika tidak ada yang cocok.
    public const UNITS_NON_FISIK = ['Lisensi', 'Account', 'User', 'Device/Seat', 'Subscription', 'Unit', 'Paket', 'Domain', 'Sertifikat'];

    // Sifat Barang di-chain dari item_type: Aset Fisik cuma boleh "Tidak Habis
    // Pakai" (alur penuh, jadi Asset permanen) atau "Habis Pakai" (registrasi
    // ringan). Aset Non-Fisik cuma boleh "Tidak Habis Pakai" (alur penuh, jadi
    // IntangibleAsset) atau "Jasa" (registrasi ringan). "Jasa" TIDAK sama dengan
    // Non-Fisik secara umum — lisensi software tetap "Tidak Habis Pakai" karena ada
    // vendor & masa berlaku yang harus dipantau, beda karakter dengan jasa sekali
    // kerja seperti servis AC.
    public const SIFAT_BARANG_FISIK = ['Tidak Habis Pakai', 'Habis Pakai'];
    public const SIFAT_BARANG_NON_FISIK = ['Tidak Habis Pakai', 'Jasa'];

    // Kategori untuk item Fisik + Habis Pakai (ATK, dsb). Dipakai bersama kolom
    // "category" yang sama dengan Kategori Non-Fisik — kolomnya cuma string biasa
    // tanpa CHECK constraint di database, jadi aman dipakai untuk dua domain
    // berbeda selama validasinya konsisten di sisi aplikasi (lihat
    // AssetRequestController::store()).
    public const HABIS_PAKAI_CATEGORIES = [
        'ATK' => 'Alat Tulis Kantor (ATK)',
        'RT & Kebersihan' => 'Perlengkapan RT & Kebersihan',
        'Bahan Praktikum' => 'Komponen & Bahan Praktikum',
        'Konsumsi & Pantry' => 'Konsumsi & Pantry',
        'Lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'asset_request_id',
        'asset_type_id',
        'item_name',
        'specification',
        'category',
        'sifat_barang',
        'quantity',
        'unit',
        'estimated_price_per_unit',
        'image',
        'item_type',
        'approval_status',
        'approval_notes',
        'approved_by',
        'approved_at',
        'rolled_from_item_id',
        'received_quantity',
        'receipt_notes',
        'receipt_proof_file',
    ];

    protected $casts = [
        'estimated_price_per_unit' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->image)
                ? \Illuminate\Support\Facades\Storage::url($this->image)
                : asset('assets/logo-stti.png'),
        );
    }

    // URL bukti penerimaan (opsional) untuk item registrasi ringan (Habis
    // Pakai/Jasa) — null kalau tidak ada file yang diunggah, beda dari imageUrl()
    // di atas yang selalu punya fallback gambar karena memang wajib diisi untuk
    // item Fisik penuh.
    protected function receiptProofUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->receipt_proof_file && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->receipt_proof_file)
                ? \Illuminate\Support\Facades\Storage::url($this->receipt_proof_file)
                : null,
        );
    }

    // Item Habis Pakai (Fisik) atau Jasa (Non-Fisik): registrasi ringan, tidak
    // masuk tabel assets/intangible_assets — lihat AssetRequestController::receive().
    // Item lama (dibuat sebelum kolom sifat_barang ada) punya sifat_barang = null,
    // yang dengan sengaja TIDAK dianggap light receipt di sini — default paling
    // aman untuk kombinasi yang tidak dikenal adalah alur penuh (lebih ketat),
    // bukan sebaliknya.
    public function isLightReceipt(): bool
    {
        return ($this->item_type === 'Fisik' && $this->sifat_barang === 'Habis Pakai')
            || ($this->item_type === 'Non-Fisik' && $this->sifat_barang === 'Jasa');
    }

    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rolledFrom()
    {
        return $this->belongsTo(self::class, 'rolled_from_item_id');
    }

    public function rolloverChildren()
    {
        return $this->hasMany(self::class, 'rolled_from_item_id');
    }

    // Item yang boleh diproses di halaman Penerimaan Barang.
    // Item yang ditolak atau ditangguhkan Ketua belum lolos approval dan tidak boleh
    // ikut diregistrasi sebagai aset sampai statusnya berubah menjadi "approved".
    public function scopeReceivable($query)
    {
        return $query->whereNotIn('approval_status', ['rejected', 'deferred']);
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * ($this->estimated_price_per_unit ?? 0);
    }

    // Status approval label
    public function getApprovalStatusLabelAttribute()
    {
        return match ($this->approval_status) {
            'pending' => '⏳ Menunggu',
            'approved' => '✅ Disetujui',
            'rejected' => '❌ Ditolak',
            'deferred' => '⏳ Ditangguhkan',
            default => $this->approval_status,
        };
    }

    // Badge class untuk status approval
    public function getApprovalBadgeClassAttribute()
    {
        return match ($this->approval_status) {
            'pending' => 'pending',
            'approved' => 'available',
            'rejected' => 'maintenance',
            'deferred' => 'borrowed',
            default => '',
        };
    }
}
