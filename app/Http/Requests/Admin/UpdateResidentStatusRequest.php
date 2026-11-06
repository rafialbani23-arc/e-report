<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResidentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $resident = $this->route('user');

        return $resident instanceof User
            && ($this->user()?->can('updateStatus', $resident) ?? false);
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }
}
