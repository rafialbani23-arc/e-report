<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LaporanFilterRequest;
use App\Http\Requests\UpdateLaporanStatusRequest;
use App\Models\Laporan;
use App\Models\User;
use App\Notifications\LaporanUpdatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(LaporanFilterRequest $request): View
    {
        $reports = Laporan::query()
            ->with('pelapor:id,name,email')
            ->matchingFilters($request->validated())
            ->paginate(15)
            ->withQueryString();

        return view('laporans.index', [
            'reports' => $reports,
            'filters' => $request->validated(),
            'isAdmin' => true,
        ]);
    }

    public function show(Laporan $laporan): View
    {
        Gate::authorize('view', $laporan);
        $laporan->load([
            'pelapor:id,name,email,no_whatsapp,alamat',
            'statusHistories.admin:id,name',
        ]);

        return view('laporans.show', [
            'laporan' => $laporan,
            'isAdmin' => true,
        ]);
    }

    public function updateStatus(UpdateLaporanStatusRequest $request, Laporan $laporan): RedirectResponse
    {
        $admin = $request->user();
        abort_unless($admin instanceof User, 403);

        $validated = $request->validated();

        DB::transaction(function () use ($admin, $laporan, $validated): void {
            $lockedReport = Laporan::query()
                ->lockForUpdate()
                ->findOrFail($laporan->getKey());
            $previousStatus = $lockedReport->status;
            $previousResponse = $lockedReport->tanggapan_admin;
            $newStatus = $validated['status'];
            $newResponse = $validated['tanggapan_admin'] ?? null;
            $statusChanged = $previousStatus !== $newStatus;
            $responseChanged = $previousResponse !== $newResponse;

            if ($statusChanged || $responseChanged) {
                $lockedReport->status = $newStatus;
                $lockedReport->tanggapan_admin = $newResponse;
                $lockedReport->save();
            }

            if ($statusChanged) {
                $lockedReport->statusHistories()->create([
                    'previous_status' => $previousStatus,
                    'new_status' => $newStatus,
                    'admin_id' => $admin->getKey(),
                ]);
            }

            $notificationEvents = [];

            if ($statusChanged) {
                $notificationEvents[] = 'status';
            }

            if ($responseChanged && $newResponse !== null) {
                $notificationEvents[] = 'response';
            }

            if ($notificationEvents !== []) {
                abort_unless(
                    Schema::hasTable('notifications'),
                    503,
                    'Pusat notifikasi belum aktif karena migration database belum diterapkan.',
                );

                $lockedReport->pelapor->notify(new LaporanUpdatedNotification(
                    laporanId: $lockedReport->getKey(),
                    laporanTitle: $lockedReport->judul,
                    events: $notificationEvents,
                    status: $newStatus,
                ));
            }
        });

        return redirect()->route('admin.laporans.show', $laporan)
            ->with('success', 'Status laporan dan tanggapan admin berhasil diperbarui.');
    }
}
