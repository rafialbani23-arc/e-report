<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laporan extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending' => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];

    protected $fillable = [
        'judul',
        'deskripsi',
        'tanggal',
        'foto',
        'tanggapan_admin',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LaporanStatusHistory::class)->latest('created_at')->latest('id');
    }

    public function statusDescription(): string
    {
        return match ($this->status) {
            'approved' => 'Pengaduan telah disetujui admin',
            'rejected' => 'Pengaduan ditolak oleh admin',
            default => 'Pengaduan menunggu peninjauan admin',
        };
    }

    /**
     * @param  array{q?: string, status?: string, date_from?: string, date_to?: string, sort?: string}  $filters
     */
    #[Scope]
    protected function matchingFilters(Builder $query, array $filters): void
    {
        if (filled($filters['q'] ?? null)) {
            $search = $filters['q'];

            $query->where(function (Builder $query) use ($search): void {
                $query->where('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $query
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('tanggal', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('tanggal', '<=', $date));

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->oldest('created_at'),
            'title' => $query->orderBy('judul'),
            'date' => $query->orderByDesc('tanggal'),
            default => $query->latest('created_at'),
        };

        $query->orderByDesc('id');
    }
}
