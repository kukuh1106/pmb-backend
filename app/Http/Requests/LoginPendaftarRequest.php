<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginPendaftarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_pendaftaran' => 'required|string',
            'kode_akses' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_pendaftaran.required' => 'Nomor pendaftaran wajib diisi',
            'kode_akses.required' => 'Kode akses wajib diisi',
        ];
    }
}
