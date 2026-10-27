<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StakeholderSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cari Unit yang sudah ada (menggunakan LIKE agar fleksibel)
        $unitRektorat = Unit::where('name', 'LIKE', '%Rektorat%')->first() ?? Unit::create(['name' => 'Rektorat', 'category' => 'Pimpinan', 'type' => 'biro']);
        $unitSarpras = Unit::where('name', 'LIKE', '%Fasilitas Umum%')->first() ?? Unit::create(['name' => 'Fasilitas Umum', 'category' => 'Sarpras', 'type' => 'biro']);
        $unitTI = Unit::where('name', 'LIKE', '%Informatika%')->first() ?? Unit::create(['name' => 'Teknik Informatika', 'category' => 'Akademik', 'type' => 'prodi']);
        $unitTE = Unit::where('name', 'LIKE', '%Elektro%')->first() ?? Unit::create(['name' => 'Teknik Elektro', 'category' => 'Akademik', 'type' => 'prodi']);
        $unitTM = Unit::where('name', 'LIKE', '%Mesin%')->first() ?? Unit::create(['name' => 'Teknik Mesin', 'category' => 'Akademik', 'type' => 'prodi']);
        $unitUPA = Unit::where('name', 'LIKE', '%Administratif%')->first() ?? Unit::create(['name' => 'Unit Kerja Administratif', 'category' => 'Administrasi', 'type' => 'biro']);

        // 2. Daftar Stakeholder (Pisahkan yang double seperti Tami & Ade)
        $users = [
            ['name' => 'Sugiharto', 'username' => 'sugiharto', 'email' => 'sugiharto@stti.ac.id', 'level' => 'Ketua', 'unit_id' => $unitRektorat->id],
            ['name' => 'Pak Harry', 'username' => 'harry', 'email' => 'harry@stti.ac.id', 'level' => 'Sarana dan Prasarana', 'unit_id' => $unitSarpras->id],
            ['name' => 'Pak Rizal', 'username' => 'rizal', 'email' => 'rizal@stti.ac.id', 'level' => 'Penanggung Jawab Pengadaan', 'unit_id' => $unitSarpras->id],
            ['name' => 'Pak Zaky', 'username' => 'zaky', 'email' => 'zaky@stti.ac.id', 'level' => 'Tim Pemeliharaan', 'unit_id' => $unitSarpras->id],

            ['name' => 'Pak Siswo', 'username' => 'siswo', 'email' => 'siswo@stti.ac.id', 'level' => 'Kepala Laboratorium', 'unit_id' => $unitTI->id],
            ['name' => 'Pak Riyan', 'username' => 'riyan', 'email' => 'riyan@stti.ac.id', 'level' => 'Kepala Laboratorium', 'unit_id' => $unitTE->id],
            ['name' => 'Pak Afif', 'username' => 'afif', 'email' => 'afif@stti.ac.id', 'level' => 'Kepala Laboratorium', 'unit_id' => $unitTM->id],

            ['name' => 'Pak Bima', 'username' => 'bima', 'email' => 'bima@stti.ac.id', 'level' => 'Kepala Program Studi', 'unit_id' => $unitTI->id],
            ['name' => 'Pak Suharno', 'username' => 'suharno', 'email' => 'suharno@stti.ac.id', 'level' => 'Kepala Program Studi', 'unit_id' => $unitTE->id],
            ['name' => 'Pak Elya', 'username' => 'elya', 'email' => 'elya@stti.ac.id', 'level' => 'Kepala Program Studi', 'unit_id' => $unitTM->id],

            ['name' => 'Bu Nazilah', 'username' => 'nazilah', 'email' => 'nazilah@stti.ac.id', 'level' => 'Keuangan', 'unit_id' => $unitUPA->id],
            ['name' => 'Bu Tami', 'username' => 'tami', 'email' => 'tami@stti.ac.id', 'level' => 'Administrasi', 'unit_id' => $unitUPA->id],
            ['name' => 'Pak Ade', 'username' => 'ade', 'email' => 'ade@stti.ac.id', 'level' => 'Administrasi', 'unit_id' => $unitUPA->id],

            ['name' => 'Mas Galuh', 'username' => 'galuh', 'email' => 'galuh@stti.ac.id', 'level' => 'Asisten Laboratorium', 'unit_id' => $unitTI->id],
            ['name' => 'Mas Samsul', 'username' => 'samsul', 'email' => 'samsul@stti.ac.id', 'level' => 'Asisten Laboratorium', 'unit_id' => $unitTE->id],
            ['name' => 'Mas Banu', 'username' => 'banu', 'email' => 'banu@stti.ac.id', 'level' => 'Asisten Laboratorium', 'unit_id' => $unitTE->id],

            ['name' => 'Uciha Agi', 'username' => 'agi', 'email' => 'agi@stti.ac.id', 'level' => 'Asisten Laboratorium', 'unit_id' => $unitTM->id],
            ['name' => 'King Salman', 'username' => 'salman', 'email' => 'salman@stti.ac.id', 'level' => 'Asisten Laboratorium', 'unit_id' => $unitTM->id],
            ['name' => 'Mang Kamal', 'username' => 'kamal', 'email' => 'kamal@stti.ac.id', 'level' => 'Asisten Laboratorium', 'unit_id' => $unitTM->id],
            ['name' => 'Mang Dzikri', 'username' => 'dzikri', 'email' => 'dzikri@stti.ac.id', 'level' => 'Asisten Laboratorium', 'unit_id' => $unitTM->id],
        ];

        // 3. Insert ke Database
        foreach ($users as $u) {
            User::create([
                'name' => $u['name'],
                'username' => $u['username'],
                'email' => $u['email'],
                'password' => Hash::make('password123'), // Default password
                'level' => $u['level'],
                'unit_id' => $u['unit_id'],
                'status' => 'Aktif',
            ]);
        }
    }
}
