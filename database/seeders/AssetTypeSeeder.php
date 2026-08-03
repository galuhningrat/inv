<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetType;

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
            AssetType::create($type);
        }
    }
}