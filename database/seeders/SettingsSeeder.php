<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Modules\Settings\Models\SequenceNumber;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Models\Tax;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Company::all() as $company) {
            // Taxes par défaut
            $taxes = [
                ['code' => 'TVA', 'name' => 'TVA 18%', 'type' => 'vat', 'rate' => 18, 'account_number' => '4431', 'is_default' => true, 'description' => 'Taxe sur la valeur ajoutée'],
                ['code' => 'AIRSI', 'name' => 'AIRSI', 'type' => 'withholding', 'rate' => 7.5, 'account_number' => '4425', 'is_default' => false, 'description' => 'Acompte IR sur revenus salariaux'],
                ['code' => 'PATENTE', 'name' => 'Patente', 'type' => 'other', 'rate' => 0, 'account_number' => '6411', 'is_default' => false, 'description' => 'Contribution des patentes'],
            ];
            foreach ($taxes as $t) {
                Tax::updateOrCreate(
                    ['company_id' => $company->id, 'code' => $t['code']],
                    $t + ['effective_from' => now()->startOfYear()->toDateString()]
                );
            }

            // Séquences de numérotation
            $sequences = [
                ['code' => 'invoice', 'name' => 'Factures clients', 'prefix' => 'FAC', 'format' => '{prefix}-{year}-{number:04}'],
                ['code' => 'employee', 'name' => 'Matricules employés', 'prefix' => 'EMP', 'format' => '{prefix}-{number:04}'],
                ['code' => 'payslip', 'name' => 'Bulletins de paie', 'prefix' => 'BUL', 'format' => '{prefix}-{year}-{number:05}'],
                ['code' => 'journal_entry', 'name' => 'Écritures comptables', 'prefix' => 'ECR', 'format' => '{prefix}-{year}-{number:06}'],
            ];
            foreach ($sequences as $s) {
                SequenceNumber::updateOrCreate(
                    ['company_id' => $company->id, 'code' => $s['code']],
                    $s
                );
            }

            // Paramètres généraux
            $settings = [
                'language' => 'fr',
                'timezone' => 'Africa/Abidjan',
                'invoice_payment_days' => '30',
            ];
            foreach ($settings as $k => $v) {
                Setting::updateOrCreate(
                    ['company_id' => $company->id, 'key' => $k],
                    ['value' => $v, 'group' => 'general']
                );
            }

            $this->command->info('✓ Paramètres initialisés pour : ' . $company->name);
        }
    }
}
