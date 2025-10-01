<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HospitalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required','string','max:120'],
            'address'     => ['nullable','string','max:255'],
            'email'       => ['nullable','email','max:255'],
            'phone'       => ['nullable','string','max:20'],
            'description' => ['nullable','string'],
            'logo_path'   => ['nullable','string','max:255'],
            'icon_path'   => ['nullable','string','max:255'],
            'latitude'    => ['nullable','numeric','between:-90,90'],
            'longitude'   => ['nullable','numeric','between:-180,180'],
        ];
    }
}
