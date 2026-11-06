@extends('layouts.guest', ['title' => 'Masuk'])

@section('content')
    <p class="text-sm font-medium text-brand-700">Selamat datang kembali</p>
    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-brand-950">Masuk ke akun Anda</h2>
    <p class="mt-3 text-sm leading-6 text-slate-600">Gunakan akun Anda untuk melanjutkan dan memantau laporan.</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-9 space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Alamat email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                @if ($errors->has('email')) aria-describedby="email-error" @endif
                class="@error('email') border-red-400 focus:border-red-500 focus:ring-red-100 @else border-slate-200 focus:border-brand-700 focus:ring-brand-100 @enderror w-full rounded-xl border bg-white px-4 py-3 text-sm text-brand-950 outline-none transition focus:ring-4"
                placeholder="nama@email.com">
            @error('email')
                <p id="email-error" class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between gap-4">
                <label for="password" class="block text-sm font-medium text-slate-700">Kata sandi</label>
                <span class="text-xs text-slate-600">Minimal 8 karakter</span>
            </div>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                @if ($errors->has('password')) aria-describedby="password-error" @endif
                class="@error('password') border-red-400 focus:border-red-500 focus:ring-red-100 @else border-slate-200 focus:border-brand-700 focus:ring-brand-100 @enderror w-full rounded-xl border bg-white px-4 py-3 text-sm text-brand-950 outline-none transition focus:ring-4"
                placeholder="Masukkan kata sandi">
            @error('password')
                <p id="password-error" class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2.5 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 accent-brand-800">
            Ingat saya di perangkat ini
        </label>

        <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-brand-900 px-4 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 focus:outline-none focus:ring-4 focus:ring-brand-100">
            Masuk
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-600">
        Belum memiliki akun?
        <a href="{{ route('register') }}" class="font-semibold text-brand-800 hover:text-brand-950">Buat akun</a>
    </p>
@endsection
