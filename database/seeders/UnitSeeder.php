<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            // Prodi
            ['name' => 'Teknik Mesin', 'type' => 'Prodi', 'category' => 'Akademik'],
            ['name' => 'Teknik Elektro', 'type' => 'Prodi', 'category' => 'Akademik'],
            ['name' => 'Teknik Informatika', 'type' => 'Prodi', 'category' => 'Akademik'],

            // Laboratorium
            ['name' => 'Laboratorium Komputer', 'type' => 'Lab', 'category' => 'Laboratorium'],
            ['name' => 'Laboratorium Elektro', 'type' => 'Lab', 'category' => 'Laboratorium'],
            ['name' => 'Laboratorium Mesin', 'type' => 'Lab', 'category' => 'Laboratorium'],

            // Unit Umum/Pusat
            ['name' => 'Umum', 'type' => 'Umum', 'category' => 'Administrasi'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['name' => $unit['name']],
                ['type' => $unit['type'], 'category' => $unit['category']]
            );
        }
    }
}
