<?php

use App\Models\Laporan;
use App\Models\LaporanStatusHistory;
use App\Models\User;
use App\Notifications\LaporanUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists residents with their report totals and supports name search and pagination', function () {
    $admin = User::factory()->admin()->create();
    $match = User::factory()->create(['name' => 'Warga Khusus', 'email' => 'khusus@example.test']);
    $other = User::factory()->create(['name' => 'Warga Lain']);
    Laporan::factory()->for($match, 'pelapor')->create();
    Laporan::factory()->for($match, 'pelapor')->create();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['search' => 'khusus']))
        ->assertOk()
        ->assertSee('Warga Khusus')
        ->assertSee('khusus@example.test')
        ->assertSee('2')
        ->assertDontSee('Warga Lain');

    User::factory()->count(15)->create();

    $this->get(route('admin.users.index', ['page' => 2]))
        ->assertOk()
        ->assertViewHas('residents', fn ($residents) => $residents->currentPage() === 2);

    expect($other->role)->toBe('warga');
});

it('updates a resident profile without changing their role', function () {
    $admin = User::factory()->admin()->create();
    $resident = User::factory()->create();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $resident), [
            'name' => 'Nama Diperbarui',
            'email' => 'updated@example.test',
            'role' => 'admin',
        ])
        ->assertRedirect(route('admin.users.show', $resident))
        ->assertSessionHas('success', 'Profil warga berhasil diperbarui.');

    $this->assertDatabaseHas('users', [
        'id' => $resident->id,
        'name' => 'Nama Diperbarui',
        'email' => 'updated@example.test',
        'role' => 'warga',
    ]);
});

it('rejects a resident email already used by another account', function () {
    $admin = User::factory()->admin()->create();
    $resident = User::factory()->create();
    $otherResident = User::factory()->create();

    $this->actingAs($admin)
        ->from(route('admin.users.show', $resident))
        ->put(route('admin.users.update', $resident), [
            'name' => 'Nama Valid',
            'email' => $otherResident->email,
        ])
        ->assertRedirect(route('admin.users.show', $resident))
        ->assertSessionHasErrors(['email' => 'Alamat email sudah digunakan akun lain.']);

    $this->assertDatabaseHas('users', [
        'id' => $resident->id,
        'email' => $resident->email,
    ]);
});

it('activates and deactivates a resident account', function () {
    $admin = User::factory()->admin()->create();
    $resident = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.status', $resident), ['is_active' => '0'])
        ->assertRedirect(route('admin.users.show', $resident))
        ->assertSessionHas('success', 'Akun warga berhasil dinonaktifkan.');

    $this->assertDatabaseHas('users', ['id' => $resident->id, 'is_active' => false]);

    $this->patch(route('admin.users.status', $resident), ['is_active' => '1'])
        ->assertRedirect(route('admin.users.show', $resident))
        ->assertSessionHas('success', 'Akun warga berhasil diaktifkan.');

    $this->assertDatabaseHas('users', ['id' => $resident->id, 'is_active' => true]);
});

it('filters residents by account status', function () {
    $admin = User::factory()->admin()->create();
    $activeResident = User::factory()->create(['name' => 'Warga Aktif', 'is_active' => true]);
    $inactiveResident = User::factory()->create(['name' => 'Warga Nonaktif', 'is_active' => false]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['status' => 'inactive']))
        ->assertOk()
        ->assertSee('Warga Nonaktif')
        ->assertDontSee('Warga Aktif');

    expect($inactiveResident->is_active)->toBeFalse();
});

it('rejects login for a deactivated account', function () {
    $resident = User::factory()->create(['is_active' => false, 'password' => 'password123']);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $resident->email,
            'password' => 'password123',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Email atau kata sandi tidak sesuai.']);

    $this->assertGuest();
});

it('ends an authenticated resident session on the next web request after deactivation', function () {
    $admin = User::factory()->admin()->create();
    $resident = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.status', $resident), ['is_active' => '0'])
        ->assertRedirect(route('admin.users.show', $resident));

    $this->actingAs($resident)
        ->get(route('warga.dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator untuk bantuan.']);

    $this->assertGuest();
});

it('forbids residents from accessing user management', function () {
    $resident = User::factory()->create();

    $this->actingAs($resident)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

it('prevents an administrator from deactivating their own account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.status', $admin), ['is_active' => '0'])
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin', 'is_active' => true]);
});

it('preserves reports notifications and status history when a resident is deactivated', function () {
    $admin = User::factory()->admin()->create();
    $resident = User::factory()->create();
    $report = Laporan::factory()->for($resident, 'pelapor')->create();
    $history = $report->statusHistories()->create([
        'previous_status' => 'pending',
        'new_status' => 'approved',
        'admin_id' => $admin->id,
    ]);
    $resident->notify(new LaporanUpdatedNotification(
        laporanId: $report->id,
        laporanTitle: $report->judul,
        events: ['status'],
        status: 'approved',
    ));

    $this->actingAs($admin)
        ->patch(route('admin.users.status', $resident), ['is_active' => '0'])
        ->assertRedirect(route('admin.users.show', $resident));

    $this->assertModelExists($resident);
    $this->assertModelExists($report);
    $this->assertModelExists($history);
    expect($resident->notifications()->count())->toBe(1);
    expect($resident->laporans()->sole()->is($report))->toBeTrue();
    expect(LaporanStatusHistory::query()->sole()->is($history))->toBeTrue();
});
