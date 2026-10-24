<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * Mengambil semua tipe aset dengan urutan yang sudah ditentukan (Lainnya di akhir).
     */
    public static function getOrdered()
    {
        $orderedCodes = [
            'KOM', 'JAR', 'ELK', 'LAB', 'MEB', 'ARS', 'OTO', 'TNH', 'GDG', 'PRS', 'FUR', 'ATK', 'LAN',
        ];

        return self::all()->sortBy(function ($type) use ($orderedCodes) {
            $pos = array_search(strtoupper($type->code), $orderedCodes);

            return $pos === false ? 999 : $pos;
        });
    }

    /**
     * Skema Dinamis (Otak dari Form Berbasis Kategori)
     * Menentukan field apa saja yang muncul, wajib/opsional, dan disimpan di mana.
     *
     * Parameter:
     * - in_batch: Disimpan di tabel asset_batches (JSON category_data)
     * - in_unit: Disimpan di tabel assets (JSON unit_data)
     * - hide_general: Menyembunyikan field default (brand, model, serial_number)
     */
    public static function getSchema($code)
    {
        $schemas = [
            // =================================================================
            // 6.1 KOM — Komputer & Periferal
            // =================================================================
            'KOM' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Jenis Perangkat', 'type' => 'select', 'options' => [
                    'laptop' => 'Laptop', 'desktop' => 'Desktop', 'server' => 'Server',
                    'monitor' => 'Monitor', 'printer' => 'Printer', 'keyboard' => 'Keyboard', 'mouse' => 'Mouse', 'lainnya' => 'Periferal Lainnya',
                ], 'required' => true, 'in_batch' => true],

                // Spesifikasi Teknis (Kondisional)
                'processor' => ['label' => 'Prosesor / Chipset', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'ram' => ['label' => 'Kapasitas RAM (GB)', 'type' => 'number', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'storage' => ['label' => 'Penyimpanan (SSD/HDD)', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'gpu' => ['label' => 'GPU / Grafis', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'os' => ['label' => 'Sistem Operasi & Versi', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'ukuran_layar' => ['label' => 'Ukuran Layar (Inch)', 'type' => 'number', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['laptop', 'monitor']]],
                'resolusi_layar' => ['label' => 'Resolusi Layar', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['laptop', 'monitor']]],

                // Pengelolaan
                'garansi_berakhir' => ['label' => 'Masa Garansi Berakhir', 'type' => 'date', 'required' => false, 'in_batch' => true],

                // Identitas Jaringan (Per Unit)
                'hostname' => ['label' => 'Hostname / Nama Komputer', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'mac_address' => ['label' => 'MAC Address', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
            ],

            // =================================================================
            // 6.2 JAR — Jaringan
            // =================================================================
            'JAR' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Jenis Perangkat Jaringan', 'type' => 'select', 'options' => [
                    'router' => 'Router', 'switch' => 'Switch', 'ap' => 'Access Point', 'modem' => 'Modem / ONT', 'firewall' => 'Firewall',
                ], 'required' => true, 'in_batch' => true],

                'jumlah_port' => ['label' => 'Jumlah Port', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'kecepatan' => ['label' => 'Kecepatan Port (Mbps/Gbps)', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'dukungan_poe' => ['label' => 'Dukungan PoE', 'type' => 'select', 'options' => ['Ya' => 'Ya', 'Tidak' => 'Tidak'], 'required' => false, 'in_batch' => true],
                'versi_firmware' => ['label' => 'Versi Firmware', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'lokasi_rak' => ['label' => 'Nomor Rak / Identitas', 'type' => 'text', 'required' => false, 'in_unit' => true],
                'mac_address' => ['label' => 'MAC Address', 'type' => 'text', 'required' => false, 'in_unit' => true],
                'ip_address' => ['label' => 'IP Address', 'type' => 'text', 'required' => false, 'in_unit' => true],
            ],

            // =================================================================
            // 6.3 ELK — Elektronik Umum
            // =================================================================
            'ELK' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Jenis Elektronik', 'type' => 'select', 'options' => [
                    'proyektor' => 'Proyektor', 'tv' => 'TV / Layar', 'kamera' => 'Kamera', 'audio' => 'Audio', 'ac' => 'AC', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],

                'daya_listrik' => ['label' => 'Daya Listrik (Watt)', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'tegangan' => ['label' => 'Tegangan (Volt)', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'kapasitas_pendingin' => ['label' => 'Kapasitas Pendingin (PK)', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['ac']]],
                'tahun_pembuatan' => ['label' => 'Tahun Pembuatan', 'type' => 'number', 'required' => false, 'in_batch' => true],
            ],

            // =================================================================
            // 6.4 LAB — Peralatan & Instrumen Laboratorium
            // =================================================================
            'LAB' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Jenis Instrumen', 'type' => 'select', 'options' => [
                    'ukur' => 'Alat Ukur', 'eksperimen' => 'Peralatan Eksperimen', 'mikroskop' => 'Mikroskop / Optik', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],

                'fungsi_alat' => ['label' => 'Fungsi Instrumen', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'rentang_ukur' => ['label' => 'Rentang Pengukuran', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'akurasi' => ['label' => 'Akurasi / Ketelitian', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'satuan_ukur' => ['label' => 'Satuan Pengukuran', 'type' => 'text', 'required' => false, 'in_batch' => true],

                'butuh_kalibrasi' => ['label' => 'Memerlukan Kalibrasi?', 'type' => 'select', 'options' => ['Ya' => 'Ya', 'Tidak' => 'Tidak'], 'required' => true, 'in_batch' => true],
                'interval_kalibrasi' => ['label' => 'Interval (Bulan)', 'type' => 'number', 'required' => false, 'in_batch' => true, 'show_if' => ['butuh_kalibrasi' => ['Ya']]],
                'tgl_kalibrasi_terakhir' => ['label' => 'Tgl Kalibrasi Terakhir', 'type' => 'date', 'required' => false, 'in_unit' => true, 'show_if' => ['butuh_kalibrasi' => ['Ya']]],
                'no_sertifikat_kalibrasi' => ['label' => 'No. Sertifikat Kalibrasi', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['butuh_kalibrasi' => ['Ya']]],
            ],

            // =================================================================
            // 6.5 MEB — Furniture/Mebel
            // =================================================================
            'MEB' => [
                'hide_general' => true, // Mebel tanpa merek/serial number
                'subkategori' => ['label' => 'Jenis Mebel', 'type' => 'select', 'options' => [
                    'meja' => 'Meja', 'kursi' => 'Kursi', 'lemari' => 'Lemari', 'rak' => 'Rak', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],

                'bahan_utama' => ['label' => 'Bahan Utama', 'type' => 'select', 'options' => ['Kayu' => 'Kayu', 'Besi' => 'Besi', 'Kaca' => 'Kaca', 'Plastik' => 'Plastik', 'Kombinasi' => 'Kombinasi'], 'required' => true, 'in_batch' => true],
                'dimensi' => ['label' => 'Dimensi (P x L x T cm)', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'kapasitas' => ['label' => 'Kapasitas/Day tampung', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'warna' => ['label' => 'Warna', 'type' => 'text', 'required' => false, 'in_batch' => true],
            ],

            // =================================================================
            // 6.6 ARS — Koleksi Perpustakaan & Arsip
            // =================================================================
            'ARS' => [
                'hide_general' => true,
                'subkategori' => ['label' => 'Jenis Koleksi', 'type' => 'select', 'options' => [
                    'pustaka' => 'Bahan Pustaka (Buku/Jurnal)', 'arsip' => 'Arsip Dokumen',
                ], 'required' => true, 'in_batch' => true],

                // Bahan Pustaka
                'penulis' => ['label' => 'Penulis / Kontributor', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'isbn' => ['label' => 'ISBN / ISSN', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'penerbit' => ['label' => 'Penerbit', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'tahun_terbit' => ['label' => 'Tahun Terbit', 'type' => 'number', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'edisi' => ['label' => 'Edisi / Volume', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'bahasa' => ['label' => 'Bahasa', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'klasifikasi' => ['label' => 'Klasifikasi Koleksi', 'type' => 'text', 'required' => false, 'in_batch' => true],

                // Arsip
                'pencipta_arsip' => ['label' => 'Pencipta / Unit Penghasil Arsip', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['arsip']]],
                'rentang_waktu' => ['label' => 'Tanggal / Rentang Waktu', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['arsip']]],
                'tingkat_akses' => ['label' => 'Tingkat Akses (Biasa/Rahasia)', 'type' => 'select', 'options' => ['Biasa' => 'Biasa', 'Rahasia' => 'Rahasia'], 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['arsip']]],
                'masa_retensi' => ['label' => 'Masa Retensi (Tahun)', 'type' => 'number', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['arsip']]],

                // Per unit
                'nomor_aksesion' => ['label' => 'No. Aksesion / Katalog / Kode Arsip', 'type' => 'text', 'required' => true, 'in_unit' => true],
            ],

            // =================================================================
            // 6.7 OTO — Kendaraan Operasional
            // =================================================================
            'OTO' => [
                'hide_general' => true, // Diganti field khusus kendaraan
                'subkategori' => ['label' => 'Jenis Kendaraan', 'type' => 'select', 'options' => [
                    'mobil' => 'Mobil', 'motor' => 'Motor', 'truk' => 'Truk', 'bus' => 'Bus', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],

                'merek' => ['label' => 'Merek Kendaraan', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'model' => ['label' => 'Tipe / Model', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'tahun' => ['label' => 'Tahun Pembuatan', 'type' => 'number', 'required' => true, 'in_batch' => true],
                'bahan_bakar' => ['label' => 'Bahan Bakar', 'type' => 'select', 'options' => ['Bensin' => 'Bensin', 'Solar' => 'Solar', 'Listrik' => 'Listrik', 'Hybrid' => 'Hybrid'], 'required' => false, 'in_batch' => true],
                'kapasitas_mesin' => ['label' => 'Kapasitas Mesin (cc)', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'transmisi' => ['label' => 'Transmisi', 'type' => 'select', 'options' => ['Manual' => 'Manual', 'Otomatis' => 'Otomatis'], 'required' => false, 'in_batch' => true],
                'warna' => ['label' => 'Warna', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'kapasitas_penumpang' => ['label' => 'Kapasitas Penumpang', 'type' => 'number', 'required' => false, 'in_batch' => true],

                // Administrasi & Per Unit
                'no_rangka' => ['label' => 'Nomor Rangka (VIN)', 'type' => 'text', 'required' => true, 'in_unit' => true],
                'no_mesin' => ['label' => 'Nomor Mesin', 'type' => 'text', 'required' => true, 'in_unit' => true],
                'no_polisi' => ['label' => 'Nomor Polisi', 'type' => 'text', 'required' => true, 'in_unit' => true],
                'odometer' => ['label' => 'Odometer / KM Terakhir', 'type' => 'number', 'required' => false, 'in_unit' => true],
                'masa_berlaku_stnk' => ['label' => 'Masa Berlaku STNK', 'type' => 'date', 'required' => false, 'in_unit' => true],
            ],

            // =================================================================
            // 6.8 TNH — Tanah & Lahan (Max Qty 1)
            // =================================================================
            'TNH' => [
                'hide_general' => true,
                'max_qty' => 1, // Kunci kuantitas di 1
                'is_static_asset' => true,
                'subkategori' => ['label' => 'Status Kepemilikan', 'type' => 'select', 'options' => [
                    'HM' => 'Hak Milik', 'HGB' => 'Hak Guna Bangunan', 'HGU' => 'Hak Guna Usaha', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],

                'luas_m2' => ['label' => 'Luas Tanah (m²)', 'type' => 'number', 'required' => true, 'in_batch' => true],
                'no_sertifikat' => ['label' => 'Nomor Sertifikat', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'lokasi_administratif' => ['label' => 'Alamat / Kelurahan, Kecamatan', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'koordinat' => ['label' => 'Koordinat Peta (Opsional)', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'peruntukan' => ['label' => 'Peruntukan Saat Ini', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'pemegang_hak' => ['label' => 'Pemegang Hak', 'type' => 'text', 'required' => false, 'in_batch' => true],
            ],

            // =================================================================
            // 6.9 GDG — Gedung & Bangunan (Max Qty 1)
            // =================================================================
            'GDG' => [
                'hide_general' => true,
                'max_qty' => 1, // Kunci kuantitas di 1
                'is_static_asset' => true,
                'subkategori' => ['label' => 'Jenis Bangunan', 'type' => 'select', 'options' => [
                    'perkantoran' => 'Perkantoran', 'laboratorium' => 'Laboratorium', 'kelas' => 'Ruang Kelas', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],

                'luas_bangunan' => ['label' => 'Luas Bangunan (m²)', 'type' => 'number', 'required' => true, 'in_batch' => true],
                'jumlah_lantai' => ['label' => 'Jumlah Lantai', 'type' => 'number', 'required' => true, 'in_batch' => true],
                'tahun_pembangunan' => ['label' => 'Tahun Pembangunan', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'no_izin_bangunan' => ['label' => 'No. IMB / PBG', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'status_kepemilikan' => ['label' => 'Status Kepemilikan', 'type' => 'text', 'required' => false, 'in_batch' => true],
            ],

            // =================================================================
            // 6.10 PRS — Prasarana Umum
            // =================================================================
            'PRS' => [
                'hide_general' => true,
                'subkategori' => ['label' => 'Jenis Prasarana', 'type' => 'select', 'options' => [
                    'listrik' => 'Instalasi Listrik', 'air' => 'Instalasi Air', 'jalan' => 'Jalan / Drainase', 'jaringan' => 'Jaringan Utilitas',
                ], 'required' => true, 'in_batch' => true],

                'panjang_meter' => ['label' => 'Panjang (meter)', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'kapasitas_volume' => ['label' => 'Kapasitas / Volume', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'material_utama' => ['label' => 'Material Utama', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'tahun_pemasangan' => ['label' => 'Tahun Pemasangan', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'cakupan_wilayah' => ['label' => 'Cakupan Wilayah', 'type' => 'text', 'required' => false, 'in_batch' => true],
            ],

            // =================================================================
            // 6.11 FUR — Furniture (Sama seperti MEB)
            // =================================================================
            'FUR' => [
                'hide_general' => true,
                'subkategori' => ['label' => 'Jenis Furniture', 'type' => 'select', 'options' => [
                    'meja' => 'Meja', 'kursi' => 'Kursi', 'lemari' => 'Lemari', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],
                'bahan_utama' => ['label' => 'Bahan Utama', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'dimensi' => ['label' => 'Dimensi', 'type' => 'text', 'required' => false, 'in_batch' => true],
            ],

            // =================================================================
            // 6.12 ATK — Alat & Perkakas
            // =================================================================
            'ATK' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Kategori Perkakas', 'type' => 'select', 'options' => [
                    'tangan' => 'Peralatan Tangan', 'listrik' => 'Peralatan Listrik', 'ukur' => 'Alat Ukur', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],

                'ukuran' => ['label' => 'Ukuran / Spesifikasi', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'material' => ['label' => 'Material', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'daya_listrik' => ['label' => 'Daya Listrik (Watt)', 'type' => 'number', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['listrik']]],
            ],

            // =================================================================
            // 6.13 LAN — Lainnya
            // =================================================================
            'LAN' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Jenis Aset', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'deskripsi_fungsi' => ['label' => 'Deskripsi & Fungsi Aset', 'type' => 'textarea', 'required' => true, 'in_batch' => true],
                'alasan_lainnya' => ['label' => 'Alasan Masuk Kategori Lainnya', 'type' => 'text', 'required' => false, 'in_batch' => true],
            ],
        ];

        // Default schema jika kode tidak ditemukan
        return $schemas[strtoupper($code)] ?? [
            'hide_general' => false,
        ];
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class);
    }
}
