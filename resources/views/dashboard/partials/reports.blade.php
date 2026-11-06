@if ($reports->isEmpty())
    <div class="flex flex-col items-center px-6 py-12 text-center" role="status">
        <span aria-hidden="true" class="grid size-12 place-items-center rounded-2xl bg-brand-50 text-lg text-brand-700">—</span>
        <h3 class="mt-4 text-sm font-semibold text-brand-950">Belum ada laporan</h3>
        <p class="mt-1 max-w-xs text-sm leading-6 text-slate-600">
            {{ $isAdmin ? 'Laporan pada periode ini akan muncul di sini.' : 'Laporan yang Anda kirim akan muncul di sini.' }}
        </p>
        @unless ($isAdmin)
            <a href="{{ route('warga.laporans.create') }}" class="mt-4 inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-brand-800 hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                Buat laporan pertama
            </a>
        @endunless
    </div>
@else
    <div class="overflow-x-auto">
        <table class="w-full min-w-[560px] text-left">
            <thead class="bg-slate-50/70 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                <tr>
                    <th scope="col" class="px-5 py-3.5 sm:px-6">Laporan</th>
                    @if ($isAdmin)
                        <th scope="col" class="px-4 py-3.5">Pelapor</th>
                    @endif
                    <th scope="col" class="px-4 py-3.5">Dikirim</th>
                    <th scope="col" class="px-5 py-3.5 text-right sm:px-6">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($reports as $report)
                    @php
                        $statusClass = match ($report->status) {
                            'pending' => 'bg-amber-50 text-amber-700',
                            'approved' => 'bg-sky-50 text-sky-700',
                            'rejected' => 'bg-rose-50 text-rose-700',
                            default => 'bg-slate-100 text-slate-600',
                        };
                        $reportRoute = $isAdmin ? 'admin.laporans.show' : 'warga.laporans.show';
                    @endphp
                    <tr class="transition hover:bg-slate-50/60">
                        <td class="px-5 py-4 sm:px-6">
                            <a href="{{ route($reportRoute, $report) }}" class="font-medium text-brand-900 hover:text-brand-700 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                                {{ $report->judul }}
                            </a>
                            <p class="mt-1 text-xs text-slate-600">#{{ str_pad((string) $report->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </td>
                        @if ($isAdmin)
                            <td class="px-4 py-4 text-sm text-slate-600">{{ $report->pelapor->name }}</td>
                        @endif
                        <td class="px-4 py-4 text-sm text-slate-600">{{ $report->created_at?->locale('id')->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-4 text-right sm:px-6">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-medium {{ $statusClass }}">
                                {{ \App\Models\Laporan::STATUSES[$report->status] ?? ucfirst($report->status) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
