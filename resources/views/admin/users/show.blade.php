@extends('layouts.dashboard', [
    'title' => 'Profil warga',
    'user' => auth()->user(),
    'isAdmin' => true,
])

@section('content')
    <a href="{{ route('admin.users.index') }}" class="inline-flex min-h-10 items-center rounded-lg pr-3 text-sm font-semibold text-brand-800 hover:text-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
        <span aria-hidden="true" class="mr-2">←</span> Kembali ke pengguna
    </a>

    @if (session('success'))
        <div role="status" class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <section class="mt-5 grid gap-6 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
        <article class="h-fit rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.14em] text-slate-600">Profil warga</p>
                    <h1 class="mt-2 text-xl font-semibold tracking-tight text-brand-950">{{ $resident->name }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ $resident->email }}</p>
                </div>
                @if ($statusAvailable && ! $resident->is_active)
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">Nonaktif</span>
                @else
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">Aktif</span>
                @endif
            </div>

            <dl class="mt-6 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs text-slate-600">Jumlah laporan</dt>
                    <dd class="mt-1 text-lg font-semibold text-brand-950">{{ number_format($resident->laporans_count) }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs text-slate-600">Tanggal terdaftar</dt>
                    <dd class="mt-1 text-sm font-semibold text-brand-950">{{ $resident->created_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</dd>
                </div>
            </dl>
            <p class="mt-4 text-xs leading-5 text-slate-600">Aktivitas terakhir tidak ditampilkan karena aplikasi belum menyimpan data tersebut.</p>

            <form method="POST" action="{{ route('admin.users.update', $resident) }}" class="mt-6 border-t border-slate-100 pt-5">
                @csrf
                @method('PUT')
                <h2 class="text-sm font-semibold text-brand-950">Perbarui profil</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="name" class="mb-1.5 block text-xs font-medium text-slate-600">Nama</label>
                        <input id="name" name="name" value="{{ old('name', $resident->name) }}" required maxlength="255" autocomplete="name"
                            aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                            @if ($errors->has('name')) aria-describedby="resident-name-error" @endif
                            class="min-h-11 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-brand-950 outline-none focus:border-brand-700 focus:ring-4 focus:ring-brand-100 @error('name') border-rose-400 @enderror">
                        @error('name')<p id="resident-name-error" class="mt-1.5 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-medium text-slate-600">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $resident->email) }}" required maxlength="255" autocomplete="email"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                            @if ($errors->has('email')) aria-describedby="resident-email-error" @endif
                            class="min-h-11 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-brand-950 outline-none focus:border-brand-700 focus:ring-4 focus:ring-brand-100 @error('email') border-rose-400 @enderror">
                        @error('email')<p id="resident-email-error" class="mt-1.5 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>
                <button type="submit" class="mt-4 min-h-11 rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">Simpan perubahan</button>
            </form>

            @if ($statusAvailable)
                <form method="POST" action="{{ route('admin.users.status', $resident) }}"
                    onsubmit="return confirm('{{ $resident->is_active ? 'Nonaktifkan akun ini? Pemiliknya akan keluar pada request berikutnya.' : 'Aktifkan kembali akun ini?' }}')"
                    class="mt-6 border-t border-slate-100 pt-5">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_active" value="{{ $resident->is_active ? '0' : '1' }}">
                    @error('is_active')<p class="mb-3 text-xs text-rose-700">{{ $message }}</p>@enderror
                    <button type="submit" class="min-h-11 rounded-xl border px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 {{ $resident->is_active ? 'border-rose-200 text-rose-700 hover:bg-rose-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                        {{ $resident->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}
                    </button>
                </form>
            @endif
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/30">
            <div class="flex items-end justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-brand-950">Laporan warga</h2>
                    <p class="mt-1 text-xs text-slate-600">Daftar laporan yang dikirim oleh akun ini.</p>
                </div>
                <span class="text-xs font-medium text-slate-600">{{ number_format($reports->total()) }} laporan</span>
            </div>
            @if ($reports->isEmpty())
                <div class="px-6 py-12 text-center" role="status">
                    <p class="text-sm font-medium text-brand-950">Belum ada laporan</p>
                    <p class="mt-1 text-sm text-slate-600">Laporan dari warga ini akan muncul di sini.</p>
                </div>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($reports as $report)
                        @php
                            $reportStatusClass = match ($report->status) {
                                'pending' => 'bg-amber-50 text-amber-700',
                                'approved' => 'bg-sky-50 text-sky-700',
                                'rejected' => 'bg-rose-50 text-rose-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <li class="flex flex-col justify-between gap-2 px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                            <div class="min-w-0">
                                <a href="{{ route('admin.laporans.show', $report) }}" class="font-medium text-brand-900 hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">{{ $report->judul }}</a>
                                <p class="mt-1 text-xs text-slate-600">{{ $report->created_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</p>
                            </div>
                            <span class="w-fit rounded-full px-2.5 py-1 text-[11px] font-medium {{ $reportStatusClass }}">{{ \App\Models\Laporan::STATUSES[$report->status] ?? ucfirst($report->status) }}</span>
                        </li>
                    @endforeach
                </ul>
                @if ($reports->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $reports->links() }}</div>
                @endif
            @endif
        </article>
    </section>
@endsection
