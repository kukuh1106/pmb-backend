<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PilihJadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jadwal_ujian_id' => 'required|exists:jadwal_ujian,id',
        ];
    }

    public function messages(): array
    {
        return [
            'jadwal_ujian_id.required' => 'Jadwal ujian wajib dipilih',
            'jadwal_ujian_id.exists' => 'Jadwal ujian tidak valid',
        ];
    }
}
