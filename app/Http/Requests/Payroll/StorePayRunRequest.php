<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class StorePayRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Modules\Payroll\Models\PayRun::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:50'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'payment_date' => ['nullable', 'date', 'after_or_equal:period_end'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la période de paie est obligatoire.',
            'period_start.required' => 'La date de début de période est obligatoire.',
            'period_end.required' => 'La date de fin de période est obligatoire.',
            'period_end.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ];
    }
}
