<?php

declare(strict_types=1);

namespace App\Http\Requests\Criteria;

use Illuminate\Foundation\Http\FormRequest;

class StoreCriteriaRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:20', 'unique:criterias,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:benefit,cost'],
            'weight' => ['required', 'numeric', 'min:0.01', 'max:1.00'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Kode kriteria sudah digunakan.',
            'type.in' => 'Sifat kriteria wajib berupa benefit atau cost.',
            'weight.min' => 'Bobot kriteria minimal 0.01.',
            'weight.max' => 'Bobot kriteria maksimal 1.00.',
        ];
    }
}
