<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->role === 'admin';
    }

    public function view(User $actor, User $resident): bool
    {
        return $actor->role === 'admin' && $resident->role === 'warga';
    }

    public function update(User $actor, User $resident): bool
    {
        return $actor->role === 'admin' && $resident->role === 'warga';
    }

    public function updateStatus(User $actor, User $resident): bool
    {
        return $actor->role === 'admin'
            && $resident->role === 'warga'
            && $actor->isNot($resident);
    }
}
