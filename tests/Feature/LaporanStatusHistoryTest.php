<?php

use App\Models\Laporan;
use App\Models\LaporanStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records pending to final status transitions with an admin response and actor', function (
    string $status,
    string $label,
) {
    $this->travelTo('2026-11-06 09:00:00');
    $warga = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();

    $this->actingAs($admin)
        ->patch(route('admin.laporans.status', $report), [
            'status' => $status,
            'tanggapan_admin' => "Tindak lanjut untuk status {$label}.",
        ])
        ->assertRedirect(route('admin.laporans.show', $report));

    $this->assertDatabaseHas('laporans', [
        'id' => $report->id,
        'status' => $status,
        'tanggapan_admin' => "Tindak lanjut untuk status {$label}.",
    ]);
    $this->assertDatabaseHas('laporan_status_histories', [
        'laporan_id' => $report->id,
        'previous_status' => 'pending',
        'new_status' => $status,
        'admin_id' => $admin->id,
        'created_at' => '2026-11-06 09:00:00',
    ]);

    $history = LaporanStatusHistory::query()->sole();
    expect($history->laporan->is($report))->toBeTrue();
    expect($history->admin->is($admin))->toBeTrue();

    $this->actingAs($warga)
        ->get(route('warga.laporans.show', $report))
        ->assertOk()
        ->assertSee('Tanggapan admin')
        ->assertSee("Tindak lanjut untuk status {$label}.")
        ->assertSee('Menunggu')
        ->assertSee($label)
        ->assertSee($admin->name);

    $this->actingAs($admin)
        ->get(route('admin.laporans.show', $report))
        ->assertOk()
        ->assertSee("Tindak lanjut untuk status {$label}.")
        ->assertSee($admin->name);
})->with([
    'approved decision' => ['approved', 'Disetujui'],
    'rejected decision' => ['rejected', 'Ditolak'],
]);

it('does not add status history when an admin only updates the response', function () {
    $warga = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();

    $this->actingAs($admin)
        ->patch(route('admin.laporans.status', $report), [
            'status' => 'pending',
            'tanggapan_admin' => 'Kami sedang memeriksa informasi tambahan.',
        ])
        ->assertRedirect(route('admin.laporans.show', $report));

    $this->assertDatabaseHas('laporans', [
        'id' => $report->id,
        'status' => 'pending',
        'tanggapan_admin' => 'Kami sedang memeriksa informasi tambahan.',
    ]);
    $this->assertDatabaseCount('laporan_status_histories', 0);
});

it('does not let a warga submit an admin response or change status', function () {
    $warga = User::factory()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();

    $this->actingAs($warga)
        ->patch(route('admin.laporans.status', $report), [
            'status' => 'approved',
            'tanggapan_admin' => 'Perubahan tidak sah.',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('laporans', [
        'id' => $report->id,
        'status' => 'pending',
        'tanggapan_admin' => null,
    ]);
    $this->assertDatabaseCount('laporan_status_histories', 0);
});

it('rejects an oversized admin response without changing report status or history', function () {
    $warga = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();

    $this->actingAs($admin)
        ->from(route('admin.laporans.show', $report))
        ->patch(route('admin.laporans.status', $report), [
            'status' => 'approved',
            'tanggapan_admin' => str_repeat('a', 10001),
        ])
        ->assertRedirect(route('admin.laporans.show', $report))
        ->assertSessionHasErrors(['tanggapan_admin']);

    $this->assertDatabaseHas('laporans', [
        'id' => $report->id,
        'status' => 'pending',
        'tanggapan_admin' => null,
    ]);
    $this->assertDatabaseCount('laporan_status_histories', 0);
});

it('keeps report and admin relations consistent for each status history entry', function () {
    $warga = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();

    $history = $report->statusHistories()->create([
        'previous_status' => 'pending',
        'new_status' => 'approved',
        'admin_id' => $admin->id,
    ]);

    expect($report->statusHistories()->count())->toBe(1);
    expect($admin->laporanStatusHistories()->sole()->is($history))->toBeTrue();
    expect($history->laporan->pelapor->is($warga))->toBeTrue();
    expect($history->admin->role)->toBe('admin');

    $this->assertDatabaseHas('laporan_status_histories', [
        'id' => $history->id,
        'laporan_id' => $report->id,
        'admin_id' => $admin->id,
    ]);
});
