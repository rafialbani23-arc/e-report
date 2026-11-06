<?php

namespace App\Policies;

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LaporanPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'warga'], true);
    }

    public function view(User $user, Laporan $laporan): bool|Response
    {
        return $user->role === 'admin' || $laporan->pelapor_id === $user->id
            ? true
            : Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return $user->role === 'warga';
    }

    public function update(User $user, Laporan $laporan): bool|Response
    {
        return $user->role === 'warga'
            && $laporan->pelapor_id === $user->id
            && $laporan->status === 'pending'
            ? true
            : Response::denyAsNotFound();
    }

    public function delete(User $user, Laporan $laporan): bool|Response
    {
        return $user->role === 'warga'
            && $laporan->pelapor_id === $user->id
            && $laporan->status === 'pending'
            ? true
            : Response::denyAsNotFound();
    }

    public function updateStatus(User $user, Laporan $laporan): bool
    {
        return $user->role === 'admin';
    }
}
