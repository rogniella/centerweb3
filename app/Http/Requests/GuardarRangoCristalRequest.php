<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GuardarRangoCristalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Cri_esf' => ['required', 'numeric'],
            'Cri_esf_neg' => ['required', 'numeric'],
            'Cri_cil' => ['required', 'numeric'],
            'Cri_cil_neg' => ['required', 'numeric'],
            'Cri_add' => ['nullable', 'numeric'],
            'Cri_rango' => ['nullable', 'integer'],
            'Cri_metodo' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'Cri_esf' => 'esfera',
            'Cri_esf_neg' => 'esfera negativa',
            'Cri_cil' => 'cilindro',
            'Cri_cil_neg' => 'cilindro negativo',
            'Cri_add' => 'adicción',
            'Cri_rango' => 'rango',
            'Cri_metodo' => 'método',
        ];
    }
}
