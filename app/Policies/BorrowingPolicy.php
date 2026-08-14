<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Borrowing;

class BorrowingPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // semua peran boleh akses menu Peminjaman, discoping di query
    }

    public function view(User $user, Borrowing $borrowing): bool
    {
        if (in_array($user->level, ['Sarpras', 'Admin']))
            return true;
        return $borrowing->borrower_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true; // semua peran boleh ajukan peminjaman
    }

    public function update(User $user, Borrowing $borrowing): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin']); // approve/return cuma Sarpras
    }

    public function delete(User $user, Borrowing $borrowing): bool
    {
        return in_array($user->level, ['Sarpras', 'Admin']);
    }

    public function approveCrossUnit(User $user, Borrowing $borrowing): bool
    {
        return $user->level === 'Kalab'
            && $borrowing->asset->unit_id === $user->unit_id
            && $borrowing->status === 'Menunggu Persetujuan Kalab';
    }

    public function rejectCrossUnit(User $user, Borrowing $borrowing): bool
    {
        return $this->approveCrossUnit($user, $borrowing); // syarat sama persis
    }
}