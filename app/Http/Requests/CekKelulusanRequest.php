<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CekKelulusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_pendaftaran' => 'required|string',
            'tanggal_lahir' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_pendaftaran.required' => 'Nomor pendaftaran wajib diisi',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
        ];
    }
}
