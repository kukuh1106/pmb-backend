<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_kelulusan' => 'required|in:lulus,tidak_lulus,belum_diproses',
        ];
    }

    public function messages(): array
    {
        return [
            'status_kelulusan.required' => 'Status kelulusan wajib dipilih',
            'status_kelulusan.in' => 'Status harus lulus, tidak_lulus, atau belum_diproses',
        ];
    }
}
