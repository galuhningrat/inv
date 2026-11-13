<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
<<<<<<< HEAD
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
=======
            AssetTypeSeeder::class, // Harus pertama
            UserSeeder::class,      // Harus kedua (untuk foreign key peminjaman/request)
            AssetSeeder::class,     // Terakhir, karena butuh AssetType
        ]);
    }
}
>>>>>>> 39cb6caecfad6984e017629417d9109f6fb3cc7e
