<?php

declare(strict_types=1);

namespace App\Http\Requests\Evaluation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvaluationRequest extends FormRequest
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
            'scores'               => ['required', 'array'],
            'scores.*.criteria_id' => ['required', 'integer', 'exists:criterias,id'],
            'scores.*.score'       => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'scores.required'               => 'Data nilai evaluasi wajib diisi.',
            'scores.*.criteria_id.exists'   => 'Kriteria yang dipilih tidak ditemukan dalam sistem.',
            'scores.*.score.required'       => 'Nilai tiap kriteria wajib diisi.',
            'scores.*.score.numeric'        => 'Nilai harus berupa angka.',
            'scores.*.score.min'            => 'Nilai tidak boleh kurang dari 0.',
        ];
    }
}
