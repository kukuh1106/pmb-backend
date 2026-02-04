<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifikasiDokumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dokumen_id' => 'required|exists:dokumen,id',
            'status' => 'required|in:valid,tidak_valid',
            'catatan' => 'required_if:status,tidak_valid|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'dokumen_id.required' => 'ID Dokumen wajib diisi',
            'dokumen_id.exists' => 'Dokumen tidak ditemukan',
            'status.required' => 'Status verifikasi wajib dipilih',
            'status.in' => 'Status harus valid atau tidak_valid',
            'catatan.required_if' => 'Catatan wajib diisi jika dokumen tidak valid',
            'catatan.max' => 'Catatan maksimal 500 karakter',
        ];
    }
}
