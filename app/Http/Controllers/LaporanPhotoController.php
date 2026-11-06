<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanPhotoController extends Controller
{
    public function show(Laporan $laporan): StreamedResponse
    {
        Gate::authorize('view', $laporan);

        abort_unless($laporan->foto !== null && Storage::disk('local')->exists($laporan->foto), 404);

        return Storage::disk('local')->response(
            $laporan->foto,
            basename($laporan->foto),
            ['X-Content-Type-Options' => 'nosniff'],
            'inline',
        );
    }
}
