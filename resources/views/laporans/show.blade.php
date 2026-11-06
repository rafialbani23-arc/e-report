@extends('layouts.dashboard', [
    'title' => 'Detail laporan',
    'user' => auth()->user(),
    'isAdmin' => $isAdmin,
])

@section('content')
    @php
        $statusClass = match ($laporan->status) {
            'pending' => 'bg-amber-50 text-amber-700 ring-amber-200/70',
            'approved' => 'bg-sky-50 text-sky-700 ring-sky-200/70',
            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200/70',
            default => 'bg-slate-100 text-slate-600 ring-slate-200',
        };
        $statusLabel = \App\Models\Laporan::STATUSES[$laporan->status] ?? ucfirst($laporan->status);
    @endphp

    <div class="mx-auto max-w-5xl">
        <a href="{{ route($isAdmin ? 'admin.laporans.index' : 'warga.laporans.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-brand-800">
            <span aria-hidden="true">←</span>
            {{ $isAdmin ? 'Kembali ke semua laporan' : 'Kembali ke laporan saya' }}
        </a>

        @if (session('success'))
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="status">{{ session('warning') }}</div>
        @endif

        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                @foreach ($errors->all() as $message)
                    <p>{{ $message }}</p>
                @endforeach
            </div>
        @endif

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/30">
            <header class="flex flex-col justify-between gap-4 border-b border-slate-100 px-5 py-6 sm:flex-row sm:items-start sm:px-7">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-medium text-slate-600">Laporan #{{ str_pad((string) $laporan->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-medium ring-1 ring-inset {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <h1 class="mt-3 break-words text-2xl font-semibold tracking-tight text-brand-950">{{ $laporan->judul }}</h1>
                    <p class="mt-2 text-sm text-slate-600">Dikirim {{ $laporan->created_at?->locale('id')->translatedFormat('d F Y, H:i') }}</p>
                </div>
                @if (! $isAdmin && $laporan->status === 'pending')
                    <div class="flex shrink-0 gap-2">
                        <a href="{{ route('warga.laporans.edit', $laporan) }}" class="inline-flex min-h-11 items-center rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">Edit</a>
                        <form method="POST" action="{{ route('warga.laporans.destroy', $laporan) }}" onsubmit="return confirm('Hapus laporan ini? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="min-h-11 rounded-xl border border-rose-200 px-3.5 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-700">Hapus</button>
                        </form>
                    </div>
                @endif
            </header>

            <div class="grid gap-0 lg:grid-cols-[minmax(0,1.4fr)_minmax(250px,0.8fr)]">
                <div class="space-y-7 px-5 py-6 sm:px-7">
                    <div>
                        <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-600">Deskripsi kejadian</h2>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $laporan->deskripsi }}</p>
                    </div>
                    @if ($laporan->tanggapan_admin)
                        <div class="rounded-xl border border-brand-100 bg-brand-50/70 p-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-700">Tanggapan admin</h2>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $laporan->tanggapan_admin }}</p>
                        </div>
                    @endif
                    @if ($laporan->foto)
                        <div>
                            <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-600">Foto bukti</h2>
                            <a href="{{ route('laporans.foto', $laporan) }}" target="_blank" rel="noopener" class="mt-3 block max-w-xl overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                <img src="{{ route('laporans.foto', $laporan) }}" alt="Bukti foto laporan: {{ $laporan->judul }}" class="max-h-[28rem] w-full object-contain">
                            </a>
                        </div>
                    @endif
                </div>

                <aside class="border-t border-slate-100 bg-slate-50/60 px-5 py-6 sm:px-7 lg:border-l lg:border-t-0">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-600">Informasi laporan</h2>
                    <dl class="mt-5 space-y-5">
                        <div>
                            <dt class="text-xs text-slate-600">Tanggal kejadian</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-700">{{ $laporan->tanggal?->locale('id')->translatedFormat('d F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-600">Terakhir diperbarui</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-700">{{ $laporan->updated_at?->locale('id')->translatedFormat('d F Y, H:i') }}</dd>
                        </div>
                        @if ($isAdmin)
                            <div class="border-t border-slate-200 pt-5">
                                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-600">Informasi pelapor</dt>
                                <dd class="mt-3 text-sm font-semibold text-brand-950">{{ $laporan->pelapor->name }}</dd>
                                <dd class="mt-1 break-all text-sm text-slate-600">{{ $laporan->pelapor->email }}</dd>
                                @if ($laporan->pelapor->no_whatsapp)
                                    <dd class="mt-1 text-sm text-slate-600">{{ $laporan->pelapor->no_whatsapp }}</dd>
                                @endif
                                @if ($laporan->pelapor->alamat)
                                    <dd class="mt-1 text-sm leading-6 text-slate-600">{{ $laporan->pelapor->alamat }}</dd>
                                @endif
                            </div>
                        @endif
                    </dl>
                </aside>
            </div>
        </section>

        <section class="mt-5 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-brand-950">Riwayat status</h2>
                    <p class="mt-1 text-sm text-slate-600">Catatan perubahan status laporan.</p>
                </div>
                <span class="rounded-lg bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-600">{{ $laporan->statusHistories->count() }} perubahan</span>
            </div>

            @if ($laporan->statusHistories->isEmpty())
                <p class="mt-5 rounded-xl bg-slate-50 px-4 py-4 text-sm text-slate-600">Belum ada perubahan status.</p>
            @else
                <ol class="mt-5 space-y-0">
                    @foreach ($laporan->statusHistories as $history)
                        <li class="relative flex gap-4 pb-6 last:pb-0">
                            @unless ($loop->last)
                                <span class="absolute bottom-0 left-[0.4375rem] top-3 w-px bg-slate-200"></span>
                            @endunless
                            <span class="relative mt-1.5 size-2.5 shrink-0 rounded-full bg-brand-700 ring-4 ring-brand-50"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-brand-950">
                                    {{ \App\Models\Laporan::STATUSES[$history->previous_status] ?? ucfirst($history->previous_status) }}
                                    <span class="px-1 text-slate-600">→</span>
                                    {{ \App\Models\Laporan::STATUSES[$history->new_status] ?? ucfirst($history->new_status) }}
                                </p>
                                <p class="mt-1 text-xs text-slate-600">
                                    Diubah oleh {{ $history->admin->name }} · {{ $history->created_at?->locale('id')->translatedFormat('d F Y, H:i') }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        @if ($isAdmin)
            <section class="mt-5 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                <div>
                    <h2 class="text-base font-semibold text-brand-950">Perbarui status</h2>
                    <p class="mt-1 text-sm text-slate-600">Pilih status yang tersedia dan tulis tanggapan untuk warga.</p>
                </div>
                <form method="POST" action="{{ route('admin.laporans.status', $laporan) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-3 sm:grid-cols-[minmax(180px,0.65fr)_minmax(0,1.35fr)]">
                        <div>
                            <label for="status" class="mb-1.5 block text-xs font-medium text-slate-600">Status laporan</label>
                            <select id="status" name="status" required
                                aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}"
                                @if ($errors->has('status')) aria-describedby="status-error" @endif
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-brand-700 focus:ring-4 focus:ring-brand-100 @error('status') border-rose-400 @enderror">
                                @foreach (\App\Models\Laporan::STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $laporan->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<p id="status-error" class="mt-1.5 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="tanggapan_admin" class="mb-1.5 block text-xs font-medium text-slate-600">Tanggapan untuk warga</label>
                            <textarea id="tanggapan_admin" name="tanggapan_admin" rows="3" maxlength="10000"
                                aria-invalid="{{ $errors->has('tanggapan_admin') ? 'true' : 'false' }}"
                                @if ($errors->has('tanggapan_admin')) aria-describedby="tanggapan-admin-error" @endif
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm leading-6 outline-none placeholder:text-slate-600 focus:border-brand-700 focus:ring-4 focus:ring-brand-100 @error('tanggapan_admin') border-rose-400 @enderror"
                                placeholder="Tuliskan informasi tindak lanjut atau alasan keputusan.">{{ old('tanggapan_admin', $laporan->tanggapan_admin) }}</textarea>
                            @error('tanggapan_admin')<p id="tanggapan-admin-error" class="mt-1.5 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-xl bg-brand-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800">Simpan pembaruan</button>
                    </div>
                </form>
                <p class="mt-3 text-xs leading-5 text-slate-600">Riwayat hanya mencatat perubahan status; pembaruan tanggapan tanpa perubahan status tidak menambah baris riwayat.</p>
            </section>
        @endif
    </div>
@endsection
