<?php

namespace App\Http\Requests;

use App\Models\Laporan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $laporan = $this->route('laporan');

        return $laporan instanceof Laporan && ($this->user()?->can('update', $laporan) ?? false);
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

    protected function failedAuthorization(): never
    {
        $laporan = $this->route('laporan');

        if ($laporan instanceof Laporan) {
            Gate::authorize('update', $laporan);
        }

        abort(403);
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
