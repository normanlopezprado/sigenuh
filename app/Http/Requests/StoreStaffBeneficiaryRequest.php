<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffBeneficiaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajusta si usas políticas/roles
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required','string','max:150'],
            'job_title' => ['nullable','string','max:120'],
            'status'    => ['required','boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'El nombre es obligatorio.',
        ];
    }
}
