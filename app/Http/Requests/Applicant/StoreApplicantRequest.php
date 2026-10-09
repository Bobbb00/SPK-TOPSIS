<?php

declare(strict_types=1);

namespace App\Http\Requests\Applicant;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nim' => ['required', 'string', 'max:20', 'unique:applicants,nim'],
            'name' => ['required', 'string', 'max:255'],
            'study_program' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nim.unique' => 'NIM mahasiswa sudah terdaftar pada sistem.',
            'nim.required' => 'Nomor Induk Mahasiswa (NIM) wajib diisi.',
            'name.required' => 'Nama lengkap mahasiswa wajib diisi.',
            'study_program.required' => 'Program studi wajib diisi.',
        ];
    }
}
