@extends('layouts.dashboard', [
    'title' => 'Notifikasi',
    'user' => auth()->user(),
    'isAdmin' => $isAdmin,
])

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-700">Pusat informasi</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-brand-950">Notifikasi</h1>
                <p class="mt-2 text-sm text-slate-600">Pembaruan status dan tanggapan untuk laporan Anda.</p>
            </div>
            @if ($unreadNotificationCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-brand-200 hover:text-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                        Tandai semua dibaca
                    </button>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
        @endif

        @if ($notifications->isEmpty())
            <section class="mt-7 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <span aria-hidden="true" class="mx-auto grid size-12 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                    <svg viewBox="0 0 24 24" fill="none" class="size-6" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4.5-5.8V4a1.5 1.5 0 0 0-3 0v1.2A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9"/>
                    </svg>
                </span>
                <h2 class="mt-4 text-base font-semibold text-brand-950">Belum ada notifikasi</h2>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-600">Pembaruan dari admin terkait laporan Anda akan muncul di sini.</p>
            </section>
        @else
            <section aria-label="Daftar notifikasi" class="mt-7 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/30">
                <ol class="divide-y divide-slate-100">
                    @foreach ($notifications as $notification)
                        @php
                            $isUnread = $notification->unread();
                            $reportId = $notification->data['laporan_id'] ?? null;
                        @endphp
                        <li class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:px-6 {{ $isUnread ? 'bg-brand-50/40' : 'bg-white' }}">
                            <span class="mt-1.5 hidden size-2 shrink-0 rounded-full bg-brand-700 sm:block {{ $isUnread ? '' : 'invisible' }}" aria-hidden="true"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-brand-950">{{ $notification->data['laporan_judul'] ?? 'Pembaruan laporan' }}</span>
                                    @if ($isUnread)
                                        <span class="rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-brand-800">Baru</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">Dibaca</span>
                                    @endif
                                </div>
                                <p class="mt-1.5 text-sm leading-6 text-slate-600">{{ $notification->data['message'] ?? 'Ada pembaruan terkait laporan Anda.' }}</p>
                                <p class="mt-2 text-xs text-slate-600">
                                    <time datetime="{{ $notification->created_at?->toAtomString() }}">{{ $notification->created_at?->locale('id')->translatedFormat('d F Y, H:i') }}</time>
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-3 sm:justify-end">
                                @if (is_numeric($reportId))
                                    <a href="{{ route($isAdmin ? 'admin.laporans.show' : 'warga.laporans.show', $reportId) }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-brand-800 transition hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                                        Buka laporan
                                        <span class="sr-only">: {{ $notification->data['laporan_judul'] ?? 'Pembaruan laporan' }}</span>
                                    </a>
                                @endif
                                @if ($isUnread)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex min-h-10 items-center rounded-lg border border-slate-200 px-3 text-xs font-medium text-slate-600 transition hover:bg-white hover:text-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700" aria-label="Tandai notifikasi {{ $notification->data['laporan_judul'] ?? 'laporan' }} sudah dibaca">
                                            Tandai dibaca
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <div class="mt-6">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
