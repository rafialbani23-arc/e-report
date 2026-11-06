<?php

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('registers a warga account without granting the submitted role', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Rani Pratama',
        'email' => 'rani@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('users', [
        'name' => 'Rani Pratama',
        'email' => 'rani@example.test',
        'role' => 'warga',
    ]);
    expect(Hash::check('password123', User::query()->where('email', 'rani@example.test')->value('password')))->toBeTrue();
    $this->assertAuthenticated();
});

it('shows registration validation errors for an unconfirmed password', function () {
    $response = $this->followingRedirects()->from(route('register'))->post(route('register.store'), [
        'name' => 'Rani Pratama',
        'email' => 'rani@example.test',
        'password' => 'password123',
        'password_confirmation' => 'different123',
    ]);

    $response->assertOk()
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('aria-describedby="password-error"', false)
        ->assertSee('id="password-error"', false);
});

it('rate limits repeated registration submissions', function () {
    for ($attempt = 1; $attempt <= 6; $attempt++) {
        $this->from(route('register'))->post(route('register.store'), [])->assertRedirect(route('register'));
    }

    $this->from(route('register'))->post(route('register.store'), [])->assertTooManyRequests();

    $this->assertDatabaseCount('users', 0);
});

it('authenticates a user with valid credentials and rejects an invalid password', function () {
    $user = User::factory()->create([
        'email' => 'rani@example.test',
        'password' => 'password123',
    ]);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'email' => 'Email atau kata sandi tidak sesuai.',
        ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('invalidates the authenticated session on logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('shows system-wide database statistics to an admin', function () {
    $firstWarga = User::factory()->create();
    $secondWarga = User::factory()->create();

    Laporan::factory()->for($firstWarga, 'pelapor')->create([
        'judul' => 'Lampu jalan belum menyala',
    ]);
    $approvedReport = Laporan::factory()->for($firstWarga, 'pelapor')->create([
        'judul' => 'Perbaikan saluran',
    ]);
    $approvedReport->forceFill(['status' => 'approved'])->save();

    Laporan::factory()->for($secondWarga, 'pelapor')->create([
        'judul' => 'Permintaan fasilitas',
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('statistics', [
            'total' => 3,
            'pending' => 2,
            'approved' => 1,
            'rejected' => 0,
        ])
        ->assertSee('Lampu jalan belum menyala')
        ->assertSee($firstWarga->name)
        ->assertSee('Menunggu');
});

it('limits warga dashboard statistics and recent reports to their own reports', function () {
    $warga = User::factory()->create();
    $otherWarga = User::factory()->create();

    Laporan::factory()->for($warga, 'pelapor')->create([
        'judul' => 'Laporan milik saya',
    ]);
    $otherReport = Laporan::factory()->for($otherWarga, 'pelapor')->create([
        'judul' => 'Laporan milik warga lain',
    ]);
    $otherReport->forceFill(['status' => 'approved'])->save();

    $this->actingAs($warga)
        ->get(route('warga.dashboard'))
        ->assertOk()
        ->assertViewHas('statistics', [
            'total' => 1,
            'pending' => 1,
            'approved' => 0,
            'rejected' => 0,
        ])
        ->assertSee('Laporan milik saya')
        ->assertDontSee('Laporan milik warga lain');
});

it('forbids users from opening a dashboard for another role', function () {
    $warga = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($warga)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($admin)->get(route('warga.dashboard'))->assertForbidden();
});

it('redirects guests to login before showing a dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('warga.dashboard'))->assertRedirect(route('login'));
});

it('renders accessible responsive navigation with the active section identified', function () {
    $resident = User::factory()->create();

    $this->actingAs($resident)
        ->get(route('warga.dashboard'))
        ->assertOk()
        ->assertSee('aria-controls="dashboard-sidebar"', false)
        ->assertSee('data-sidebar-backdrop', false)
        ->assertSee('aria-current="page"', false)
        ->assertDontSee('Manajemen pengguna');

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Manajemen pengguna')
        ->assertSee('table-fixed')
        ->assertSee('hidden px-4 py-3.5 sm:table-cell')
        ->assertSee('aria-current="page"', false);
});
