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

    public static function getSchema($code)
    {
        $schemas = [
            'KOM' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Jenis Perangkat', 'type' => 'select', 'options' => [
                    'laptop' => 'Laptop', 'desktop' => 'Desktop', 'server' => 'Server',
                    'monitor' => 'Monitor', 'printer' => 'Printer', 'keyboard' => 'Keyboard', 'mouse' => 'Mouse', 'lainnya' => 'Periferal Lainnya',
                ], 'required' => true, 'in_batch' => true],

                // 🔥 SPESIFIKASI TEKNIS DIPINDAH KE IN_UNIT 🔥
                'processor' => ['label' => 'Prosesor / Chipset', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'ram' => ['label' => 'Kapasitas RAM (GB)', 'type' => 'number', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'storage' => ['label' => 'Penyimpanan (SSD/HDD)', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'gpu' => ['label' => 'GPU / Grafis', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'os' => ['label' => 'Sistem Operasi & Versi', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'ukuran_layar' => ['label' => 'Ukuran Layar (Inch)', 'type' => 'number', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['laptop', 'monitor']]],
                'resolusi_layar' => ['label' => 'Resolusi Layar', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['laptop', 'monitor']]],

                // Garansi tetap di batch (biasanya 1 batch garansinya sama)
                'garansi_berakhir' => ['label' => 'Masa Garansi Berakhir', 'type' => 'date', 'required' => false, 'in_batch' => true],

                'hostname' => ['label' => 'Hostname / Nama Komputer', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
                'mac_address' => ['label' => 'MAC Address', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['laptop', 'desktop', 'server']]],
            ],

            'JAR' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Jenis Perangkat Jaringan', 'type' => 'select', 'options' => [
                    'router' => 'Router', 'switch' => 'Switch', 'ap' => 'Access Point', 'modem' => 'Modem / ONT', 'firewall' => 'Firewall',
                    'kabel' => 'Kabel / Fiber Optic', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],

                // 🔥 SPESIFIKASI TEKNIS DIPINDAH KE IN_UNIT 🔥
                'jumlah_port' => ['label' => 'Jumlah Port', 'type' => 'number', 'required' => false, 'in_unit' => true],
                'kecepatan' => ['label' => 'Kecepatan Port (Mbps/Gbps)', 'type' => 'text', 'required' => false, 'in_unit' => true],
                'dukungan_poe' => ['label' => 'Dukungan PoE', 'type' => 'select', 'options' => ['Ya' => 'Ya', 'Tidak' => 'Tidak'], 'required' => false, 'in_unit' => true],
                'versi_firmware' => ['label' => 'Versi Firmware', 'type' => 'text', 'required' => false, 'in_unit' => true],

                // Field unit lainnya
                'lokasi_rak' => ['label' => 'Nomor Rak / Identitas', 'type' => 'text', 'required' => false, 'in_unit' => true],
                'mac_address' => ['label' => 'MAC Address', 'type' => 'text', 'required' => false, 'in_unit' => true],
                'ip_address' => ['label' => 'IP Address', 'type' => 'text', 'required' => false, 'in_unit' => true],
            ],

            'ELK' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Jenis Elektronik', 'type' => 'select', 'options' => [
                    'proyektor' => 'Proyektor', 'tv' => 'TV / Layar', 'kamera' => 'Kamera', 'audio' => 'Audio', 'ac' => 'AC', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],

                // 🔥 SPESIFIKASI TEKNIS DIPINDAH KE IN_UNIT 🔥
                'daya_listrik' => ['label' => 'Daya Listrik (Watt)', 'type' => 'number', 'required' => false, 'in_unit' => true],
                'tegangan' => ['label' => 'Tegangan (Volt)', 'type' => 'number', 'required' => false, 'in_unit' => true],
                'kapasitas_pendingin' => ['label' => 'Kapasitas Pendingin (PK)', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['subkategori' => ['ac']]],
                'tahun_pembuatan' => ['label' => 'Tahun Pembuatan', 'type' => 'number', 'required' => false, 'in_unit' => true],
            ],

            'LAB' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Jenis Instrumen', 'type' => 'select', 'options' => [
                    'ukur' => 'Alat Ukur', 'eksperimen' => 'Peralatan Eksperimen', 'mikroskop' => 'Mikroskop / Optik', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],

                // 🔥 SPESIFIKASI TEKNIS DIPINDAH KE IN_UNIT 🔥
                'fungsi_alat' => ['label' => 'Fungsi Instrumen', 'type' => 'text', 'required' => false, 'in_unit' => true],
                'rentang_ukur' => ['label' => 'Rentang Pengukuran', 'type' => 'text', 'required' => false, 'in_unit' => true],
                'akurasi' => ['label' => 'Akurasi / Ketelitian', 'type' => 'text', 'required' => false, 'in_unit' => true],
                'satuan_ukur' => ['label' => 'Satuan Pengukuran', 'type' => 'text', 'required' => false, 'in_unit' => true],

                // Kalibrasi tetap di batch sebagai penentu utama
                'butuh_kalibrasi' => ['label' => 'Memerlukan Kalibrasi?', 'type' => 'select', 'options' => ['Ya' => 'Ya', 'Tidak' => 'Tidak'], 'required' => true, 'in_batch' => true],
                'interval_kalibrasi' => ['label' => 'Interval (Bulan)', 'type' => 'number', 'required' => false, 'in_batch' => true, 'show_if' => ['butuh_kalibrasi' => ['Ya']]],

                // Hasil kalibrasi per unit
                'tgl_kalibrasi_terakhir' => ['label' => 'Tgl Kalibrasi Terakhir', 'type' => 'date', 'required' => false, 'in_unit' => true, 'show_if' => ['butuh_kalibrasi' => ['Ya']]],
                'no_sertifikat_kalibrasi' => ['label' => 'No. Sertifikat Kalibrasi', 'type' => 'text', 'required' => false, 'in_unit' => true, 'show_if' => ['butuh_kalibrasi' => ['Ya']]],
            ],

            'MEB' => [
                'hide_general' => true,
                'subkategori' => ['label' => 'Jenis Mebel', 'type' => 'select', 'options' => [
                    'meja' => 'Meja', 'kursi' => 'Kursi', 'lemari' => 'Lemari', 'rak' => 'Rak', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],
                'bahan_utama' => ['label' => 'Bahan Utama', 'type' => 'select', 'options' => ['Kayu' => 'Kayu', 'Besi' => 'Besi', 'Kaca' => 'Kaca', 'Plastik' => 'Plastik', 'Kombinasi' => 'Kombinasi'], 'required' => true, 'in_batch' => true],
                'dimensi' => ['label' => 'Dimensi (P x L x T cm)', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'kapasitas' => ['label' => 'Kapasitas/Day tampung', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'warna' => ['label' => 'Warna', 'type' => 'text', 'required' => false, 'in_batch' => true],
            ],

            'ARS' => [
                'hide_general' => true,
                'subkategori' => ['label' => 'Jenis Koleksi', 'type' => 'select', 'options' => [
                    'pustaka' => 'Bahan Pustaka (Buku/Jurnal)', 'arsip' => 'Arsip Dokumen',
                ], 'required' => true, 'in_batch' => true],
                'penulis' => ['label' => 'Penulis / Kontributor', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'isbn' => ['label' => 'ISBN / ISSN', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'penerbit' => ['label' => 'Penerbit', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'tahun_terbit' => ['label' => 'Tahun Terbit', 'type' => 'number', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'edisi' => ['label' => 'Edisi / Volume', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'bahasa' => ['label' => 'Bahasa', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['pustaka']]],
                'klasifikasi' => ['label' => 'Klasifikasi Koleksi', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'pencipta_arsip' => ['label' => 'Pencipta / Unit Penghasil Arsip', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['arsip']]],
                'rentang_waktu' => ['label' => 'Tanggal / Rentang Waktu', 'type' => 'text', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['arsip']]],
                'tingkat_akses' => ['label' => 'Tingkat Akses (Biasa/Rahasia)', 'type' => 'select', 'options' => ['Biasa' => 'Biasa', 'Rahasia' => 'Rahasia'], 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['arsip']]],
                'masa_retensi' => ['label' => 'Masa Retensi (Tahun)', 'type' => 'number', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['arsip']]],
                'nomor_aksesion' => ['label' => 'No. Aksesion / Katalog / Kode Arsip', 'type' => 'text', 'required' => true, 'in_unit' => true],
            ],

            'OTO' => [
                'hide_general' => true,
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
                'no_rangka' => ['label' => 'Nomor Rangka (VIN)', 'type' => 'text', 'required' => true, 'in_unit' => true],
                'no_mesin' => ['label' => 'Nomor Mesin', 'type' => 'text', 'required' => true, 'in_unit' => true],
                'no_polisi' => ['label' => 'Nomor Polisi', 'type' => 'text', 'required' => true, 'in_unit' => true],
                'odometer' => ['label' => 'Odometer / KM Terakhir', 'type' => 'number', 'required' => false, 'in_unit' => true],
                'masa_berlaku_stnk' => ['label' => 'Masa Berlaku STNK', 'type' => 'date', 'required' => false, 'in_unit' => true],
                'file_stnk' => ['label' => 'Upload STNK (PDF/Gambar)', 'type' => 'file', 'required' => false, 'in_unit' => true],
            ],

            'TNH' => [
                'hide_general' => true,
                'max_qty' => 1,
                'is_static_asset' => true,
                'hide_condition' => true,
                'hide_image' => true,
                'subkategori' => ['label' => 'Status Kepemilikan', 'type' => 'select', 'options' => [
                    'HM' => 'Hak Milik', 'HGB' => 'Hak Guna Bangunan', 'HGU' => 'Hak Guna Usaha', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],
                'luas_m2' => ['label' => 'Luas Tanah (m²)', 'type' => 'number', 'required' => true, 'in_batch' => true],
                'no_sertifikat' => ['label' => 'Nomor Sertifikat', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'lokasi_administratif' => ['label' => 'Alamat / Kelurahan, Kecamatan', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'koordinat' => ['label' => 'Koordinat Peta (Opsional)', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'peruntukan' => ['label' => 'Peruntukan Saat Ini', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'pemegang_hak' => ['label' => 'Pemegang Hak', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'file_sertifikat' => ['label' => 'Upload Sertifikat Tanah (PDF/Gambar)', 'type' => 'file', 'required' => false, 'in_batch' => true],
                'foto_lokasi_tanah' => [
                    'label' => 'Upload Foto Lokasi Tanah (Bisa lebih dari 1)',
                    'type' => 'file',
                    'required' => false,
                    'in_batch' => true,
                    'multiple' => true,
                ],
            ],

            'GDG' => [
                'hide_general' => true,
                'max_qty' => 1,
                'is_static_asset' => true,
                'hide_condition' => true,
                'hide_image' => true,
                'subkategori' => ['label' => 'Jenis Bangunan', 'type' => 'select', 'options' => [
                    'perkantoran' => 'Perkantoran', 'laboratorium' => 'Laboratorium', 'kelas' => 'Ruang Kelas', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],
                'luas_bangunan' => ['label' => 'Luas Bangunan (m²)', 'type' => 'number', 'required' => true, 'in_batch' => true],
                'jumlah_lantai' => ['label' => 'Jumlah Lantai', 'type' => 'number', 'required' => true, 'in_batch' => true],
                'tahun_pembangunan' => ['label' => 'Tahun Pembangunan', 'type' => 'number', 'required' => false, 'in_batch' => true],
                'no_izin_bangunan' => ['label' => 'No. IMB / PBG', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'status_kepemilikan' => ['label' => 'Status Kepemilikan', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'file_imb' => ['label' => 'Upload IMB/PBG (PDF/Gambar)', 'type' => 'file', 'required' => false, 'in_batch' => true],
                'foto_gedung' => [
                    'label' => 'Upload Foto Gedung/Bangunan (Bisa lebih dari 1)',
                    'type' => 'file',
                    'required' => false,
                    'in_batch' => true,
                    'multiple' => true,
                ],
            ],

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

            'FUR' => [
                'hide_general' => true,
                'subkategori' => ['label' => 'Jenis Furniture', 'type' => 'select', 'options' => [
                    'meja' => 'Meja', 'kursi' => 'Kursi', 'lemari' => 'Lemari', 'rak' => 'Rak', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],
                'bahan_utama' => ['label' => 'Bahan Utama', 'type' => 'select', 'options' => ['Kayu' => 'Kayu', 'Besi' => 'Besi', 'Kaca' => 'Kaca', 'Plastik' => 'Plastik', 'Kombinasi' => 'Kombinasi'], 'required' => true, 'in_batch' => true],
                'dimensi' => ['label' => 'Dimensi (P x L x T cm)', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'kapasitas' => ['label' => 'Kapasitas/Day tampung', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'warna' => ['label' => 'Warna', 'type' => 'text', 'required' => false, 'in_batch' => true],
            ],

            'ATK' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Kategori Perkakas/ATK', 'type' => 'select', 'options' => [
                    'atk' => 'Alat Tulis Kantor (ATK)',
                    'tangan' => 'Peralatan Tangan', 'listrik' => 'Peralatan Listrik', 'ukur' => 'Alat Ukur', 'lainnya' => 'Lainnya',
                ], 'required' => true, 'in_batch' => true],
                'ukuran' => ['label' => 'Ukuran / Spesifikasi', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'material' => ['label' => 'Material', 'type' => 'text', 'required' => false, 'in_batch' => true],
                'daya_listrik' => ['label' => 'Daya Listrik (Watt)', 'type' => 'number', 'required' => false, 'in_batch' => true, 'show_if' => ['subkategori' => ['listrik']]],
            ],

            'LAN' => [
                'hide_general' => false,
                'subkategori' => ['label' => 'Jenis Aset', 'type' => 'text', 'required' => true, 'in_batch' => true],
                'deskripsi_fungsi' => ['label' => 'Deskripsi & Fungsi Aset', 'type' => 'textarea', 'required' => true, 'in_batch' => true],
                'alasan_lainnya' => ['label' => 'Alasan Masuk Kategori Lainnya', 'type' => 'text', 'required' => false, 'in_batch' => true],
            ],
        ];

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
