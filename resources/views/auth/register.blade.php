@extends('layouts.guest', ['title' => 'Buat akun'])

@section('content')
    <p class="text-sm font-medium text-brand-700">Mulai dari sini</p>
    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-brand-950">Buat akun E-Report</h2>
    <p class="mt-3 text-sm leading-6 text-slate-600">Daftar untuk menyampaikan laporan dan melihat perkembangannya.</p>

    <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-4">
        @csrf

        <div>
            <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Nama lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                @if ($errors->has('name')) aria-describedby="name-error" @endif
                class="@error('name') border-red-400 focus:border-red-500 focus:ring-red-100 @else border-slate-200 focus:border-brand-700 focus:ring-brand-100 @enderror w-full rounded-xl border bg-white px-4 py-3 text-sm text-brand-950 outline-none transition focus:ring-4"
                placeholder="Nama Anda">
            @error('name')
                <p id="name-error" class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Alamat email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                @if ($errors->has('email')) aria-describedby="email-error" @endif
                class="@error('email') border-red-400 focus:border-red-500 focus:ring-red-100 @else border-slate-200 focus:border-brand-700 focus:ring-brand-100 @enderror w-full rounded-xl border bg-white px-4 py-3 text-sm text-brand-950 outline-none transition focus:ring-4"
                placeholder="nama@email.com">
            @error('email')
                <p id="email-error" class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                @if ($errors->has('password')) aria-describedby="password-error" @endif
                class="@error('password') border-red-400 focus:border-red-500 focus:ring-red-100 @else border-slate-200 focus:border-brand-700 focus:ring-brand-100 @enderror w-full rounded-xl border bg-white px-4 py-3 text-sm text-brand-950 outline-none transition focus:ring-4"
                placeholder="Minimal 8 karakter">
            @error('password')
                <p id="password-error" class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">Konfirmasi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"
                @if ($errors->has('password_confirmation')) aria-describedby="password-confirmation-error" @elseif ($errors->has('password')) aria-describedby="password-error" @endif
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-brand-950 outline-none transition focus:border-brand-700 focus:ring-4 focus:ring-brand-100"
                placeholder="Ulangi kata sandi">
            @error('password_confirmation')
                <p id="password-confirmation-error" class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-brand-900 px-4 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 focus:outline-none focus:ring-4 focus:ring-brand-100">
            Buat akun
        </button>
    </form>

    <p class="mt-7 text-center text-sm text-slate-600">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="font-semibold text-brand-800 hover:text-brand-950">Masuk</a>
    </p>
@endsection
