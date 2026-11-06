<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $resident = $this->route('user');

        return $resident instanceof User
            && ($this->user()?->can('update', $resident) ?? false);
    }

    public function rules(): array
    {
        $resident = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($resident instanceof User ? $resident->getKey() : null),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama warga wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.unique' => 'Alamat email sudah digunakan akun lain.',
        ];
    }
}
