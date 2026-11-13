<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Asset;

class AssetPolicy
{
    // SEMUA user yang login boleh melihat daftar aset (index)
    public function viewAny(User $user): bool
    {
        return in_array($user->level, [
            'Sarpras',
            'Admin',
            'Kalab',
            'Kaprodi',
            'Aslab',
            'Keuangan',
            'Karyawan',
            'Mahasiswa',
            'Rektor',
            'PJ Pengadaan',
            'Tim Pemeliharaan',
            'Administrasi',
            'Karyawan', 
            'Dosen',
            'Mahasiswa',
        ]);
    }

    // SEMUA user boleh melihat detail aset SIAPA PUN (read-only lintas unit)
    public function view(User $user, Asset $asset): bool
    {
        return true; // Semua yang bisa login boleh lihat detail aset
    }

    // CREATE: hanya role tertentu
    public function create(User $user): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin', 'Kalab', 'Kaprodi', 'Aslab']);
    }

    // UPDATE: hanya pemilik unit (atau Sarpras/Admin)
    public function update(User $user, Asset $asset): bool
    {
        if (in_array($user->level, ['Sarpras', 'Admin'])) {
            return true; // Sarpras/Admin bisa edit semua
        }

        if (in_array($user->level, ['Kalab', 'Kaprodi', 'Aslab'])) {
            return $asset->unit_id === $user->unit_id; // Hanya unit sendiri
        }

        return false;
    }

    // DELETE: hanya Sarpras/Admin
    public function delete(User $user, Asset $asset): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin']);
    }
}