<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Maintenance;

class MaintenancePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin', 'Kalab', 'Aslab', 'Tim Pemeliharaan', 'Rektor', 'Karyawan', 'Mahasiswa']);
    }

    public function view(User $user, Maintenance $maintenance): bool
    {
        if (in_array($user->level, ['Sarpras', 'Admin', 'Tim Pemeliharaan', 'Rektor'])) {
            return true;
        }
        if ($user->level === 'Kalab') {
            return $maintenance->asset->unit_id === $user->unit_id; // Kalab lihat tiket berdasarkan unit aset
        }
        return $maintenance->recorded_by === $user->id; // Aslab/Karyawan/Mahasiswa: tiket sendiri
    }
    public function create(User $user): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin', 'Kalab', 'Aslab', 'Karyawan', 'Mahasiswa', 'Tim Pemeliharaan']);
    }

    public function update(User $user, Maintenance $maintenance): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin', 'Tim Pemeliharaan', 'Kalab']);
    }

    public function delete(User $user, Maintenance $maintenance): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin']);
    }
}