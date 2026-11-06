@extends('layouts.dashboard', [
    'title' => 'Manajemen pengguna',
    'user' => auth()->user(),
    'isAdmin' => true,
])

@section('content')
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-brand-700">Administrasi</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-brand-950 sm:text-3xl">Manajemen pengguna</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Kelola profil dan akses akun warga tanpa mengubah riwayat laporan mereka.</p>
        </div>
        <p class="text-sm text-slate-600"><span class="font-semibold text-brand-950">{{ number_format($residents->total()) }}</span> warga</p>
    </section>

    @if (session('success'))
        <div role="status" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    @unless ($statusAvailable)
        <div role="status" class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800">
            Status akun belum dapat dikelola sampai migration status akun diterapkan. Akun lama tetap berfungsi seperti biasa.
        </div>
    @endunless

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/30">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-[minmax(14rem,1fr)_12rem_auto] sm:items-end sm:p-5">
            <div>
                <label for="search" class="mb-1.5 block text-xs font-medium text-slate-600">Cari nama atau email</label>
                <input id="search" name="search" type="search" value="{{ request('search') }}" maxlength="255" placeholder="Contoh: Siti atau nama@email.com"
                    class="min-h-11 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-brand-950 outline-none transition placeholder:text-slate-600 focus:border-brand-700 focus:ring-4 focus:ring-brand-100">
            </div>
            @if ($statusAvailable)
                <div>
                    <label for="status" class="mb-1.5 block text-xs font-medium text-slate-600">Status akun</label>
                    <select id="status" name="status" class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-brand-950 outline-none focus:border-brand-700 focus:ring-4 focus:ring-brand-100">
                        <option value="">Semua status</option>
                        <option value="active" @selected(request('status') === 'active')>Aktif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                    </select>
                </div>
            @endif
            <button type="submit" class="min-h-11 rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">Terapkan</button>
        </form>

        @if ($residents->isEmpty())
            <div class="px-6 py-14 text-center" role="status">
                <span aria-hidden="true" class="mx-auto grid size-12 place-items-center rounded-2xl bg-brand-50 text-brand-700">W</span>
                <h2 class="mt-4 text-sm font-semibold text-brand-950">Belum ada warga ditemukan</h2>
                <p class="mt-1 text-sm text-slate-600">Coba ubah kata pencarian atau filter yang digunakan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full table-fixed text-left sm:table-auto sm:min-w-[700px]">
                    <thead class="bg-slate-50/70 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                        <tr>
                            <th scope="col" class="w-[52%] px-5 py-3.5 sm:w-auto sm:px-6">Warga</th>
                            <th scope="col" class="hidden px-4 py-3.5 sm:table-cell">Laporan</th>
                            <th scope="col" class="hidden px-4 py-3.5 sm:table-cell">Terdaftar</th>
                            <th scope="col" class="w-[22%] px-4 py-3.5 sm:w-auto">Status akun</th>
                            <th scope="col" class="w-[26%] px-5 py-3.5 text-right sm:w-auto sm:px-6"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($residents as $resident)
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-4 sm:px-6">
                                    <a href="{{ route('admin.users.show', $resident) }}" class="font-medium text-brand-900 hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">{{ $resident->name }}</a>
                                    <p class="mt-1 break-words text-xs text-slate-600">{{ $resident->email }}</p>
                                </td>
                                <td class="hidden px-4 py-4 text-sm text-slate-600 sm:table-cell">{{ number_format($resident->laporans_count) }}</td>
                                <td class="hidden px-4 py-4 text-sm text-slate-600 sm:table-cell">{{ $resident->created_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-4">
                                    @if ($statusAvailable && ! $resident->is_active)
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">Nonaktif</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">Aktif</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right sm:px-6">
                                    <a href="{{ route('admin.users.show', $resident) }}" class="inline-flex min-h-9 items-center rounded-lg px-2 text-sm font-semibold text-brand-800 hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 sm:px-3">Lihat<span class="sr-only"> profil</span></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($residents->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $residents->links() }}</div>
            @endif
        @endif
    </section>
@endsection
