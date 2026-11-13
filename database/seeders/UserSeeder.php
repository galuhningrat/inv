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
            [
                'name' => 'Staff Sarpras',
                'username' => 'sarpras',
                'email' => 'sarpras@stti.ac.id',
                'password' => Hash::make('sarpras123'),
                'level' => 'Sarpras',
                'status' => 'Aktif',
                'avatar' => 'assets/sarpras.png'
            ],
            [
                'name' => 'Muhammad Sugiarto, S.E., M.M.',
                'username' => 'rektor',
                'email' => 'rektor@stti.ac.id',
                'password' => Hash::make('rektor123'),
                'level' => 'Rektor',
                'status' => 'Aktif',
                'avatar' => 'assets/rektor.png'
            ],
            [
                'name' => 'Bima Azis Kusuma, S.T., M.T.',
                'username' => 'kaprodi',
                'email' => 'kaprodi@stti.ac.id',
                'password' => Hash::make('kaprodi123'),
                'level' => 'Kaprodi',
                'status' => 'Aktif',
                'avatar' => 'assets/kaprodi.png'
            ],
            [
                'name' => 'Mrs. Nazilah',
                'username' => 'keuangan',
                'email' => 'mrsnazilah@stti.ac.id',
                'password' => Hash::make('keuangan123'),
                'level' => 'Keuangan',
                'status' => 'Aktif',
                'avatar' => 'assets/keuangan.png'
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