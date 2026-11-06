<?php

namespace App\Http\Requests;

use App\Models\Laporan;
use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Laporan::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:10000'],
            'tanggal' => ['required', 'date'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul laporan wajib diisi.',
            'deskripsi.required' => 'Deskripsi laporan wajib diisi.',
            'tanggal.required' => 'Tanggal kejadian wajib diisi.',
            'foto.image' => 'Bukti laporan harus berupa gambar.',
            'foto.mimes' => 'Format bukti hanya boleh JPG, PNG, atau WEBP.',
            'foto.extensions' => 'Ekstensi bukti hanya boleh JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran bukti maksimal 5 MB.',
        ];
    }
}
