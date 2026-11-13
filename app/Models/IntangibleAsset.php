<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntangibleAsset extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 🔥 POIN 17.4: Audit Trail — catat setiap perubahan otomatis
     */
    protected static function booted(): void
    {
        // 1. Log saat aset dibuat
        static::created(function ($asset) {
            IntangibleAssetLog::create([
                'intangible_asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'action' => 'created',
                'description' => 'Aset baru didaftarkan',
            ]);
        });

        // 2. Log saat aset diupdate — hanya field yang berubah
        static::updated(function ($asset) {
            $changes = $asset->getChanges();
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            // Label ramah untuk setiap field
            $labels = [
                'name' => 'Nama Aset',
                'description' => 'Deskripsi',
                'category' => 'Kategori',
                'vendor' => 'Vendor',
                'price' => 'Harga Perolehan',
                'acquisition_method' => 'Metode Perolehan',
                'activation_date' => 'Tanggal Perolehan',
                'funding_source' => 'Sumber Pendanaan',
                'contract_number' => 'Nomor Kontrak',
                'license_type' => 'Jenis Lisensi',
                'billing_model' => 'Model Pembayaran',
                'expiry_date' => 'Tanggal Kedaluwarsa',
                'reminder_days' => 'Pengingat Masa Berlaku',
                'quota' => 'Kuota Pengguna',
                'unit_id' => 'Unit Pengelola',
                'pic_id' => 'PIC',
                'access_url' => 'URL Akses',
                'status' => 'Status',
                'certificate_file' => 'Dokumen Sertifikat',
                'category_data' => 'Spesifikasi Khusus',
                'asset_request_id' => 'Permintaan Pengadaan',
            ];

            $tracked = [];
            foreach ($changes as $key => $newValue) {
                if (! isset($labels[$key])) {
                    continue;
                }

                $oldValue = $asset->getOriginal($key);

                // Masking file (jangan simpan path lengkap)
                if ($key === 'certificate_file') {
                    $oldValue = $oldValue ? '(ada file)' : '(kosong)';
                    $newValue = $newValue ? '(ada file baru)' : '(dihapus)';
                }

                // Khusus category_data: tampilkan field apa saja yang berubah
                if ($key === 'category_data') {
                    $oldData = is_array($oldValue) ? $oldValue : (json_decode($oldValue, true) ?? []);
                    $newData = is_array($newValue) ? $newValue : (json_decode($newValue, true) ?? []);
                    $allKeys = array_unique(array_merge(array_keys($oldData), array_keys($newData)));
                    $changedKeys = [];
                    foreach ($allKeys as $k) {
                        if (($oldData[$k] ?? null) !== ($newData[$k] ?? null)) {
                            $changedKeys[] = ($k === 'product_key') ? 'Product Key (disembunyikan)' : $k;
                        }
                    }
                    $oldValue = null;
                    $newValue = implode(', ', $changedKeys) ?: '(tidak ada)';
                }

                // Konversi FK ke nama
                if ($key === 'unit_id') {
                    $oldValue = $oldValue ? (\App\Models\Unit::find($oldValue)?->name ?? "#{$oldValue}") : '-';
                    $newValue = $newValue ? (\App\Models\Unit::find($newValue)?->name ?? "#{$newValue}") : '-';
                }
                if ($key === 'pic_id') {
                    $oldValue = $oldValue ? (\App\Models\User::find($oldValue)?->name ?? "#{$oldValue}") : '-';
                    $newValue = $newValue ? (\App\Models\User::find($newValue)?->name ?? "#{$newValue}") : '-';
                }

                // Format tanggal
                if (in_array($key, ['activation_date', 'expiry_date']) && ($oldValue || $newValue)) {
                    $oldValue = $oldValue ? \Carbon\Carbon::parse($oldValue)->format('d M Y') : '-';
                    $newValue = $newValue ? \Carbon\Carbon::parse($newValue)->format('d M Y') : '-';
                }

                // Format harga
                if ($key === 'price') {
                    $oldValue = $oldValue !== null ? 'Rp '.number_format((float) $oldValue, 0, ',', '.') : '-';
                    $newValue = $newValue !== null ? 'Rp '.number_format((float) $newValue, 0, ',', '.') : '-';
                }

                $tracked[$key] = [
                    'label' => $labels[$key],
                    'old' => is_scalar($oldValue) ? $oldValue : json_encode($oldValue),
                    'new' => is_scalar($newValue) ? $newValue : json_encode($newValue),
                ];
            }

            if (empty($tracked)) {
                return;
            }

            $changedLabels = array_column($tracked, 'label');

            IntangibleAssetLog::create([
                'intangible_asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'action' => 'updated',
                'changes' => $tracked,
                'description' => 'Mengubah: '.implode(', ', $changedLabels),
            ]);
        });

        // 3. Log saat aset dihapus (soft delete)
        static::deleted(function ($asset) {
            IntangibleAssetLog::create([
                'intangible_asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'action' => 'deleted',
                'description' => 'Aset dihapus dari inventaris',
            ]);
        });
    }

    public const CATEGORIES = [
        'Software' => 'Perangkat Lunak / Software',
        'Cloud/SaaS' => 'Lisensi Cloud / SaaS',
        'Akun Digital' => 'Akun Digital / Subscription',
        'Domain/Hosting' => 'Domain & Hosting',
        'Jurnal Ilmiah' => 'Jurnal Ilmiah & Database',
        'HAKI/Paten' => 'HAKI / Paten / Hak Cipta',
        'Sertifikat Digital' => 'Sertifikat Digital / SSL',
        'Kurikulum' => 'Lisensi Kurikulum / Konten',
        'Lainnya' => 'Lainnya',
    ];

    // 🔥 POIN 14: Konstanta Sumber Pendanaan (Single Source of Truth)
    public const FUNDING_SOURCES = [
        'Dana Yayasan',
        'Hibah/Bantuan Pemerintah (LLDIKTI)',
        'Dana Mandiri/UKT Mahasiswa',
        'Kerja Sama Industri',
    ];

    protected $fillable = [
        'name', 'description', 'category', 'category_data', 'vendor',
        'price', 'acquisition_method', 'activation_date', 'funding_source', 'contract_number',
        'license_type', 'billing_model', 'expiry_date', 'reminder_days',
        'quota', 'unit_id', 'pic_id', 'access_url', 'certificate_file',
        'product_key', 'assigned_user_email', 'status', 'created_by', 'asset_request_id',
    ];

    protected $casts = [
        'activation_date' => 'date',
        'expiry_date' => 'date',
        'price' => 'decimal:2',
        'category_data' => 'array', // Cast JSON ke Array
        'product_key' => 'encrypted', // Enkripsi otomatis
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($item) {
    //         if (empty($item->asset_code)) {
    //             // Gunakan UUID atau mekanisme lock untuk mencegah duplikasi di concurrent request
    //             // Untuk prototipe, kita tetap gunakan logika count dengan safety net
    //             $year = date('Y');
    //             $lastAsset = self::whereYear('created_at', $year)->withTrashed()->lockForUpdate()->orderBy('id', 'desc')->first();
    //             $count = $lastAsset ? intval(substr($lastAsset->asset_code, -4)) + 1 : 1;
    //             $item->asset_code = sprintf('NF/%s/%04d', $year, $count);
    //         }
    //     });
    // }

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

    /**
     * 🔥 POIN 17.4: Relasi ke riwayat perubahan
     */
    public function logs()
    {
        return $this->hasMany(IntangibleAssetLog::class, 'intangible_asset_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * 🔥 POIN 16: Relasi ke permintaan pengadaan (jika aset berasal dari pengajuan)
     */
    public function request()
    {
        return $this->belongsTo(AssetRequest::class, 'asset_request_id');
    }

    /**
     * 🔥 POIN 16: Scope untuk menemukan aset tertaut ke request tertentu
     */
    public function scopeLinkedToRequest($query, $requestId)
    {
        return $query->where('asset_request_id', $requestId);
    }

    /**
     * Scope: Aset yang mendekati kedaluwarsa sesuai reminder_days masing-masing
     * (Menggunakan filter di level PHP agar database-agnostic)
     */
    public static function getExpiringSoonAssets()
    {
        return self::whereNotNull('expiry_date')
            ->where('status', 'Aktif')
            ->where('expiry_date', '>=', now()->startOfDay()->toDateString())
            ->with('unit', 'pic')
            ->get()
            ->filter(function ($asset) {
                $daysRemaining = now()->startOfDay()->diffInDays($asset->expiry_date->startOfDay(), false);
                $reminderDays = $asset->reminder_days ?? 30; // Default 30 hari jika tidak diset

                return $daysRemaining >= 0 && $daysRemaining <= $reminderDays;
            })
            ->sortBy('expiry_date');
    }

    /**
     * Accessor: Sisa hari menuju kedaluwarsa
     */
    public function getDaysUntilExpiryAttribute()
    {
        if (! $this->expiry_date) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);
    }

    /**
     * Accessor: Label status kedaluwarsa (untuk badge UI)
     */
    public function getExpiryStatusBadgeAttribute()
    {
        if (! $this->expiry_date) {
            return null;
        }

        $days = $this->days_until_expiry;

        if ($days < 0) {
            return ['label' => 'Kedaluwarsa', 'color' => '#ef4444'];
        }
        if ($days <= 7) {
            return ['label' => "Kritis ({$days} hari)", 'color' => '#ef4444'];
        }
        if ($days <= 30) {
            return ['label' => "Segera ({$days} hari)", 'color' => '#f59e0b'];
        }

        return null; // Lebih dari 30 hari, tidak perlu badge
    }

    /**
     * Skema Field Dinamis Berdasarkan Kategori
     */
    public static function getSchema($category)
    {
        $schemas = [
            'Software' => [
                'hide_vendor' => false,
                'license_types' => ['Perpetual' => 'Perpetual (Selamanya)', 'Subscription' => 'Subscription (Berlangganan)', 'Volume' => 'Volume / Site License'],
                'billing_models' => ['Sekali Bayar' => 'Sekali Bayar', 'Tahunan' => 'Tahunan', 'Bulanan' => 'Bulanan'],
                // Identitas Perangkat Lunak
                'product_name' => ['label' => 'Nama Produk', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'publisher' => ['label' => 'Penerbit / Pengembang', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'version' => ['label' => 'Versi Perangkat Lunak', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'edition' => ['label' => 'Edisi / Paket', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'software_type' => ['label' => 'Jenis Perangkat Lunak', 'type' => 'select', 'options' => ['Sistem Operasi' => 'Sistem Operasi', 'Aplikasi' => 'Aplikasi', 'Utilitas' => 'Utilitas', 'Lainnya' => 'Lainnya'], 'required' => false, 'in_batch' => true],
                'sku' => ['label' => 'SKU / Product ID', 'type' => 'text', 'required' => false, 'in_batch' => true],
                // Informasi Lisensi
                'license_model' => ['label' => 'Model Lisensi', 'type' => 'select', 'options' => ['Per User' => 'Per User', 'Per Device' => 'Per Device', 'Site License' => 'Site License'], 'required' => true, 'in_batch' => true],
                'license_scope' => ['label' => 'Cakupan Lisensi', 'type' => 'select', 'options' => ['Individual' => 'Individual', 'Laboratorium' => 'Laboratorium', 'Institusi' => 'Institusi'], 'required' => false, 'in_batch' => true],
                'max_users' => ['label' => 'Jumlah Pengguna Maksimal', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'is_transferable' => ['label' => 'Dapat Dipindah Tangankan?', 'type' => 'select', 'options' => ['Ya' => 'Ya', 'Tidak' => 'Tidak'], 'required' => false, 'in_batch' => true],
                'product_key' => ['label' => 'Product Key / Serial (Rahasia)', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'assigned_user_email' => ['label' => 'Email Pengguna Terdaftar', 'type' => 'email', 'required' => false, 'in_batch' => true],
                'renewal_info' => ['label' => 'Info Pembaruan Lisensi', 'type' => 'text', 'required' => false, 'in_batch' => true],
            ],
            'Cloud/SaaS' => [
                'hide_vendor' => false,
                'license_types' => ['Subscription' => 'Subscription (Berlangganan)'],
                'billing_models' => ['Bulanan' => 'Bulanan', 'Tahunan' => 'Tahunan'],
                'service_name' => ['label' => 'Nama Layanan', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'product_package' => ['label' => 'Nama Produk / Paket', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'service_type' => ['label' => 'Jenis Layanan', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'service_url' => ['label' => 'URL Layanan', 'type' => 'url', 'required' => false, 'in_batch' => true],
                'admin_url' => ['label' => 'URL Dasbor Admin', 'type' => 'url', 'required' => false, 'in_batch' => true],
                'billing_period' => ['label' => 'Periode Penagihan', 'type' => 'select', 'options' => ['Bulanan' => 'Bulanan', 'Tahunan' => 'Tahunan'], 'required' => false, 'in_batch' => true],
                'next_renewal_date' => ['label' => 'Tanggal Perpanjangan Berikutnya', 'type' => 'date', 'required' => false, 'in_batch' => true],
                'user_quota' => ['label' => 'Kuota Pengguna (Jumlah)', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'storage_capacity' => ['label' => 'Kapasitas Penyimpanan', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'seat_count' => ['label' => 'Jumlah Lisensi / Seat', 'type' => 'number', 'required' => false, 'in_batch' => true],
            ],
            'Akun Digital' => [
                'hide_vendor' => false,
                'license_types' => ['Subscription' => 'Subscription (Berlangganan)', 'Limited Right' => 'Hak Penggunaan Terbatas'],
                'billing_models' => ['Bulanan' => 'Bulanan', 'Tahunan' => 'Tahunan', 'Tanpa Biaya' => 'Tanpa Biaya (Gratis)'],
                'account_name' => ['label' => 'Nama Layanan / Akun', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'account_type' => ['label' => 'Jenis Akun', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'account_email' => ['label' => 'Email Akun', 'type' => 'email', 'required' => true, 'in_batch' => true],
                'admin_owner' => ['label' => 'Pemilik Administratif', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'usage_scope' => ['label' => 'Cakupan Hak Penggunaan', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'assigned_user_email' => ['label' => 'Ditugaskan Kepada (Email)', 'type' => 'email', 'required' => false, 'in_batch' => true],
            ],
            'Domain/Hosting' => [
                'hide_vendor' => false,
                'license_types' => ['Subscription' => 'Pendaftaran / Perpanjangan Berkala'],
                'billing_models' => ['Tahunan' => 'Tahunan', 'Bulanan' => 'Bulanan'],
                // Domain Fields
                'domain_name' => ['label' => 'Nama Domain Lengkap', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'registrar' => ['label' => 'Registrar', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'domain_reg_date' => ['label' => 'Tanggal Registrasi Domain', 'type' => 'date', 'required' => false, 'in_batch' => true],
                'domain_expiry_date' => ['label' => 'Tanggal Kedaluwarsa Domain', 'type' => 'date', 'required' => false, 'in_batch' => true],
                'next_renewal' => ['label' => 'Tanggal Pembaruan Berikutnya', 'type' => 'date', 'required' => false, 'in_batch' => true],
                // Hosting Fields
                'hosting_name' => ['label' => 'Nama Layanan Hosting', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'hosting_type' => ['label' => 'Jenis Hosting', 'type' => 'select', 'options' => ['Shared' => 'Shared Hosting', 'VPS' => 'VPS / Virtual Server', 'Dedicated' => 'Dedicated Server', 'Cloud' => 'Cloud Hosting'], 'required' => false, 'in_batch' => true],
                'hosting_package' => ['label' => 'Paket Hosting', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'storage' => ['label' => 'Kapasitas Penyimpanan', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'bandwidth' => ['label' => 'Bandwidth / Kuota', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'site_count' => ['label' => 'Jumlah Domain / Situs', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'hosting_start_date' => ['label' => 'Tanggal Mulai Hosting', 'type' => 'date', 'required' => false, 'in_batch' => true],
                'hosting_end_date' => ['label' => 'Tanggal Akhir Hosting', 'type' => 'date', 'required' => false, 'in_batch' => true],
                'panel_url' => ['label' => 'URL Panel Admin', 'type' => 'url', 'required' => false, 'in_batch' => true],
            ],
            'Jurnal Ilmiah' => [
                'hide_vendor' => false,
                'license_types' => ['Subscription' => 'Subscription (Berlangganan)'],
                'billing_models' => ['Tahunan' => 'Tahunan', 'Sekali Bayar' => 'Sekali Bayar (Akses Permanen)'],
                'journal_name' => ['label' => 'Nama Jurnal / Database', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'publisher' => ['label' => 'Penerbit / Penyedia', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'collection_type' => ['label' => 'Jenis Koleksi', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'issn' => ['label' => 'ISSN / e-ISSN (Opsional)', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'access_url' => ['label' => 'URL Akses', 'type' => 'url', 'required' => false, 'in_batch' => true],
                'access_method' => ['label' => 'Metode Akses', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'coverage' => ['label' => 'Cakupan Langganan', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'user_count' => ['label' => 'Jumlah Pengguna', 'type' => 'number', 'required' => false, 'in_batch' => true],
            ],
            'HAKI/Paten' => [
                'hide_vendor' => true,
                'license_types' => ['Registered Right' => 'Hak Terdaftar / Dilindungi'],
                'billing_models' => ['Sekali Bayar' => 'Sekali Bayar (Biaya Pendaftaran)'],
                // Identitas Hak
                'work_name' => ['label' => 'Nama Karya / Invensi', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'ip_type' => ['label' => 'Jenis HAKI', 'type' => 'select', 'options' => ['Paten' => 'Paten', 'Hak Cipta' => 'Hak Cipta', 'Merek' => 'Merek', 'Desain Industri' => 'Desain Industri'], 'required' => true, 'in_batch' => true],
                'app_number' => ['label' => 'Nomor Permohonan', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'app_date' => ['label' => 'Tanggal Permohonan', 'type' => 'date', 'required' => false, 'in_batch' => true],
                'registration_number' => ['label' => 'Nomor Registrasi / Sertifikat', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'grant_date' => ['label' => 'Tanggal Pemberian Hak', 'type' => 'date', 'required' => false, 'in_batch' => true],
                'country' => ['label' => 'Negara / Wilayah Pendaftaran', 'type' => 'text', 'required' => false, 'in_batch' => true],
                // Kepemilikan & Legalitas
                'inventor' => ['label' => 'Pencipta / Inventor', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'right_holder' => ['label' => 'Pemegang Hak', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'registration_status' => ['label' => 'Status Proses Registrasi', 'type' => 'select', 'options' => ['Dalam Proses' => 'Dalam Proses', 'Disetujui' => 'Disetujui', 'Ditolak' => 'Ditolak'], 'required' => false, 'in_batch' => true],
            ],
            'Sertifikat Digital' => [
                'hide_vendor' => false,
                'license_types' => ['Subscription' => 'Masa Berlaku Sertifikat'],
                'billing_models' => ['Tahunan' => 'Tahunan', 'Sekali Bayar' => 'Sekali Bayar'],
                'cert_name' => ['label' => 'Nama Sertifikat', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'cert_type' => ['label' => 'Jenis Sertifikat', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'issuer' => ['label' => 'Penerbit / Certificate Authority', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'serial_number' => ['label' => 'Nomor Seri Sertifikat', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'subject_domain' => ['label' => 'Domain / Subjek Sertifikat', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'scope' => ['label' => 'Cakupan Sertifikat', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'issued_date' => ['label' => 'Tanggal Penerbitan', 'type' => 'date', 'required' => false, 'in_batch' => true],
                'service_url' => ['label' => 'URL Layanan Terkait', 'type' => 'url', 'required' => false, 'in_batch' => true],
            ],
            'Kurikulum' => [
                'hide_vendor' => true,
                'license_types' => ['Limited Right' => 'Hak Penggunaan Internal', 'Registered Right' => 'Hak Cipta Terdaftar'],
                'billing_models' => ['Tanpa Biaya' => 'Tanpa Biaya (Internal)', 'Sekali Bayar' => 'Sekali Bayar'],
                'curriculum_name' => ['label' => 'Nama Kurikulum / Materi', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'document_type' => ['label' => 'Jenis Dokumen / Konten', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'study_program' => ['label' => 'Program Studi', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'version' => ['label' => 'Versi', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'valid_year' => ['label' => 'Tahun Berlaku', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'ratification_date' => ['label' => 'Tanggal Pengesahan', 'type' => 'date', 'required' => false, 'in_batch' => true],
                'sk_number' => ['label' => 'Nomor SK Pengesahan', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'creator' => ['label' => 'Pencipta / Pengembang', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'right_holder' => ['label' => 'Pemegang Hak', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'review_date' => ['label' => 'Tanggal Peninjauan / Revisi', 'type' => 'date', 'required' => false, 'in_batch' => true],
            ],
            'Lainnya' => [
                'hide_vendor' => false,
                'license_types' => ['Perpetual' => 'Perpetual', 'Subscription' => 'Subscription', 'Limited Right' => 'Hak Terbatas', 'Registered Right' => 'Hak Terdaftar'],
                'billing_models' => ['Sekali Bayar' => 'Sekali Bayar', 'Tahunan' => 'Tahunan', 'Bulanan' => 'Bulanan'],
                'sub_category' => ['label' => 'Sub Kategori / Jenis Aset', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'creator' => ['label' => 'Pencipta / Penyedia', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'reason' => ['label' => 'Alasan Masuk Kategori Lainnya', 'type' => 'textarea', 'required' => true, 'in_batch' => true],
            ],
        ];

        return $schemas[$category] ?? ['hide_vendor' => false];
    }
}
