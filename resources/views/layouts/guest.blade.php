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
    <body class="min-h-screen bg-[#f7f8f5] font-sans text-brand-950 antialiased">
        <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
            <section class="relative hidden overflow-hidden bg-brand-950 px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between xl:px-20">
                <div class="absolute -right-36 -top-32 size-[30rem] rounded-full border border-white/10"></div>
                <div class="absolute -right-12 -top-8 size-[21rem] rounded-full border border-white/10"></div>
                <div class="absolute -bottom-48 -left-36 size-[32rem] rounded-full bg-brand-800/50 blur-3xl"></div>

                <a href="{{ route('home') }}" class="relative z-10 inline-flex w-fit items-center gap-3">
                    <span class="grid size-11 place-items-center rounded-2xl bg-white text-sm font-bold tracking-tight text-brand-950">ER</span>
                    <span>
                        <span class="block text-base font-semibold tracking-wide">E-Report</span>
                        <span class="block text-xs text-white/65">Layanan pelaporan digital</span>
                    </span>
                </a>

                <div class="relative z-10 max-w-xl py-16">
                    <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-xs font-medium text-white/75">
                        <span class="size-1.5 rounded-full bg-[#d7a85f]"></span>
                        Pelaporan publik, lebih transparan
                    </p>
                    <h1 class="text-4xl font-semibold leading-[1.12] tracking-tight xl:text-5xl">
                        Suara Anda membantu lingkungan menjadi lebih baik.
                    </h1>
                    <p class="mt-6 max-w-lg text-base leading-7 text-white/65">
                        Sampaikan laporan dengan mudah, lalu pantau setiap perkembangannya dalam satu ruang yang aman dan tertata.
                    </p>

                    <div class="mt-12 grid max-w-lg grid-cols-2 gap-3">
                        <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                            <span class="text-xs text-white/65">Proses terarah</span>
                            <p class="mt-2 text-sm font-medium">Setiap laporan tercatat</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                            <span class="text-xs text-white/65">Akses personal</span>
                            <p class="mt-2 text-sm font-medium">Status mudah dipantau</p>
                        </div>
                    </div>
                </div>

                <p class="relative z-10 text-xs text-white/65">E-Report · Ruang aspirasi digital</p>
            </section>

            <section class="flex min-h-screen flex-col px-5 py-6 sm:px-10 lg:px-14 xl:px-24">
                <div class="mb-12 flex items-center justify-between lg:hidden">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-xl bg-brand-950 text-xs font-bold text-white">ER</span>
                        <span class="text-sm font-semibold">E-Report</span>
                    </a>
                    <span class="text-xs text-slate-600">Pelaporan digital</span>
                </div>

                <div class="my-auto w-full max-w-md self-center py-8">
                    @yield('content')
                </div>

                <footer class="pt-8 text-center text-xs text-slate-600">
                    © {{ now()->year }} E-Report. Ruang aman untuk menyampaikan aspirasi.
                </footer>
            </section>
        </main>
    </body>
</html>
