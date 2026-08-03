<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AssetTypeSeeder::class, // Harus pertama
            UserSeeder::class,      // Harus kedua (untuk foreign key peminjaman/request)
            AssetSeeder::class,     // Terakhir, karena butuh AssetType
        ]);
    }
}