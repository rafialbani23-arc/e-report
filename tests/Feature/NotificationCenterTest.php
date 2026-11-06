<?php

use App\Models\Laporan;
use App\Models\User;
use App\Notifications\LaporanUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('notifies only the report owner when status and response change and avoids repeated notifications', function (
    string $status,
    string $statusLabel,
) {
    $warga = User::factory()->create();
    $otherWarga = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();
    $otherReport = Laporan::factory()->for($otherWarga, 'pelapor')->create();

    $payload = [
        'status' => $status,
        'tanggapan_admin' => 'Laporan telah diverifikasi dan akan ditindaklanjuti.',
    ];

    $this->actingAs($admin)
        ->patch(route('admin.laporans.status', $report), $payload)
        ->assertRedirect(route('admin.laporans.show', $report));

    $notification = $warga->notifications()->sole();

    expect($notification->data)->toMatchArray([
        'laporan_id' => $report->id,
        'laporan_judul' => $report->judul,
        'events' => ['status', 'response'],
        'status' => $status,
    ]);
    expect($notification->data['message'])->toContain($statusLabel)
        ->toContain('tanggapan admin diperbarui');
    expect($otherWarga->notifications()->count())->toBe(0);
    expect($report->statusHistories()->count())->toBe(1);

    $this->patch(route('admin.laporans.status', $report), $payload)
        ->assertRedirect(route('admin.laporans.show', $report));

    expect($warga->notifications()->count())->toBe(1);
    expect($report->statusHistories()->count())->toBe(1);
    expect($otherReport->status)->toBe('pending');
})->with([
    'approved status' => ['approved', 'Disetujui'],
    'rejected status' => ['rejected', 'Ditolak'],
]);

it('notifies the warga when an admin adds or updates a response without a status change', function () {
    $warga = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create([
        'tanggapan_admin' => 'Kami sedang memeriksa laporan.',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.laporans.status', $report), [
            'status' => 'pending',
            'tanggapan_admin' => 'Pemeriksaan selesai dan laporan akan segera ditindaklanjuti.',
        ])
        ->assertRedirect(route('admin.laporans.show', $report));

    $notification = $warga->notifications()->sole();

    expect($notification->data['events'])->toBe(['response']);
    expect($notification->data['status'])->toBe('pending');
    expect($report->statusHistories()->count())->toBe(0);
});

it('stores notification changes in the same transaction as status and history updates', function () {
    $warga = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();

    DB::unprepared(<<<'SQL'
        CREATE TRIGGER fail_notification_insert
        BEFORE INSERT ON notifications
        BEGIN
            SELECT RAISE(ABORT, 'notification insert failed');
        END;
    SQL);

    $this->actingAs($admin)
        ->patch(route('admin.laporans.status', $report), [
            'status' => 'rejected',
            'tanggapan_admin' => 'Informasi pada laporan belum dapat diverifikasi.',
        ])
        ->assertStatus(500);

    $this->assertDatabaseHas('laporans', [
        'id' => $report->id,
        'status' => 'pending',
        'tanggapan_admin' => null,
    ]);
    $this->assertDatabaseCount('laporan_status_histories', 0);
    $this->assertDatabaseCount('notifications', 0);
});

it('keeps existing pages available and blocks notification writes while its migration is pending', function () {
    $warga = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();

    Schema::drop('notifications');

    $this->actingAs($warga)
        ->get(route('warga.dashboard'))
        ->assertOk()
        ->assertSee('Pusat notifikasi akan tersedia setelah pembaruan database diterapkan.');

    $this->get(route('notifications.index'))
        ->assertStatus(503);

    $this->actingAs($admin)
        ->patch(route('admin.laporans.status', $report), [
            'status' => 'approved',
            'tanggapan_admin' => 'Laporan telah diverifikasi.',
        ])
        ->assertStatus(503);

    $this->assertDatabaseHas('laporans', [
        'id' => $report->id,
        'status' => 'pending',
        'tanggapan_admin' => null,
    ]);
    $this->assertDatabaseCount('laporan_status_histories', 0);
});

it('requires authentication to view the notification center', function () {
    $this->get(route('notifications.index'))
        ->assertRedirect(route('login'));
});

it('shows an accessible empty state when the user has no notifications', function () {
    $warga = User::factory()->create();

    $this->actingAs($warga)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Belum ada notifikasi')
        ->assertSee('Notifikasi, 0 belum dibaca')
        ->assertDontSee('Tandai semua dibaca');
});

it('shows only the signed in users notifications and permits marking one as read', function () {
    $warga = User::factory()->create();
    $otherWarga = User::factory()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();
    $otherReport = Laporan::factory()->for($otherWarga, 'pelapor')->create();
    $warga->notify(new LaporanUpdatedNotification(
        laporanId: $report->id,
        laporanTitle: $report->judul,
        events: ['status'],
        status: 'approved',
    ));
    $notification = $warga->notifications()->sole();

    $otherWarga->notify(new LaporanUpdatedNotification(
        laporanId: $otherReport->id,
        laporanTitle: $otherReport->judul,
        events: ['status'],
        status: 'rejected',
    ));
    $otherNotification = $otherWarga->notifications()->sole();

    $this->actingAs($warga)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee($report->judul)
        ->assertDontSee($otherReport->judul)
        ->assertSee('Tandai semua dibaca')
        ->assertSee(route('warga.laporans.show', $report));

    $this->patch(route('notifications.read', $otherNotification->id))
        ->assertNotFound();

    $this->patch(route('notifications.read', $notification->id))
        ->assertRedirect(route('notifications.index'));

    expect($warga->notifications()->findOrFail($notification->id)->read_at)->not->toBeNull();
    expect($otherWarga->notifications()->findOrFail($otherNotification->id)->read_at)->toBeNull();
});

it('marks all and only the signed in users unread notifications as read', function () {
    $warga = User::factory()->create();
    $otherWarga = User::factory()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();
    $otherReport = Laporan::factory()->for($otherWarga, 'pelapor')->create();

    $warga->notify(new LaporanUpdatedNotification(
        laporanId: $report->id,
        laporanTitle: $report->judul,
        events: ['status'],
        status: 'approved',
    ));
    $firstNotification = $warga->notifications()->sole();

    $warga->notify(new LaporanUpdatedNotification(
        laporanId: $report->id,
        laporanTitle: $report->judul,
        events: ['response'],
        status: 'approved',
    ));
    $secondNotification = $warga->notifications()
        ->where('id', '!=', $firstNotification->id)
        ->sole();

    $otherWarga->notify(new LaporanUpdatedNotification(
        laporanId: $otherReport->id,
        laporanTitle: $otherReport->judul,
        events: ['status'],
        status: 'rejected',
    ));
    $otherNotification = $otherWarga->notifications()->sole();
    $firstNotification->markAsRead();

    $this->actingAs($warga)
        ->patch(route('notifications.read-all'))
        ->assertRedirect(route('notifications.index'));

    expect($warga->unreadNotifications()->count())->toBe(0);
    expect($warga->notifications()->findOrFail($firstNotification->id)->read_at)->not->toBeNull();
    expect($warga->notifications()->findOrFail($secondNotification->id)->read_at)->not->toBeNull();
    expect($otherWarga->notifications()->findOrFail($otherNotification->id)->read_at)->toBeNull();
});
