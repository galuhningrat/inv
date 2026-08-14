<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // 5 unit penunjang baru
        $newUnits = ['Rektorat', 'Unit IT & Jaringan', 'Fasilitas Umum', 'Pelayanan Mahasiswa', 'Unit Kerja Administratif'];
        foreach ($newUnits as $name) {
            Unit::firstOrCreate(['name' => $name], ['type' => 'Umum']);
        }

        $map = [
            'Teknik Mesin' => ['Ruang Dosen Prodi Teknik Mesin', 'Lab. Teknik Mesin', 'Perpustakaan Teknik Mesin', 'Ruang Kaprodi Teknik Mesin'],
            'Teknik Elektro' => ['Ruang Dosen & Praktisi Prodi Teknik Elektro', 'Lab. Teknik Elektro', 'Lab. Teknik Elektro - Unit Workshop', 'Perpustakaan Teknik Elektro', 'Ruang Kaprodi Teknik Elektro'],
            'Teknik Informatika' => ['Ruang Dosen Prodi Teknik Informatika', 'Lab. Komputer', 'Perpustakaan Teknik Informatika', 'Ruang Kaprodi Teknik Informatika'],
            'Rektorat' => ['Ruang Rektor', 'Toilet Rektor', 'Ruang Wakil Rektor', 'Gedung Rektorat'],
            'Unit IT & Jaringan' => ['Ruang Server & Jaringan'],
            'Fasilitas Umum' => ['Ruang Kelas 001', 'Ruang Kelas 002', 'Ruang Kelas 003', 'Ruang Kelas 004', 'Ruang Kelas 005', 'Perpustakaan Umum', 'Gudang', 'Toilet Umum 01', 'Toilet Umum 02'],
            'Pelayanan Mahasiswa' => ['Asrama Mahasiswa', 'Toilet Mahasiswa 01', 'Toilet Mahasiswa 02'],
            'Unit Kerja Administratif' => ['Ruang Dosen MKDU/MKU/MKWU', 'Ruang Administrasi (BAAK, SDM, Bag. Keuangan)', 'Toilet Dosen 01', 'Toilet Dosen 02'],
        ];

        foreach ($map as $unitName => $locations) {
            $unit = Unit::where('name', $unitName)->first();
            if (!$unit)
                continue;
            foreach ($locations as $loc) {
                Location::firstOrCreate(['unit_id' => $unit->id, 'name' => $loc]);
            }
        }
    }
}