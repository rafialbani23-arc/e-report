@extends('layouts.dashboard', [
    'title' => $laporan->exists ? 'Edit laporan' : 'Buat laporan',
    'user' => auth()->user(),
    'isAdmin' => $isAdmin ?? false,
])

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('warga.laporans.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-brand-800">
            <span aria-hidden="true">←</span>
            Kembali ke laporan saya
        </a>
        <div class="mt-6">
            <p class="text-sm font-medium text-brand-700">{{ $laporan->exists ? 'Perbarui informasi' : 'Sampaikan aspirasi Anda' }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-brand-950 sm:text-3xl">{{ $laporan->exists ? 'Edit laporan' : 'Buat laporan baru' }}</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Jelaskan kejadian secara jelas agar laporan dapat ditinjau dengan baik.</p>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-semibold">Periksa kembali isian berikut.</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $laporan->exists ? route('warga.laporans.update', $laporan) : route('warga.laporans.store') }}" enctype="multipart/form-data" class="mt-7 space-y-5 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">
            @csrf
            @if ($laporan->exists)
                @method('PUT')
            @endif

            <div>
                <label for="judul" class="mb-2 block text-sm font-medium text-slate-700">Judul laporan <span class="text-red-500">*</span></label>
                <input id="judul" name="judul" type="text" value="{{ old('judul', $laporan->judul) }}" maxlength="255" required
                    aria-invalid="{{ $errors->has('judul') ? 'true' : 'false' }}"
                    @if ($errors->has('judul')) aria-describedby="judul-help judul-error" @else aria-describedby="judul-help" @endif
                    class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-600 focus:border-brand-700 focus:ring-4 focus:ring-brand-100 @error('judul') border-red-400 @enderror"
                    placeholder="Contoh: Lampu jalan padam di Jalan Melati">
                <p id="judul-help" class="mt-1.5 text-xs text-slate-600">Tulis ringkasan yang mudah dikenali.</p>
                @error('judul')<p id="judul-error" class="mt-1.5 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="deskripsi" class="mb-2 block text-sm font-medium text-slate-700">Deskripsi kejadian <span class="text-red-500">*</span></label>
                <textarea id="deskripsi" name="deskripsi" rows="6" maxlength="10000" required
                    aria-invalid="{{ $errors->has('deskripsi') ? 'true' : 'false' }}"
                    @if ($errors->has('deskripsi')) aria-describedby="deskripsi-error" @endif
                    class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-slate-600 focus:border-brand-700 focus:ring-4 focus:ring-brand-100 @error('deskripsi') border-red-400 @enderror"
                    placeholder="Jelaskan lokasi, waktu, dan hal yang perlu diketahui petugas.">{{ old('deskripsi', $laporan->deskripsi) }}</textarea>
                @error('deskripsi')<p id="deskripsi-error" class="mt-1.5 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="tanggal" class="mb-2 block text-sm font-medium text-slate-700">Tanggal kejadian <span class="text-red-500">*</span></label>
                    <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', $laporan->tanggal?->format('Y-m-d')) }}" required
                        aria-invalid="{{ $errors->has('tanggal') ? 'true' : 'false' }}"
                        @if ($errors->has('tanggal')) aria-describedby="tanggal-error" @endif
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-700 focus:ring-4 focus:ring-brand-100 @error('tanggal') border-red-400 @enderror">
                    @error('tanggal')<p id="tanggal-error" class="mt-1.5 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="foto" class="mb-2 block text-sm font-medium text-slate-700">Foto bukti <span class="font-normal text-slate-600">(opsional)</span></label>
                    <input id="foto" name="foto" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        aria-invalid="{{ $errors->has('foto') ? 'true' : 'false' }}"
                        @if ($errors->has('foto')) aria-describedby="foto-help foto-error" @else aria-describedby="foto-help" @endif
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-800">
                    <p id="foto-help" class="mt-1.5 text-xs text-slate-600">JPG, PNG, atau WEBP · maksimal 5 MB.</p>
                    @error('foto')<p id="foto-error" class="mt-1.5 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                    @if ($laporan->foto)
                        <a href="{{ route('laporans.foto', $laporan) }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-medium text-brand-700 hover:text-brand-950">Lihat foto saat ini</a>
                    @endif
                </div>
            </div>

            <div class="flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-5 sm:flex-row">
                <a href="{{ route('warga.laporans.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-center text-sm font-medium text-slate-600 transition hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-xl bg-brand-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800">{{ $laporan->exists ? 'Simpan perubahan' : 'Kirim laporan' }}</button>
            </div>
        </form>
    </div>
@endsection
