<?php

namespace App\Http\Requests;

use App\Models\Laporan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLaporanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $laporan = $this->route('laporan');

        return $laporan instanceof Laporan && ($this->user()?->can('updateStatus', $laporan) ?? false);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(array_keys(Laporan::STATUSES))],
            'tanggapan_admin' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status laporan wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak didukung.',
            'tanggapan_admin.max' => 'Tanggapan admin maksimal 10.000 karakter.',
        ];
    }
}
