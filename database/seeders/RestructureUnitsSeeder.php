<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\Location;

class RestructureUnitsSeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            'Non-Akademik' => [
                'Pimpinan Sekolah Tinggi (Rektorat/Ketua)' => [
                    'Ruang Ketua Sekolah Tinggi',
                    'Ruang Wakil Ketua I (Akademik)',
                    'Ruang Wakil Ketua II (Administrasi & Keuangan)',
                    'Ruang Wakil Ketua III (Kemahasiswaan)',
                    'Toilet Khusus Pimpinan',
                ],
                'Unit Administrasi Umum & Keuangan (BAUK)' => [
                    'Ruang Administrasi Pusat',
                    'Ruang Arsip & Dokumen',
                    'Gudang Aset Pusat',
                ],
            ],
            'UPT' => [
                'UPT Perpustakaan Pusat' => [
                    'Ruang Sirkulasi Perpustakaan Umum',
                    'Ruang Baca & Diskusi',
                ],
                'UPT Sistem Informasi & Komputer (Unit IT)' => [
                    'Ruang Server Utama & Jaringan Campus Core',
                    'Ruang Workshop Perbaikan Perangkat IT',
                ],
                'Unit Mata Kuliah Umum (MKU/MKDU)' => [
                    'Ruang Dosen MKDU/MKU/MKWU',
                ],
            ],
            'Prasarana' => [
                'Gedung Kelas Bersama (Gedung Kuliah)' => [
                    'Ruang Kelas 001',
                    'Ruang Kelas 002',
                    'Ruang Kelas 003',
                    'Ruang Kelas 004',
                    'Ruang Kelas 005',
                ],
                'UPT Layanan Mahasiswa & Asrama' => [
                    'Asrama Mahasiswa',
                ],
                'Fasilitas Sanitasi Umum' => [
                    'Toilet Mahasiswa 01 (Pria)',
                    'Toilet Mahasiswa 02 (Wanita)',
                    'Toilet Dosen 01 (Pria)',
                    'Toilet Dosen 02 (Wanita)',
                    'Toilet Umum 01',
                    'Toilet Umum 02',
                ],
            ],
        ];

        foreach ($structure as $category => $units) {
            foreach ($units as $unitName => $locations) {
                $unit = Unit::firstOrCreate(
                    ['name' => $unitName],
                    ['type' => 'Umum', 'category' => $category]
                );
                foreach ($locations as $loc) {
                    Location::firstOrCreate(['unit_id' => $unit->id, 'name' => $loc]);
                }
            }
        }
    }
}