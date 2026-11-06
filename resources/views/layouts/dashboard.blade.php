<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }} · E-Report</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f6f7f5] font-sans text-brand-950 antialiased">
        <div class="min-h-screen lg:flex">
            <button type="button" data-sidebar-backdrop aria-label="Tutup menu navigasi" class="fixed inset-0 z-30 hidden bg-brand-950/45 backdrop-blur-[2px] lg:hidden"></button>
            <aside id="dashboard-sidebar" data-sidebar tabindex="-1" aria-label="Navigasi utama" class="fixed inset-y-0 left-0 z-40 hidden w-[17rem] flex-col overflow-y-auto bg-brand-950 px-5 py-6 text-white shadow-xl shadow-brand-950/10 lg:static lg:flex lg:shrink-0 lg:shadow-none">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2">
                    <span class="grid size-10 place-items-center rounded-xl bg-white text-xs font-bold text-brand-950">ER</span>
                    <span>
                        <span class="block text-sm font-semibold tracking-wide">E-Report</span>
                        <span class="block text-xs text-white/55">Ruang pelaporan</span>
                    </span>
                </a>
                <button type="button" data-sidebar-close class="ml-auto mt-5 inline-flex min-h-11 items-center gap-2 self-end rounded-lg px-3 text-sm font-medium text-white/75 transition hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white lg:hidden">
                    <span aria-hidden="true">×</span>
                    Tutup menu
                </button>

                <div class="mt-11 px-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/65">Menu utama</div>
                <nav aria-label="Menu utama" class="mt-3 space-y-1">
                    <a href="{{ route('dashboard') }}" @class([
                        'flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition',
                        'bg-white/10 text-white' => request()->routeIs('dashboard', 'admin.dashboard', 'warga.dashboard'),
                        'text-white/75 hover:bg-white/10 hover:text-white' => ! request()->routeIs('dashboard', 'admin.dashboard', 'warga.dashboard'),
                    ]) @if (request()->routeIs('dashboard', 'admin.dashboard', 'warga.dashboard')) aria-current="page" @endif>
                        <span class="grid size-7 place-items-center rounded-lg bg-white/10 text-xs">01</span>
                        Dashboard
                    </a>
                    <a href="{{ route($isAdmin ? 'admin.laporans.index' : 'warga.laporans.index') }}" @class([
                        'flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition',
                        'bg-white/10 text-white' => request()->routeIs('admin.laporans.*', 'warga.laporans.*'),
                        'text-white/75 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.laporans.*', 'warga.laporans.*'),
                    ]) @if (request()->routeIs('admin.laporans.*', 'warga.laporans.*')) aria-current="page" @endif>
                        <span class="grid size-7 place-items-center rounded-lg bg-white/5 text-xs">02</span>
                        {{ $isAdmin ? 'Semua laporan' : 'Laporan saya' }}
                    </a>
                    @if ($isAdmin)
                        <a href="{{ route('admin.users.index') }}" @class([
                            'flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition',
                            'bg-white/10 text-white' => request()->routeIs('admin.users.*'),
                            'text-white/75 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.users.*'),
                        ]) @if (request()->routeIs('admin.users.*')) aria-current="page" @endif>
                            <span class="grid size-7 place-items-center rounded-lg bg-white/5 text-xs">03</span>
                            Manajemen pengguna
                        </a>
                    @endif
                </nav>

                <div class="mt-auto rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 place-items-center rounded-full bg-[#d7a85f]/15 text-xs font-semibold text-[#edc781]">
                            {{ str($user->name)->substr(0, 1)->upper() }}
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium">{{ $user->name }}</span>
                            <span class="block text-xs text-white/55">{{ $isAdmin ? 'Administrator' : 'Warga' }}</span>
                        </span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-lg border border-white/10 px-3 py-2 text-left text-xs font-medium text-white/70 transition hover:bg-white/10 hover:text-white">
                            Keluar dari akun
                        </button>
                    </form>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur">
                    <div class="flex h-[4.5rem] items-center justify-between gap-4 px-5 sm:px-8 lg:px-10">
                        <div class="flex items-center gap-3">
                            <button type="button" data-sidebar-toggle aria-label="Buka menu navigasi" aria-controls="dashboard-sidebar" aria-expanded="false"
                                class="grid size-10 place-items-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 lg:hidden">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="size-5" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                            <div>
                                <p class="text-[11px] font-medium uppercase tracking-[0.16em] text-slate-600">{{ $isAdmin ? 'Administrasi' : 'Ruang warga' }}</p>
                                <p class="text-sm font-semibold text-brand-950">{{ $title }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <details class="relative">
                                <summary aria-label="{{ $notificationCenterAvailable ? 'Notifikasi, '.$unreadNotificationCount.' belum dibaca' : 'Pusat notifikasi belum tersedia' }}" aria-haspopup="true"
                                    class="relative grid size-10 cursor-pointer list-none place-items-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 [&::-webkit-details-marker]:hidden">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="size-5" stroke="currentColor" stroke-width="1.7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4.5-5.8V4a1.5 1.5 0 0 0-3 0v1.2A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9"/>
                                    </svg>
                                    @if ($unreadNotificationCount > 0)
                                        <span class="absolute -right-1 -top-1 min-w-5 rounded-full bg-rose-600 px-1 text-center text-[10px] font-bold leading-5 text-white" aria-hidden="true">
                                            {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                        </span>
                                    @endif
                                </summary>
                                <div aria-label="Notifikasi terbaru" class="absolute right-0 z-40 mt-3 w-[min(22rem,calc(100vw-2.5rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                        <h2 class="text-sm font-semibold text-brand-950">Notifikasi terbaru</h2>
                                        @if ($unreadNotificationCount > 0)
                                            <span class="text-xs font-medium text-brand-700">{{ $unreadNotificationCount }} belum dibaca</span>
                                        @endif
                                    </div>
                                    @if (! $notificationCenterAvailable)
                                        <p class="px-4 py-5 text-sm leading-6 text-amber-700">Pusat notifikasi akan tersedia setelah pembaruan database diterapkan.</p>
                                    @elseif ($recentNotifications->isEmpty())
                                        <p class="px-4 py-5 text-sm text-slate-600">Belum ada notifikasi.</p>
                                    @else
                                        <ol class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                                            @foreach ($recentNotifications as $notification)
                                                @php
                                                    $notificationReportId = $notification->data['laporan_id'] ?? null;
                                                    $notificationReportTitle = $notification->data['laporan_judul'] ?? 'Pembaruan laporan';
                                                @endphp
                                                <li class="flex items-start gap-3 px-4 py-3 {{ $notification->unread() ? 'bg-brand-50/50' : 'bg-white' }}">
                                                    <span class="mt-1.5 size-2 shrink-0 rounded-full bg-brand-700 {{ $notification->unread() ? '' : 'invisible' }}" aria-hidden="true"></span>
                                                    <div class="min-w-0 flex-1">
                                                        @if (is_numeric($notificationReportId))
                                                            <a href="{{ route($isAdmin ? 'admin.laporans.show' : 'warga.laporans.show', $notificationReportId) }}" class="block rounded-sm text-sm font-medium leading-5 text-brand-950 hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                                                                {{ $notification->data['message'] ?? $notificationReportTitle }}
                                                            </a>
                                                        @else
                                                            <p class="text-sm leading-5 text-slate-700">{{ $notification->data['message'] ?? $notificationReportTitle }}</p>
                                                        @endif
                                                        <p class="mt-1 text-xs text-slate-600">{{ $notification->created_at?->locale('id')->diffForHumans() }}</p>
                                                    </div>
                                                    @if ($notification->unread())
                                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="rounded-md px-2 py-1 text-[11px] font-medium text-brand-700 hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700" aria-label="Tandai notifikasi {{ $notificationReportTitle }} sudah dibaca">
                                                                Baca
                                                            </button>
                                                        </form>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ol>
                                    @endif
                                    @if ($notificationCenterAvailable)
                                        <a href="{{ route('notifications.index') }}" class="block border-t border-slate-100 px-4 py-3 text-center text-sm font-semibold text-brand-800 transition hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-brand-700">
                                            Lihat semua notifikasi
                                        </a>
                                    @endif
                                </div>
                            </details>
                            <div class="hidden items-center gap-3 sm:flex">
                                <span class="size-2 rounded-full bg-emerald-500"></span>
                                <span class="text-xs text-slate-600">Sistem aktif</span>
                                <span class="ml-1 grid size-9 place-items-center rounded-full bg-brand-100 text-xs font-semibold text-brand-800">
                                    {{ str($user->name)->substr(0, 1)->upper() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </header>

                <main id="main-content" tabindex="-1" class="mx-auto w-full max-w-[1440px] flex-1 px-5 py-8 outline-none sm:px-8 lg:px-10 lg:py-10">
                    @yield('content')
                </main>
                <footer class="border-t border-slate-200/80 px-5 py-4 text-center text-xs text-slate-600 sm:px-8 lg:px-10">
                    © {{ now()->year }} E-Report · Layanan pelaporan masyarakat
                </footer>
            </div>
        </div>
    </body>
</html>
