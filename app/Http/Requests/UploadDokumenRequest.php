<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDokumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|max:5120', // 5MB max
            'jenis_dokumen' => 'required|in:ijazah,transkrip,ktp,pas_foto,surat_rekomendasi,proposal,lainnya',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File wajib diupload',
            'file.file' => 'Upload harus berupa file',
            'file.max' => 'Ukuran file maksimal 5MB',
            'jenis_dokumen.required' => 'Jenis dokumen wajib dipilih',
            'jenis_dokumen.in' => 'Jenis dokumen tidak valid',
        ];
    }
}
