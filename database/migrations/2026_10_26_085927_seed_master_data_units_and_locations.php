<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. JENIS ASET (Asset Types)
        $assetTypes = [
            ['code' => 'KOM', 'name' => 'Komputer & Periferal', 'description' => 'Laptop, PC, Monitor, Printer, dll'],
            ['code' => 'ELK', 'name' => 'Elektronik Umum', 'description' => 'Sensor, Modul, Komponen Elektronika'],
            ['code' => 'JAR', 'name' => 'Jaringan', 'description' => 'Router, Switch, Kabel LAN, FO'],
            ['code' => 'FUR', 'name' => 'Furniture', 'description' => 'Meja, Kursi, Lemari, Papan Tulis'],
            ['code' => 'ATK', 'name' => 'Alat & Perkakas', 'description' => 'Obeng, Tang, Crimping Tool'],
            ['code' => 'LAN', 'name' => 'Lainnya', 'description' => 'Barang-barang umum lainnya'],
        ];
        foreach ($assetTypes as $type) {
            DB::table('asset_types')->updateOrInsert(['code' => $type['code']], $type);
        }

        // 2. UNIT & KATEGORI (Sesuai Tabel Markdown)
        $units = [
            // Unit Akademik (Program Studi)
            ['name' => 'Teknik Mesin', 'type' => 'Prodi', 'category' => 'Unit Akademik (Program Studi)'],
            ['name' => 'Teknik Elektro', 'type' => 'Prodi', 'category' => 'Unit Akademik (Program Studi)'],
            ['name' => 'Teknik Informatika', 'type' => 'Prodi', 'category' => 'Unit Akademik (Program Studi)'],

            // Unsur Pimpinan
            ['name' => 'Rektorat', 'type' => 'Pimpinan', 'category' => 'Unsur Pimpinan'],

            // UPT (Unit Pelaksana Teknis)
            ['name' => 'Unit IT & Jaringan', 'type' => 'UPT', 'category' => 'UPT (Unit Pelaksana Teknis)'],

            // Unit Sarana & Prasarana
            ['name' => 'Fasilitas Umum', 'type' => 'Sarpras', 'category' => 'Unit Sarana & Prasarana'],

            // Unit Layanan / Kemahasiswaan
            ['name' => 'Pelayanan Mahasiswa', 'type' => 'Kemahasiswaan', 'category' => 'Unit Layanan / Kemahasiswaan'],

            // UPA (Unit Pelaksana Administrasi)
            ['name' => 'Unit Kerja Administratif', 'type' => 'Administrasi', 'category' => 'UPA (Unit Pelaksana Administrasi)'],
        ];
        foreach ($units as $unit) {
            DB::table('units')->updateOrInsert(['name' => $unit['name']], $unit);
        }

        // 3. LOKASI SPESIFIK (Sesuai Tabel Markdown)
        $locationsMap = [
            'Teknik Mesin' => ['Ruang Dosen Prodi Teknik Mesin', 'Lab. Teknik Mesin', 'Perpustakaan Teknik Mesin', 'Ruang Kaprodi Teknik Mesin'],
            'Teknik Elektro' => ['Ruang Dosen & Praktisi Prodi Teknik Elektro', 'Lab. Teknik Elektro', 'Lab. Teknik Elektro - Unit Workshop', 'Perpustakaan Teknik Elektro', 'Ruang Kaprodi Teknik Elektro'],
            'Teknik Informatika' => ['Ruang Dosen Prodi Teknik Informatika', 'Lab. Komputer', 'Perpustakaan Teknik Informatika', 'Ruang Kaprodi Teknik Informatika'],
            'Rektorat' => ['Ruang Rektor', 'Toilet Rektor', 'Ruang Wakil Rektor', 'Gedung Rektorat'],
            'Unit IT & Jaringan' => ['Ruang Server & Jaringan'],
            'Fasilitas Umum' => ['Ruang Kelas 001', 'Ruang Kelas 002', 'Ruang Kelas 003', 'Ruang Kelas 004', 'Ruang Kelas 005', 'Perpustakaan Umum', 'Gudang', 'Toilet Umum 01', 'Toilet Umum 02'],
            'Pelayanan Mahasiswa' => ['Asrama Mahasiswa', 'Toilet Mahasiswa 01', 'Toilet Mahasiswa 02'],
            'Unit Kerja Administratif' => ['Ruang Dosen MKDU/MKU/MKWU', 'Ruang Administrasi (BAAK, SDM, Bag. Keuangan)', 'Toilet Dosen 01', 'Toilet Dosen 02'],
        ];

        foreach ($locationsMap as $unitName => $locations) {
            // Ambil ID Unit
            $unit = DB::table('units')->where('name', $unitName)->first();
            if ($unit) {
                foreach ($locations as $locName) {
                    DB::table('locations')->updateOrInsert(
                        ['unit_id' => $unit->id, 'name' => $locName],
                        [
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        // Kosongkan karena ini data master
    }
};
