<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@stti.ac.id',
                'password' => Hash::make('admin123'),
                'level' => 'Admin',
                'status' => 'Aktif',
                'avatar' => 'assets/admin.png'
            ],
        ];

        foreach ($users as $user) {
            // Cek apakah username sudah ada sebelum membuat
            User::firstOrCreate(
                ['username' => $user['username']], // Kriteria pencarian
                $user                             // Data untuk dibuat jika tidak ditemukan
            );
        }
    }
}
