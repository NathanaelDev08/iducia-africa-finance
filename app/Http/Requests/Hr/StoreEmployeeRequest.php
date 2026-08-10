<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Modules\Hr\Models\Employee::class);
    }

    public function rules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'matricule' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'birth_place' => ['nullable', 'string', 'max:150'],
            'sex' => ['nullable', Rule::in(['M', 'F', 'Autre'])],
            'nationality' => ['nullable', 'string', 'max:100'],
            'id_card_number' => ['nullable', 'string', 'max:100'],
            'cnps_number' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'marital_status' => ['nullable', Rule::in(['celibataire', 'marie', 'divorce', 'veuf'])],
            'dependents_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'hire_date' => ['required', 'date'],
            'seniority_date' => ['nullable', 'date'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'superior_id' => ['nullable', 'exists:employees,id'],
            'professional_category' => ['nullable', 'string', 'max:100'],
            'collective_agreement' => ['nullable', 'string', 'max:150'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'bank_account' => ['nullable', 'string', 'max:100'],
            'mobile_money' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', Rule::in(['bank', 'mobile_money', 'cash'])],

            // Contrat (optionnel mais recommandé)
            'contract.contract_type_id' => ['required_with:contract.start_date', 'exists:contract_types,id'],
            'contract.start_date' => ['required_with:contract.contract_type_id', 'date'],
            'contract.end_date' => ['nullable', 'date', 'after_or_equal:contract.start_date'],
            'contract.trial_period_end_date' => ['nullable', 'date'],
            'contract.working_hours_per_week' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'contract.base_salary' => ['required_with:contract.contract_type_id', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'last_name.required' => 'Le nom est obligatoire.',
            'first_name.required' => 'Le prénom est obligatoire.',
            'hire_date.required' => "La date d'embauche est obligatoire.",
            'contract.base_salary.required_with' => 'Le salaire de base est obligatoire si un contrat est renseigné.',
        ];
    }
}
