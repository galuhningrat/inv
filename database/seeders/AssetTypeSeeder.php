<?php

namespace Database\Seeders;

use App\Models\AssetType;
use Illuminate\Database\Seeder;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'KOM', 'name' => 'Komputer & Periferal', 'description' => 'Laptop, PC, Monitor, Printer, dll'],
            ['code' => 'ELK', 'name' => 'Elektronik Umum', 'description' => 'Sensor, Modul, Komponen Elektronika'],
            ['code' => 'JAR', 'name' => 'Jaringan', 'description' => 'Router, Switch, Kabel LAN, FO'],
            ['code' => 'FUR', 'name' => 'Furniture', 'description' => 'Meja, Kursi, Lemari, Papan Tulis'],
            ['code' => 'ATK', 'name' => 'Alat & Perkakas', 'description' => 'Obeng, Tang, Crimping Tool'],
            ['code' => 'LAN', 'name' => 'Lainnya', 'description' => 'Barang-barang umum lainnya'],
        ];

        foreach ($types as $type) {
            // 🔥 Ubah menjadi updateOrCreate
            AssetType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
