<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

#[Fillable(['name', 'email', 'password', 'alamat', 'no_whatsapp', 'foto'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $attributes = ['role' => 'warga'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function laporans(): HasMany
    {
        return $this->hasMany(Laporan::class, 'pelapor_id');
    }

    public function laporanStatusHistories(): HasMany
    {
        return $this->hasMany(LaporanStatusHistory::class, 'admin_id');
    }

    public function hasAccountStatus(): bool
    {
        return Schema::hasColumn($this->getTable(), 'is_active');
    }

    public function isAccountActive(): bool
    {
        return ! $this->hasAccountStatus() || (bool) $this->is_active;
    }

    public function dashboardRoute(): string
    {
        return $this->role === 'admin' ? route('admin.dashboard') : route('warga.dashboard');
    }
}
