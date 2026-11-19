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
            return $maintenance->asset->unit_id === $user->unit_id;
        }
        return $maintenance->recorded_by === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin', 'Kalab', 'Aslab', 'Karyawan', 'Mahasiswa', 'Tim Pemeliharaan']);
    }

    /**
     * Hanya Kalab, Sarpras, Admin yang boleh memverifikasi tiket.
     * Hanya tiket berstatus 'Menunggu Verifikasi' yang bisa diverifikasi.
     */
    public function verify(User $user, Maintenance $maintenance): bool
    {
        if ($maintenance->status !== 'Menunggu Verifikasi') {
            return false;
        }

        if (in_array($user->level, ['Sarpras', 'Admin'])) {
            return true;
        }

        if ($user->level === 'Kalab') {
            // Kalab hanya bisa verifikasi aset dalam unit-nya
            return $maintenance->asset->unit_id === $user->unit_id;
        }

        return false;
    }

    public function update(User $user, Maintenance $maintenance): bool
    {
        // Tidak bisa update tiket yang belum diverifikasi
        if ($maintenance->status === 'Menunggu Verifikasi') {
            return false;
        }

        if (in_array($user->level, ['Sarpras', 'Admin', 'Tim Pemeliharaan'])) {
            return true;
        }

        if ($user->level === 'Kalab') {
            return $maintenance->asset->unit_id === $user->unit_id;
        }

        return false;
    }

    public function delete(User $user, Maintenance $maintenance): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin']);
    }
}
