<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Http\Requests\LaporanFilterRequest;
use App\Http\Requests\StoreLaporanRequest;
use App\Http\Requests\UpdateLaporanRequest;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class LaporanController extends Controller
{
    public function index(LaporanFilterRequest $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $reports = $user->laporans()
            ->matchingFilters($request->validated())
            ->paginate(10)
            ->withQueryString();

        return view('laporans.index', [
            'reports' => $reports,
            'filters' => $request->validated(),
            'isAdmin' => false,
        ]);
    }

    public function create(): View
    {
        return view('laporans.create', ['laporan' => new Laporan]);
    }

    public function store(StoreLaporanRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $validated = $request->safe()->only(['judul', 'deskripsi', 'tanggal']);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $this->storeEvidence($request->file('foto'));
        }

        $laporan = $user->laporans()->create($validated);

        return redirect()->route('warga.laporans.show', $laporan)
            ->with('success', 'Laporan berhasil dikirim.');
    }

    public function show(Laporan $laporan): View
    {
        Gate::authorize('view', $laporan);
        $laporan->load('statusHistories.admin:id,name');

        return view('laporans.show', [
            'laporan' => $laporan,
            'isAdmin' => false,
        ]);
    }

    public function edit(Laporan $laporan): View
    {
        Gate::authorize('update', $laporan);

        return view('laporans.create', [
            'laporan' => $laporan,
            'isAdmin' => false,
        ]);
    }

    public function update(UpdateLaporanRequest $request, Laporan $laporan): RedirectResponse
    {
        $validated = $request->safe()->only(['judul', 'deskripsi', 'tanggal']);
        $previousEvidence = $laporan->foto;

        if ($request->hasFile('foto')) {
            $validated['foto'] = $this->storeEvidence($request->file('foto'));
        }

        $laporan->update($validated);

        if (isset($validated['foto']) && $previousEvidence !== null) {
            if (! Storage::disk('local')->delete($previousEvidence)) {
                Log::warning('Unable to remove replaced report evidence.', [
                    'laporan_id' => $laporan->id,
                ]);

                return redirect()->route('warga.laporans.show', $laporan)
                    ->with('warning', 'Laporan tersimpan, tetapi foto bukti sebelumnya gagal dihapus.');
            }
        }

        return redirect()->route('warga.laporans.show', $laporan)
            ->with('success', 'Perubahan laporan berhasil disimpan.');
    }

    public function destroy(Laporan $laporan): RedirectResponse
    {
        Gate::authorize('delete', $laporan);

        $evidencePath = $laporan->foto;
        $laporan->delete();

        if ($evidencePath !== null && ! Storage::disk('local')->delete($evidencePath)) {
            Log::warning('Unable to remove evidence for deleted report.', [
                'laporan_id' => $laporan->id,
            ]);

            return redirect()->route('warga.laporans.index')
                ->with('warning', 'Laporan terhapus, tetapi foto bukti gagal dihapus.');
        }

        return redirect()->route('warga.laporans.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    private function storeEvidence(UploadedFile $file): string
    {
        $path = $file->store('laporans', 'local');

        if (! is_string($path)) {
            throw new RuntimeException('Bukti laporan gagal disimpan.');
        }

        return $path;
    }
}
