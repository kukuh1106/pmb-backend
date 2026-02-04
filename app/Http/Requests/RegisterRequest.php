<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'no_whatsapp' => 'required|string|max:20',
            'prodi_id' => 'required|exists:prodi,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter',
            'no_whatsapp.required' => 'Nomor WhatsApp wajib diisi',
            'no_whatsapp.max' => 'Nomor WhatsApp maksimal 20 karakter',
            'prodi_id.required' => 'Program studi wajib dipilih',
            'prodi_id.exists' => 'Program studi tidak valid',
        ];
    }
}
