@extends('layouts.dashboard', [
    'title' => $isAdmin ? 'Semua laporan' : 'Laporan saya',
    'user' => auth()->user(),
    'isAdmin' => $isAdmin,
])

@section('content')
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-brand-700">{{ $isAdmin ? 'Administrasi laporan' : 'Arsip pribadi' }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-brand-950 sm:text-3xl">{{ $isAdmin ? 'Semua laporan' : 'Laporan saya' }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ $isAdmin ? 'Tinjau laporan warga dan perbarui status penanganannya.' : 'Temukan dan kelola laporan yang telah Anda sampaikan.' }}</p>
        </div>
        @unless ($isAdmin)
            <a href="{{ route('warga.laporans.create') }}" class="inline-flex w-fit items-center gap-2 rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800">
                <span aria-hidden="true">+</span>
                Buat laporan
            </a>
        @endunless
    </section>

    @if (session('success'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="status">{{ session('warning') }}</div>
    @endif

    <form method="GET" action="{{ route($isAdmin ? 'admin.laporans.index' : 'warga.laporans.index') }}" class="mt-7 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(200px,1.5fr)_repeat(4,minmax(130px,1fr))_auto]">
            <div class="sm:col-span-2 xl:col-span-1">
                <label for="q" class="mb-1.5 block text-xs font-medium text-slate-600">Cari laporan</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Judul atau deskripsi"
                    class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-slate-600 focus:border-brand-700 focus:bg-white focus:ring-4 focus:ring-brand-100">
            </div>
            <div>
                <label for="status" class="mb-1.5 block text-xs font-medium text-slate-600">Status</label>
                <select id="status" name="status" class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-600 outline-none focus:border-brand-700 focus:ring-4 focus:ring-brand-100">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Laporan::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_from" class="mb-1.5 block text-xs font-medium text-slate-600">Dari tanggal</label>
                <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}"
                    class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-600 outline-none focus:border-brand-700 focus:ring-4 focus:ring-brand-100">
            </div>
            <div>
                <label for="date_to" class="mb-1.5 block text-xs font-medium text-slate-600">Sampai tanggal</label>
                <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}"
                    class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-600 outline-none focus:border-brand-700 focus:ring-4 focus:ring-brand-100">
            </div>
            <div>
                <label for="sort" class="mb-1.5 block text-xs font-medium text-slate-600">Urutkan</label>
                <select id="sort" name="sort" class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-600 outline-none focus:border-brand-700 focus:ring-4 focus:ring-brand-100">
                    <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Terbaru</option>
                    <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Terlama</option>
                    <option value="title" @selected(($filters['sort'] ?? '') === 'title')>Judul A–Z</option>
                    <option value="date" @selected(($filters['sort'] ?? '') === 'date')>Tanggal kejadian</option>
                </select>
            </div>
            <button type="submit" class="min-h-11 rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">Terapkan</button>
        </div>
        @if ($errors->any())
            <div class="mt-3 space-y-1 text-sm text-red-600" role="alert">
                @foreach ($errors->all() as $message)
                    <p>{{ $message }}</p>
                @endforeach
            </div>
        @endif
    </form>

    <section class="mt-5 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/30">
        @if ($reports->isEmpty())
            <div class="flex flex-col items-center px-6 py-16 text-center">
                <span class="grid size-12 place-items-center rounded-2xl bg-brand-50 text-lg text-brand-700">—</span>
                <h2 class="mt-4 text-sm font-semibold text-brand-950">Tidak ada laporan ditemukan</h2>
                <p class="mt-1 max-w-sm text-sm leading-6 text-slate-600">{{ $isAdmin ? 'Ubah kata kunci atau filter untuk melihat laporan lainnya.' : 'Coba ubah filter atau buat laporan baru.' }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-left">
                    <thead class="bg-slate-50/70 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                        <tr>
                            <th class="px-5 py-3.5 sm:px-6">Laporan</th>
                            @if ($isAdmin)
                                <th class="px-4 py-3.5">Pelapor</th>
                            @endif
                            <th class="px-4 py-3.5">Tanggal kejadian</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right sm:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($reports as $report)
                            @php
                                $statusClass = match ($report->status) {
                                    'pending' => 'bg-amber-50 text-amber-700 ring-amber-200/70',
                                    'approved' => 'bg-sky-50 text-sky-700 ring-sky-200/70',
                                    'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200/70',
                                    default => 'bg-slate-100 text-slate-600 ring-slate-200',
                                };
                            @endphp
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="max-w-[280px] px-5 py-4 sm:px-6">
                                    <a href="{{ route($isAdmin ? 'admin.laporans.show' : 'warga.laporans.show', $report) }}" class="block truncate text-sm font-medium text-brand-950 hover:text-brand-700">{{ $report->judul }}</a>
                                    <p class="mt-1 text-xs text-slate-600">#{{ str_pad((string) $report->id, 5, '0', STR_PAD_LEFT) }}</p>
                                </td>
                                @if ($isAdmin)
                                    <td class="px-4 py-4 text-sm text-slate-600">{{ $report->pelapor?->name ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $report->tanggal?->locale('id')->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-medium ring-1 ring-inset {{ $statusClass }}">{{ \App\Models\Laporan::STATUSES[$report->status] ?? ucfirst($report->status) }}</span>
                                </td>
                                <td class="px-5 py-4 text-right sm:px-6">
                                    <a href="{{ route($isAdmin ? 'admin.laporans.show' : 'warga.laporans.show', $report) }}" class="text-xs font-semibold text-brand-700 hover:text-brand-950">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                {{ $reports->onEachSide(1)->links() }}
            </div>
        @endif
    </section>
@endsection
