<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Teknik Mesin', 'type' => 'Prodi'],
            ['name' => 'Teknik Elektro', 'type' => 'Prodi'],
            ['name' => 'Teknik Informatika', 'type' => 'Prodi'],
            ['name' => 'Laboratorium Komputer', 'type' => 'Lab'],
            ['name' => 'Laboratorium Elektro', 'type' => 'Lab'],
            ['name' => 'Laboratorium Mesin', 'type' => 'Lab'],
            ['name' => 'Umum', 'type' => 'Umum'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}