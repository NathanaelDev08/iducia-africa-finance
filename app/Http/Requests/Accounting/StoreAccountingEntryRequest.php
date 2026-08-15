<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountingEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Modules\Accounting\Models\AccountingEntry::class);
    }

    public function rules(): array
    {
        return [
            'journal_id' => ['required', 'exists:journals,id'],
            'entry_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:500'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:accounts,id'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'lines.required' => 'Au moins deux lignes sont requises pour une écriture équilibrée.',
            'lines.min' => 'Au moins deux lignes sont requises pour une écriture équilibrée.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);
            $totalDebit = collect($lines)->sum('debit');
            $totalCredit = collect($lines)->sum('credit');

            if (abs($totalDebit - $totalCredit) >= 0.01) {
                $validator->errors()->add('lines', "L'écriture doit être équilibrée : Débit ({$totalDebit}) ≠ Crédit ({$totalCredit})");
            }

            // Vérifier qu'aucune ligne n'a à la fois un débit et un crédit > 0
            foreach ($lines as $index => $line) {
                if ((float) $line['debit'] > 0 && (float) $line['credit'] > 0) {
                    $validator->errors()->add("lines.{$index}.debit", 'Une ligne ne peut pas avoir simultanément un débit et un crédit.');
                }
                if ((float) $line['debit'] == 0 && (float) $line['credit'] == 0) {
                    $validator->errors()->add("lines.{$index}.debit", 'Chaque ligne doit avoir soit un débit, soit un crédit.');
                }
            }
        });
    }
}
