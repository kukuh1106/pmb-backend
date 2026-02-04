<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InputNilaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nilai_ujian' => 'required|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'nilai_ujian.required' => 'Nilai ujian wajib diisi',
            'nilai_ujian.numeric' => 'Nilai ujian harus berupa angka',
            'nilai_ujian.min' => 'Nilai ujian minimal 0',
            'nilai_ujian.max' => 'Nilai ujian maksimal 100',
        ];
    }
}
