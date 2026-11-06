<?php

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('aggregates filtered admin statistics, monthly trends, resident count, and monthly comparison from database records', function () {
    $this->travelTo('2026-11-06 12:00:00');
    $firstWarga = User::factory()->create();
    $secondWarga = User::factory()->create();
    User::factory()->admin()->create();

    $recentPending = Laporan::factory()->for($firstWarga, 'pelapor')->create([
        'judul' => 'Laporan terbaru pada periode',
        'created_at' => '2026-11-02 09:00:00',
        'updated_at' => '2026-11-02 09:00:00',
    ]);
    $recentApproved = Laporan::factory()->for($secondWarga, 'pelapor')->create([
        'status' => 'approved',
        'created_at' => '2026-11-04 10:00:00',
        'updated_at' => '2026-11-04 10:00:00',
    ]);
    $outsidePeriod = Laporan::factory()->for($firstWarga, 'pelapor')->create([
        'status' => 'rejected',
        'created_at' => '2026-10-25 10:00:00',
        'updated_at' => '2026-10-25 10:00:00',
    ]);
    $lastMonthReport = Laporan::factory()->for($firstWarga, 'pelapor')->create([
        'created_at' => '2026-10-03 10:00:00',
        'updated_at' => '2026-10-03 10:00:00',
    ]);
    $lastMonthReport->forceFill(['status' => 'approved'])->save();

    $admin = User::factory()->admin()->create();
    $recentPending->statusHistories()->create([
        'previous_status' => 'pending',
        'new_status' => 'approved',
        'admin_id' => $admin->id,
        'created_at' => '2026-11-05 11:00:00',
        'updated_at' => '2026-11-05 11:00:00',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard', ['period' => '7d']))
        ->assertOk()
        ->assertViewHas('statistics', [
            'total' => 2,
            'pending' => 1,
            'approved' => 1,
            'rejected' => 0,
        ])
        ->assertViewHas('residentCount', 2)
        ->assertViewHas('comparison', [
            'current' => 2,
            'previous' => 1,
            'current_label' => 'Bulan ini hingga hari ini',
            'previous_label' => 'Periode setara bulan lalu',
        ])
        ->assertViewHas('monthlyTrend', [
            ['key' => '2026-10', 'label' => 'Okt 26', 'count' => 0],
            ['key' => '2026-11', 'label' => 'Nov 26', 'count' => 2],
        ])
        ->assertViewHas('statusBreakdown', [
            ['key' => 'pending', 'label' => 'Menunggu', 'count' => 1],
            ['key' => 'approved', 'label' => 'Disetujui', 'count' => 1],
            ['key' => 'rejected', 'label' => 'Ditolak', 'count' => 0],
        ])
        ->assertViewHas('reports', fn ($reports) => $reports->pluck('id')->all() === [$recentApproved->id, $recentPending->id])
        ->assertViewHas('activities', fn ($activities) => $activities->pluck('laporan_id')->all() === [$recentPending->id])
        ->assertSee('Laporan terbaru pada periode')
        ->assertDontSee($outsidePeriod->judul)
        ->assertSee('Warga terdaftar');
});

it('applies the all-time period to report statistics and charts but not resident count or month comparison', function () {
    $this->travelTo('2026-11-06 12:00:00');
    $warga = User::factory()->create();
    $admin = User::factory()->admin()->create();

    Laporan::factory()->for($warga, 'pelapor')->create([
        'created_at' => '2025-12-10 10:00:00',
        'updated_at' => '2025-12-10 10:00:00',
    ]);
    $olderReport = Laporan::factory()->for($warga, 'pelapor')->create([
        'created_at' => '2025-05-10 10:00:00',
        'updated_at' => '2025-05-10 10:00:00',
    ]);
    $olderReport->forceFill(['status' => 'rejected'])->save();

    $this->actingAs($admin)
        ->get(route('admin.dashboard', ['period' => 'all']))
        ->assertOk()
        ->assertViewHas('statistics', [
            'total' => 2,
            'pending' => 1,
            'approved' => 0,
            'rejected' => 1,
        ])
        ->assertViewHas('residentCount', 1)
        ->assertViewHas('monthlyTrend', [
            ['key' => '2025-05', 'label' => 'Mei 25', 'count' => 1],
            ['key' => '2025-06', 'label' => 'Jun 25', 'count' => 0],
            ['key' => '2025-07', 'label' => 'Jul 25', 'count' => 0],
            ['key' => '2025-08', 'label' => 'Agt 25', 'count' => 0],
            ['key' => '2025-09', 'label' => 'Sep 25', 'count' => 0],
            ['key' => '2025-10', 'label' => 'Okt 25', 'count' => 0],
            ['key' => '2025-11', 'label' => 'Nov 25', 'count' => 0],
            ['key' => '2025-12', 'label' => 'Des 25', 'count' => 1],
            ['key' => '2026-01', 'label' => 'Jan 26', 'count' => 0],
            ['key' => '2026-02', 'label' => 'Feb 26', 'count' => 0],
            ['key' => '2026-03', 'label' => 'Mar 26', 'count' => 0],
            ['key' => '2026-04', 'label' => 'Apr 26', 'count' => 0],
            ['key' => '2026-05', 'label' => 'Mei 26', 'count' => 0],
            ['key' => '2026-06', 'label' => 'Jun 26', 'count' => 0],
            ['key' => '2026-07', 'label' => 'Jul 26', 'count' => 0],
            ['key' => '2026-08', 'label' => 'Agt 26', 'count' => 0],
            ['key' => '2026-09', 'label' => 'Sep 26', 'count' => 0],
            ['key' => '2026-10', 'label' => 'Okt 26', 'count' => 0],
            ['key' => '2026-11', 'label' => 'Nov 26', 'count' => 0],
        ]);
});

it('applies exact rolling date boundaries for each analytics period', function (string $period, int $expectedTotal) {
    $this->travelTo('2026-11-06 12:00:00');
    $warga = User::factory()->create();
    $admin = User::factory()->admin()->create();

    foreach ([
        '2025-11-30 12:00:00',
        '2025-12-01 12:00:00',
        '2026-08-08 12:00:00',
        '2026-08-09 12:00:00',
        '2026-10-07 12:00:00',
        '2026-10-08 12:00:00',
        '2026-10-30 12:00:00',
        '2026-10-31 12:00:00',
        '2026-11-05 12:00:00',
    ] as $createdAt) {
        Laporan::factory()->for($warga, 'pelapor')->create([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.dashboard', ['period' => $period]))
        ->assertOk()
        ->assertViewHas('statistics', [
            'total' => $expectedTotal,
            'pending' => $expectedTotal,
            'approved' => 0,
            'rejected' => 0,
        ]);
})->with([
    '7 day period' => ['7d', 2],
    '30 day period' => ['30d', 4],
    '90 day period' => ['90d', 6],
    '12 month period' => ['12m', 8],
    'all time period' => ['all', 9],
]);

it('returns empty analytics states when there are no reports', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard', ['period' => '30d']))
        ->assertOk()
        ->assertViewHas('statistics', [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
        ])
        ->assertViewHas('monthlyTrend', fn (array $trend) => $trend === [])
        ->assertViewHas('reports', fn ($reports) => $reports->isEmpty())
        ->assertViewHas('activities', fn ($activities) => $activities->isEmpty())
        ->assertSee('Belum ada data tren')
        ->assertSee('Belum ada laporan')
        ->assertSee('Belum ada perubahan status');
});

it('rejects invalid analytics periods with a validation message', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from(route('admin.dashboard'))
        ->get(route('admin.dashboard', ['period' => 'unlimited']))
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHasErrors([
            'period' => 'Periode analitik yang dipilih tidak valid.',
        ]);
});

it('shows only a warga own report statistics latest reports and status activity', function () {
    $this->travelTo('2026-11-06 12:00:00');
    $warga = User::factory()->create();
    $otherWarga = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $ownReport = Laporan::factory()->for($warga, 'pelapor')->create([
        'judul' => 'Laporan warga sendiri',
    ]);
    $approvedReport = Laporan::factory()->for($warga, 'pelapor')->create([
        'judul' => 'Laporan disetujui sendiri',
    ]);
    $approvedReport->forceFill(['status' => 'approved'])->save();

    $otherReport = Laporan::factory()->for($otherWarga, 'pelapor')->create([
        'judul' => 'Laporan privat warga lain',
    ]);
    $otherReport->forceFill(['status' => 'rejected'])->save();

    $ownReport->statusHistories()->create([
        'previous_status' => 'pending',
        'new_status' => 'approved',
        'admin_id' => $admin->id,
    ]);
    $otherReport->statusHistories()->create([
        'previous_status' => 'pending',
        'new_status' => 'rejected',
        'admin_id' => $admin->id,
    ]);

    $this->actingAs($warga)
        ->get(route('warga.dashboard'))
        ->assertOk()
        ->assertViewHas('statistics', [
            'total' => 2,
            'pending' => 1,
            'approved' => 1,
            'rejected' => 0,
        ])
        ->assertViewHas('reports', fn ($reports) => $reports->pluck('id')->all() === [$approvedReport->id, $ownReport->id])
        ->assertViewHas('activities', fn ($activities) => $activities->pluck('laporan_id')->all() === [$ownReport->id])
        ->assertSee('Laporan warga sendiri')
        ->assertSee('Laporan disetujui sendiri')
        ->assertDontSee('Laporan privat warga lain')
        ->assertSee(route('warga.laporans.create'))
        ->assertSee('Perkembangan laporan');
});

it('shows warga an empty state and quick action without exposing another warga data', function () {
    $warga = User::factory()->create();
    $otherWarga = User::factory()->create();
    Laporan::factory()->for($otherWarga, 'pelapor')->create([
        'judul' => 'Laporan bukan milik saya',
    ]);

    $this->actingAs($warga)
        ->get(route('warga.dashboard'))
        ->assertOk()
        ->assertViewHas('statistics', [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
        ])
        ->assertSee('Belum ada laporan')
        ->assertSee('Buat laporan pertama')
        ->assertSee(route('warga.laporans.create'))
        ->assertDontSee('Laporan bukan milik saya');
});

it('protects analytics dashboards by role and requires authentication', function () {
    $warga = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

    $this->actingAs($warga)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($admin)->get(route('warga.dashboard'))->assertForbidden();
});
