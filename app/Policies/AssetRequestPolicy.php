<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AssetRequest;

class AssetRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->level, ['Kaprodi', 'Kalab', 'Sarpras', 'Admin', 'PJ Pengadaan', 'Rektor', 'Keuangan']);
        // Keuangan tetap masuk meski tidak eksplisit di daftar menu Anda —
        // dibutuhkan supaya bisa akses tombol "Konfirmasi Dana Cair" yang sudah kita bangun.
    }

    public function view(User $user, AssetRequest $assetRequest): bool
    {
        if (in_array($user->level, ['Sarpras', 'Admin', 'PJ Pengadaan', 'Rektor', 'Keuangan']))
            return true;
        if (in_array($user->level, ['Kaprodi', 'Kalab']))
            return $assetRequest->unit_id === $user->unit_id;
        return $assetRequest->requester_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->level, ['Kaprodi', 'Kalab', 'Sarpras', 'Admin']);
    }

    public function update(User $user, AssetRequest $assetRequest): bool
    {
        if (in_array($user->level, ['Sarpras', 'Admin']))
            return true;
        return in_array($user->level, ['Kaprodi', 'Kalab'])
            && $assetRequest->requester_id === $user->id
            && $assetRequest->status === 'Pending';
    }

    public function delete(User $user, AssetRequest $assetRequest): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin']) && $assetRequest->status === 'Pending';
    }

    public function verify(User $user, AssetRequest $assetRequest): bool
    {
        return in_array($user->level, ['PJ Pengadaan', 'Sarpras', 'Admin']) && $assetRequest->status === 'Pending';
    }

    public function approve(User $user, AssetRequest $assetRequest): bool
    {
        return in_array($user->level, ['Rektor', 'Sarpras', 'Admin']) && $assetRequest->status === 'Diverifikasi';
    }

    public function reject(User $user, AssetRequest $assetRequest): bool
    {
        return $user->level === 'Rektor' && $assetRequest->status === 'Diverifikasi';
    }

    public function disburse(User $user, AssetRequest $assetRequest): bool
    {
        return in_array($user->level, ['Keuangan', 'Sarpras', 'Admin']) && $assetRequest->status === 'Disetujui';
    }

    public function confirmPhysical(User $user, AssetRequest $assetRequest): bool
    {
        return $user->level === 'PJ Pengadaan' && $assetRequest->status === 'Dana Cair';
    }

    public function receive(User $user, AssetRequest $assetRequest): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin']) && $assetRequest->status === 'Dikonfirmasi';
    }
}