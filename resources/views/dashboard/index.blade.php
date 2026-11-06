@extends('layouts.dashboard', [
    'title' => $isAdmin ? 'Dashboard admin' : 'Dashboard saya',
    'user' => auth()->user(),
    'isAdmin' => $isAdmin,
])

@section('content')
    <section class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
        <div>
            <p class="text-sm font-medium text-brand-700">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-brand-950 sm:text-3xl">
                {{ $isAdmin ? 'Selamat datang, Admin' : 'Halo, '.auth()->user()->name }}
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                {{ $isAdmin ? 'Pantau volume laporan, status penanganan, dan aktivitas terbaru.' : 'Ringkasan laporan dan pembaruan status Anda ada di satu tempat.' }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @unless ($isAdmin)
                <a href="{{ route('warga.laporans.create') }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                    <span aria-hidden="true">+</span>
                    Buat laporan
                </a>
            @endunless
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-brand-100 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm">
                <span class="size-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                Data diperbarui langsung
            </span>
        </div>
    </section>

    @if ($isAdmin)
        <section class="mt-7 flex flex-col justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-200/20 sm:flex-row sm:items-end sm:p-5">
            <div>
                <h2 class="text-sm font-semibold text-brand-950">Periode analitik</h2>
                <p class="mt-1 text-xs leading-5 text-slate-600">Filter statistik, grafik, laporan terbaru, dan aktivitas perubahan status.</p>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
                <div>
                    <label for="period" class="mb-1.5 block text-xs font-medium text-slate-600">Tampilkan periode</label>
                    <select id="period" name="period" class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-brand-950 outline-none transition focus:border-brand-700 focus:ring-4 focus:ring-brand-100 sm:min-w-52">
                        @foreach ($periods as $key => $label)
                            <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="min-h-11 rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                    Terapkan
                </button>
            </form>
        </section>
    @endif

    <section aria-label="Statistik laporan" class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30">
            <div class="flex items-start justify-between gap-3">
                <p class="text-sm font-medium text-slate-600">Total laporan</p>
                <span aria-hidden="true" class="grid size-9 place-items-center rounded-xl bg-brand-50 text-xs font-semibold text-brand-700">Σ</span>
            </div>
            <p class="mt-5 text-3xl font-semibold tracking-tight text-brand-950">{{ number_format($statistics['total']) }}</p>
            <p class="mt-1 text-xs text-slate-600">{{ $isAdmin ? $periodLabel : 'Laporan yang Anda kirim' }}</p>
        </article>
        @foreach ([
            ['key' => 'pending', 'label' => 'Menunggu', 'note' => 'Belum diputuskan', 'icon' => '01', 'iconClass' => 'bg-amber-50 text-amber-700'],
            ['key' => 'approved', 'label' => 'Disetujui', 'note' => 'Telah disetujui admin', 'icon' => '02', 'iconClass' => 'bg-sky-50 text-sky-700'],
            ['key' => 'rejected', 'label' => 'Ditolak', 'note' => 'Ditolak oleh admin', 'icon' => '03', 'iconClass' => 'bg-rose-50 text-rose-700'],
        ] as $status)
            <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-sm font-medium text-slate-600">{{ $status['label'] }}</p>
                    <span aria-hidden="true" class="grid size-9 place-items-center rounded-xl text-xs font-semibold {{ $status['iconClass'] }}">{{ $status['icon'] }}</span>
                </div>
                <p class="mt-5 text-3xl font-semibold tracking-tight text-brand-950">{{ number_format($statistics[$status['key']]) }}</p>
                <p class="mt-1 text-xs text-slate-600">{{ $isAdmin ? $periodLabel : $status['note'] }}</p>
            </article>
        @endforeach
        @if ($isAdmin)
            <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-sm font-medium text-slate-600">Warga terdaftar</p>
                    <span aria-hidden="true" class="grid size-9 place-items-center rounded-xl bg-violet-50 text-xs font-semibold text-violet-700">W</span>
                </div>
                <p class="mt-5 text-3xl font-semibold tracking-tight text-brand-950">{{ number_format($residentCount) }}</p>
                <p class="mt-1 text-xs text-slate-600">Semua warga terdaftar · sepanjang waktu</p>
            </article>
        @endif
    </section>

    @if ($isAdmin)
        <section aria-label="Analitik laporan" class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.8fr)]">
            <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 sm:p-6">
                <div>
                    <h2 class="text-base font-semibold text-brand-950">Tren laporan per bulan</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-600">Jumlah laporan dibuat pada periode {{ $periodLabel }}. Batang kosong menunjukkan tidak ada laporan pada bulan tersebut.</p>
                </div>
                @if (count($monthlyTrend) === 0)
                    <div class="mt-6 rounded-xl bg-slate-50 px-4 py-8 text-center" role="status">
                        <p class="text-sm font-medium text-slate-700">Belum ada data tren</p>
                        <p class="mt-1 text-xs text-slate-600">Grafik akan terisi setelah laporan tercatat.</p>
                    </div>
                @else
                    <div class="mt-6 overflow-x-auto pb-2" role="img" aria-label="Grafik jumlah laporan per bulan untuk {{ $periodLabel }}">
                        <ol class="flex min-h-48 min-w-max items-end gap-3 border-b border-slate-100 px-1 pb-2 sm:gap-4">
                            @foreach ($monthlyTrend as $month)
                                @php
                                    $barHeight = $month['count'] > 0 ? max(8, (int) round(($month['count'] / $trendMaximum) * 100)) : 0;
                                @endphp
                                <li class="flex w-12 flex-col items-center justify-end gap-2 sm:w-14" aria-label="{{ $month['label'] }}: {{ $month['count'] }} laporan">
                                    <span class="text-xs font-medium tabular-nums text-slate-600">{{ $month['count'] > 0 ? number_format($month['count']) : '' }}</span>
                                    <span class="flex h-32 w-full items-end justify-center">
                                        <span class="block w-6 rounded-t-md bg-brand-700 transition-all sm:w-7" style="height: {{ $barHeight }}%"></span>
                                    </span>
                                    <span class="whitespace-nowrap text-[11px] font-medium text-slate-600">{{ $month['label'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                    <p class="sr-only">Nilai grafik: @foreach ($monthlyTrend as $month) {{ $month['label'] }} {{ $month['count'] }} laporan; @endforeach</p>
                @endif
            </article>

            <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 sm:p-6">
                <div>
                    <h2 class="text-base font-semibold text-brand-950">Distribusi status</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-600">Proporsi laporan dalam periode {{ $periodLabel }}.</p>
                </div>
                <div class="mt-7 space-y-6">
                    @foreach ($statusBreakdown as $status)
                        @php
                            $percentage = $statistics['total'] > 0
                                ? (int) round(($status['count'] / $statistics['total']) * 100)
                                : 0;
                            $barClass = match ($status['key']) {
                                'pending' => 'bg-amber-500',
                                'approved' => 'bg-brand-700',
                                default => 'bg-rose-500',
                            };
                        @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                <span class="font-medium text-slate-600">{{ $status['label'] }}</span>
                                <span class="text-xs tabular-nums text-slate-600">{{ number_format($status['count']) }} · {{ $percentage }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="{{ $status['label'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percentage }}">
                                <div class="{{ $barClass }} h-full rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
            <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 sm:p-6">
                <div>
                    <h2 class="text-base font-semibold text-brand-950">Perbandingan bulanan</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-600">Jumlah laporan dibuat pada bulan ini dibanding periode kalender setara bulan lalu.</p>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-brand-50/70 p-4">
                        <p class="text-xs font-medium text-brand-700">{{ $comparison['current_label'] }}</p>
                        <p class="mt-3 text-2xl font-semibold tabular-nums text-brand-950">{{ number_format($comparison['current']) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-medium text-slate-600">{{ $comparison['previous_label'] }}</p>
                        <p class="mt-3 text-2xl font-semibold tabular-nums text-brand-950">{{ number_format($comparison['previous']) }}</p>
                    </div>
                </div>
                <p class="mt-4 text-xs leading-5 text-slate-600">Perbandingan ini mengikuti kalender bulanan dan tidak berubah saat filter periode di atas diganti.</p>
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/30">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
                    <div>
                        <h2 class="text-base font-semibold text-brand-950">Laporan terbaru</h2>
                        <p class="mt-1 text-xs text-slate-600">Laporan dibuat pada periode {{ $periodLabel }}.</p>
                    </div>
                    <span class="rounded-lg bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-600">8 terakhir</span>
                </div>
                @include('dashboard.partials.reports')
            </article>
        </section>

        @include('dashboard.partials.activities')
    @else
        <section class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
            <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 sm:p-6">
                <div>
                    <h2 class="text-base font-semibold text-brand-950">Perkembangan laporan</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-600">Ringkasan seluruh laporan yang Anda kirim.</p>
                </div>
                <div class="mt-6 space-y-5">
                    @foreach ([
                        ['key' => 'pending', 'label' => 'Menunggu', 'color' => 'bg-amber-500'],
                        ['key' => 'approved', 'label' => 'Disetujui', 'color' => 'bg-brand-700'],
                        ['key' => 'rejected', 'label' => 'Ditolak', 'color' => 'bg-rose-500'],
                    ] as $status)
                        @php
                            $percentage = $statistics['total'] > 0
                                ? (int) round(($statistics[$status['key']] / $statistics['total']) * 100)
                                : 0;
                        @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-600">{{ $status['label'] }}</span>
                                <span class="text-xs tabular-nums text-slate-600">{{ number_format($statistics[$status['key']]) }} · {{ $percentage }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="{{ $status['label'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percentage }}">
                                <div class="{{ $status['color'] }} h-full rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/30">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
                    <div>
                        <h2 class="text-base font-semibold text-brand-950">Laporan terbaru</h2>
                        <p class="mt-1 text-xs text-slate-600">Laporan yang Anda kirim.</p>
                    </div>
                    <a href="{{ route('warga.laporans.index') }}" class="rounded-lg px-2 py-1.5 text-xs font-semibold text-brand-800 hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">Semua laporan</a>
                </div>
                @include('dashboard.partials.reports')
            </article>
        </section>

        @include('dashboard.partials.activities')
    @endif
@endsection
