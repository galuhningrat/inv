<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Buat Unit & Kategori baru (Rektorat, UPT, Prasarana, dll)
            RestructureUnitsSeeder::class,

            // 2. Buat Lokasi Spesifik (Ruang Server, Lab Komputer, dll)
            LocationSeeder::class,

            // 3. Buat User Dasar (Admin, Rektor, Sarpras)
            UserSeeder::class,

            // 4. Buat Stakeholder (Dosen, Kaprodi, Kalab) dan hubungkan ke Unit
            StakeholderSeeder::class,
        ]);
    }
}
