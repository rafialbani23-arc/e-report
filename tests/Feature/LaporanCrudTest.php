<?php

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('creates a pending report for the authenticated warga and stores evidence privately', function () {
    Storage::fake('local');
    $warga = User::factory()->create();
    $otherWarga = User::factory()->create();

    $this->actingAs($warga)
        ->post(route('warga.laporans.store'), [
            'judul' => 'Lampu jalan padam',
            'deskripsi' => 'Lampu di Jalan Melati tidak menyala sejak kemarin malam.',
            'tanggal' => '2026-11-04',
            'foto' => UploadedFile::fake()->image('bukti.jpg', 20, 20),
            'pelapor_id' => $otherWarga->id,
            'status' => 'approved',
        ])
        ->assertRedirect();

    $report = Laporan::query()->sole();

    expect($report->pelapor_id)->toBe($warga->id);
    expect($report->status)->toBe('pending');
    expect($report->foto)->toStartWith('laporans/');
    Storage::disk('local')->assertExists($report->foto);

    $this->get(route('laporans.foto', $report))
        ->assertOk()
        ->assertHeader('content-type', 'image/jpeg');

    $this->assertDatabaseHas('laporans', [
        'id' => $report->id,
        'pelapor_id' => $warga->id,
        'status' => 'pending',
        'judul' => 'Lampu jalan padam',
    ]);
});

it('rejects a non-image evidence upload without creating a report', function () {
    Storage::fake('local');
    $warga = User::factory()->create();

    $this->actingAs($warga)
        ->from(route('warga.laporans.create'))
        ->post(route('warga.laporans.store'), [
            'judul' => 'Lampu jalan padam',
            'deskripsi' => 'Lampu di Jalan Melati tidak menyala.',
            'tanggal' => '2026-11-04',
            'foto' => UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf'),
        ])
        ->assertRedirect(route('warga.laporans.create'))
        ->assertSessionHasErrors(['foto']);

    $this->assertDatabaseCount('laporans', 0);
    expect(Storage::disk('local')->allFiles('laporans'))->toBeEmpty();
});

it('rejects evidence images larger than five megabytes', function () {
    Storage::fake('local');
    $warga = User::factory()->create();

    $this->actingAs($warga)
        ->from(route('warga.laporans.create'))
        ->post(route('warga.laporans.store'), [
            'judul' => 'Lampu jalan padam',
            'deskripsi' => 'Lampu di Jalan Melati tidak menyala.',
            'tanggal' => '2026-11-04',
            'foto' => UploadedFile::fake()->image('bukti.jpg', 20, 20)->size(5121),
        ])
        ->assertRedirect(route('warga.laporans.create'))
        ->assertSessionHasErrors(['foto']);

    $this->assertDatabaseCount('laporans', 0);
    expect(Storage::disk('local')->allFiles('laporans'))->toBeEmpty();
});

it('escapes report descriptions and admin responses when rendered', function () {
    $warga = User::factory()->create();
    $unsafeContent = '<script>alert(1)</script>';
    $report = Laporan::factory()->for($warga, 'pelapor')->create([
        'judul' => $unsafeContent,
        'deskripsi' => $unsafeContent,
        'tanggapan_admin' => $unsafeContent,
    ]);

    $this->actingAs($warga)
        ->get(route('warga.laporans.show', $report))
        ->assertOk()
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee($unsafeContent, false);
});

it('allows a warga to list and search only their own reports with filters and pagination', function () {
    $warga = User::factory()->create();
    $otherWarga = User::factory()->create();

    Laporan::factory()->for($warga, 'pelapor')->create([
        'judul' => 'Saluran tersumbat',
        'tanggal' => '2026-10-25',
    ]);
    Laporan::factory()->for($warga, 'pelapor')->create([
        'judul' => 'Lampu lingkungan padam',
        'tanggal' => '2026-11-01',
    ]);
    Laporan::factory()->for($otherWarga, 'pelapor')->create([
        'judul' => 'Lampu jalan warga lain',
    ]);

    $this->actingAs($warga)
        ->get(route('warga.laporans.index', [
            'q' => 'lampu',
            'status' => 'pending',
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-02',
            'sort' => 'oldest',
        ]))
        ->assertOk()
        ->assertSee('Lampu lingkungan padam')
        ->assertDontSee('Lampu jalan warga lain')
        ->assertViewHas('reports', fn ($reports) => $reports->total() === 1);
});

it('paginates warga reports while retaining search filters', function () {
    $warga = User::factory()->create();
    Laporan::factory()->count(11)->for($warga, 'pelapor')->create([
        'judul' => 'Lampu lingkungan',
    ]);

    $this->actingAs($warga)
        ->get(route('warga.laporans.index', ['q' => 'Lampu']))
        ->assertOk()
        ->assertViewHas('reports', fn ($reports) => $reports->total() === 11
            && $reports->perPage() === 10
            && $reports->currentPage() === 1);

    $this->get(route('warga.laporans.index', ['q' => 'Lampu', 'page' => 2]))
        ->assertOk()
        ->assertViewHas('reports', fn ($reports) => $reports->total() === 11
            && $reports->currentPage() === 2);
});

it('returns not found when a warga views, edits, updates, deletes, or downloads another warga report', function () {
    Storage::fake('local');
    $warga = User::factory()->create();
    $owner = User::factory()->create();
    $report = Laporan::factory()->for($owner, 'pelapor')->create([
        'foto' => 'laporans/private-proof.jpg',
    ]);
    Storage::disk('local')->put($report->foto, 'private evidence');

    $this->actingAs($warga)
        ->get(route('warga.laporans.show', $report))
        ->assertNotFound();

    $this->get(route('warga.laporans.edit', $report))->assertNotFound();

    $this->put(route('warga.laporans.update', $report), [
        'judul' => 'Diubah tanpa izin',
        'deskripsi' => 'Konten tidak boleh berubah.',
        'tanggal' => '2026-11-04',
    ])->assertNotFound();

    $this->delete(route('warga.laporans.destroy', $report))->assertNotFound();
    $this->get(route('laporans.foto', $report))->assertNotFound();

    $this->assertDatabaseHas('laporans', [
        'id' => $report->id,
        'judul' => $report->judul,
    ]);
    Storage::disk('local')->assertExists($report->foto);
});

it('updates and deletes only a warga own pending report', function () {
    Storage::fake('local');
    $warga = User::factory()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create([
        'foto' => 'laporans/old-proof.jpg',
    ]);
    Storage::disk('local')->put($report->foto, 'old evidence');

    $this->actingAs($warga)
        ->get(route('warga.laporans.show', $report))
        ->assertOk()
        ->assertSee($report->judul)
        ->assertSee('Edit')
        ->assertSee('Hapus');

    $this->actingAs($warga)
        ->put(route('warga.laporans.update', $report), [
            'judul' => 'Judul laporan yang diperbarui',
            'deskripsi' => 'Deskripsi diperbarui dengan benar.',
            'tanggal' => '2026-11-05',
            'foto' => UploadedFile::fake()->image('new-proof.png', 20, 20),
        ])
        ->assertRedirect(route('warga.laporans.show', $report));

    $report->refresh();
    expect($report->judul)->toBe('Judul laporan yang diperbarui');
    expect($report->foto)->not->toBe('laporans/old-proof.jpg');
    Storage::disk('local')->assertMissing('laporans/old-proof.jpg');
    Storage::disk('local')->assertExists($report->foto);

    $this->delete(route('warga.laporans.destroy', $report))
        ->assertRedirect(route('warga.laporans.index'));

    $this->assertDatabaseMissing('laporans', ['id' => $report->id]);
    Storage::disk('local')->assertMissing($report->foto);
});

it('prevents a warga from editing or deleting a report after its status changes', function () {
    $warga = User::factory()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create([
        'status' => 'approved',
    ]);

    $this->actingAs($warga)
        ->get(route('warga.laporans.edit', $report))
        ->assertNotFound();

    $this->delete(route('warga.laporans.destroy', $report))
        ->assertNotFound();
});

it('applies the report policy permission matrix for admins and warga', function () {
    $warga = User::factory()->create();
    $otherWarga = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $pendingReport = Laporan::factory()->for($warga, 'pelapor')->create();
    $otherReport = Laporan::factory()->for($otherWarga, 'pelapor')->create();
    $closedReport = new Laporan;
    $closedReport->forceFill([
        'pelapor_id' => $warga->id,
        'status' => 'approved',
    ]);

    expect($warga->can('viewAny', Laporan::class))->toBeTrue();
    expect($warga->can('create', Laporan::class))->toBeTrue();
    expect($admin->can('create', Laporan::class))->toBeFalse();
    expect($warga->can('view', $pendingReport))->toBeTrue();
    expect($admin->can('view', $otherReport))->toBeTrue();
    expect($warga->can('view', $otherReport))->toBeFalse();
    expect($warga->can('update', $pendingReport))->toBeTrue();
    expect($warga->can('delete', $pendingReport))->toBeTrue();
    expect($warga->can('update', $closedReport))->toBeFalse();
    expect($warga->can('delete', $closedReport))->toBeFalse();
    expect($admin->can('updateStatus', $pendingReport))->toBeTrue();
    expect($warga->can('updateStatus', $pendingReport))->toBeFalse();
});

it('lets an admin browse all reports and update a report to a database-supported status', function () {
    $warga = User::factory()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.laporans.index', ['sort' => 'title']))
        ->assertOk()
        ->assertSee($report->judul)
        ->assertSee($warga->name);

    $this->get(route('admin.laporans.show', $report))
        ->assertOk()
        ->assertSee($warga->email)
        ->assertSee('Perbarui status');

    $this->patch(route('admin.laporans.status', $report), [
        'status' => 'approved',
        'tanggapan_admin' => 'Petugas akan menindaklanjuti laporan ini.',
    ])->assertRedirect(route('admin.laporans.show', $report));

    $this->assertDatabaseHas('laporans', [
        'id' => $report->id,
        'status' => 'approved',
        'tanggapan_admin' => 'Petugas akan menindaklanjuti laporan ini.',
    ]);

    $this->assertDatabaseHas('laporan_status_histories', [
        'laporan_id' => $report->id,
        'previous_status' => 'pending',
        'new_status' => 'approved',
        'admin_id' => $admin->id,
    ]);
});

it('does not let a warga access administrator report management routes', function () {
    $warga = User::factory()->create();
    $report = Laporan::factory()->for($warga, 'pelapor')->create();

    $this->actingAs($warga)
        ->get(route('admin.laporans.index'))
        ->assertForbidden();

    $this->get(route('admin.laporans.show', $report))->assertForbidden();
    $this->patch(route('admin.laporans.status', $report), ['status' => 'approved'])
        ->assertForbidden();
});

it('rejects unsupported report statuses and invalid query sorting', function () {
    $report = Laporan::factory()->create([
        'status' => 'pending',
    ]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from(route('admin.laporans.show', $report))
        ->patch(route('admin.laporans.status', $report), [
            'status' => 'proses',
        ])
        ->assertRedirect(route('admin.laporans.show', $report))
        ->assertSessionHasErrors(['status']);

    $this->get(route('admin.laporans.index', ['sort' => 'id desc; drop table laporans']))
        ->assertRedirect()
        ->assertSessionHasErrors(['sort']);

    $this->assertDatabaseHas('laporans', [
        'id' => $report->id,
        'status' => 'pending',
    ]);
});

it('redirects guests from report routes to login', function () {
    $report = Laporan::factory()->create();

    $this->get(route('warga.laporans.index'))->assertRedirect(route('login'));
    $this->get(route('warga.laporans.show', $report))->assertRedirect(route('login'));
    $this->get(route('admin.laporans.index'))->assertRedirect(route('login'));
    $this->patch(route('admin.laporans.status', $report), ['status' => 'approved'])
        ->assertRedirect(route('login'));
});
