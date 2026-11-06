<section aria-labelledby="dashboard-activity-title" class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/30">
    <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
        <div>
            <h2 id="dashboard-activity-title" class="text-base font-semibold text-brand-950">{{ $isAdmin ? 'Aktivitas perubahan status' : 'Pembaruan laporan terbaru' }}</h2>
            <p class="mt-1 text-xs text-slate-600">{{ $isAdmin ? 'Perubahan yang tercatat pada periode '.$periodLabel.'.' : 'Perubahan status pada laporan milik Anda.' }}</p>
        </div>
        @if ($isAdmin)
            <span class="rounded-lg bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-600">6 terbaru</span>
        @else
            <a href="{{ route('notifications.index') }}" class="rounded-lg px-2 py-1.5 text-xs font-semibold text-brand-800 transition hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">Notifikasi</a>
        @endif
    </div>

    @if ($activities->isEmpty())
        <div class="px-5 py-9 text-center sm:px-6" role="status">
            <p class="text-sm font-medium text-slate-700">Belum ada perubahan status</p>
            <p class="mt-1 text-xs leading-5 text-slate-600">
                {{ $isAdmin ? 'Aktivitas perubahan status pada periode ini akan tercatat di sini.' : 'Pembaruan dari admin terkait laporan Anda akan muncul di sini.' }}
            </p>
        </div>
    @else
        <ol class="divide-y divide-slate-100">
            @foreach ($activities as $activity)
                @php
                    $activityReportRoute = $isAdmin ? 'admin.laporans.show' : 'warga.laporans.show';
                @endphp
                <li class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="min-w-0">
                        <a href="{{ route($activityReportRoute, $activity->laporan) }}" class="font-medium text-brand-900 hover:text-brand-700 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                            {{ $activity->laporan->judul }}
                        </a>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ \App\Models\Laporan::STATUSES[$activity->previous_status] ?? ucfirst($activity->previous_status) }}
                            <span aria-label="menjadi" class="px-1 text-slate-600">→</span>
                            {{ \App\Models\Laporan::STATUSES[$activity->new_status] ?? ucfirst($activity->new_status) }}
                            @if ($isAdmin)
                                <span class="text-slate-600">· oleh {{ $activity->admin->name }}</span>
                            @endif
                        </p>
                    </div>
                    <time datetime="{{ $activity->created_at?->toAtomString() }}" class="shrink-0 text-xs text-slate-600">
                        {{ $activity->created_at?->locale('id')->diffForHumans() }}
                    </time>
                </li>
            @endforeach
        </ol>
    @endif
</section>
