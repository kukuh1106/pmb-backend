<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBiodataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'sometimes|string|max:255',
            'tanggal_lahir' => 'sometimes|date',
            'tempat_lahir' => 'sometimes|string|max:100',
            'jenis_kelamin' => 'sometimes|in:L,P',
            'alamat' => 'sometimes|string|max:500',
            'pendidikan_terakhir' => 'sometimes|string|max:100',
            'asal_institusi' => 'sometimes|string|max:255',
            'no_whatsapp' => 'sometimes|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
            'tempat_lahir.max' => 'Tempat lahir maksimal 100 karakter',
            'jenis_kelamin.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan)',
            'alamat.max' => 'Alamat maksimal 500 karakter',
            'pendidikan_terakhir.max' => 'Pendidikan terakhir maksimal 100 karakter',
            'asal_institusi.max' => 'Asal institusi maksimal 255 karakter',
            'no_whatsapp.max' => 'Nomor WhatsApp maksimal 20 karakter',
        ];
    }
}
