<?php

declare(strict_types=1);

namespace App\Http\Requests\ScholarshipQuota;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScholarshipQuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'quota_limit' => ['required', 'integer', 'min:1', 'max:1000'],
            'period' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quota_limit.required' => 'Batas kuota penerima beasiswa wajib diisi.',
            'quota_limit.integer'  => 'Batas kuota penerima harus berupa angka bulat positif.',
            'quota_limit.min'      => 'Batas kuota penerima minimal 1 orang.',
            'period.required'      => 'Nama periode beasiswa wajib diisi.',
            'period.max'           => 'Nama periode maksimal 100 karakter.',
        ];
    }
}
