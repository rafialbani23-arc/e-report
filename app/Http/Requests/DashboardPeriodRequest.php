<?php

namespace App\Http\Requests;

use App\Models\Laporan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Laporan::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'period' => ['nullable', 'string', Rule::in(['7d', '30d', '90d', '12m', 'all'])],
        ];
    }

    public function messages(): array
    {
        return [
            'period.in' => 'Periode analitik yang dipilih tidak valid.',
        ];
    }
}
