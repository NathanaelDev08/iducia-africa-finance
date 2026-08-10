<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixPayslipItemsSeeder extends Seeder
{
    public function run(): void
    {
        $fixed = 0;

        foreach (Company::all() as $company) {
            $payslips = DB::table('payslips')->where('company_id', $company->id)->get();

            foreach ($payslips as $ps) {
                // Numéro de bulletin si absent
                if (empty($ps->slip_number)) {
                    DB::table('payslips')->where('id', $ps->id)->update([
                        'slip_number' => 'BUL-' . now()->format('Ym') . '-' . str_pad((string) $ps->id, 3, '0', STR_PAD_LEFT),
                    ]);
                }

                $hasItems = DB::table('payslip_items')->where('payslip_id', $ps->id)->count() > 0;
                if ($hasItems) continue;

                // Salaire de base
                $base = (float) $ps->base_salary;
                if ($base <= 0) {
                    $contract = DB::table('employee_contracts')->where('employee_id', $ps->employee_id)->first();
                    $base = (float) ($contract->base_salary ?? 450000);
                }

                // Calculs
                $anc = round($base * 0.05);
                $trans = 60000;
                $gross = $base + $anc + $trans;
                $cnpsBase = min($gross, 1200000);
                $cnpsSal = round($cnpsBase * 0.0425);
                $taxable = max(0, $gross - $cnpsSal);
                $ir = round($taxable * 0.05);
                $cnpsPat = round($cnpsBase * 0.077);
                $at = round($cnpsBase * 0.02);
                $pf = round($gross * 0.0575);
                $deductions = $cnpsSal + $ir;
                $net = $gross - $deductions;
                $employer = $cnpsPat + $at + $pf;

                // Mise à jour des totaux du bulletin
                DB::table('payslips')->where('id', $ps->id)->update([
                    'base_salary' => $base,
                    'gross_salary' => $gross,
                    'total_earnings' => $gross,
                    'total_deductions' => $deductions,
                    'total_employee_contributions' => $cnpsSal,
                    'total_employer_contributions' => $employer,
                    'employer_contributions' => $employer,
                    'taxable_income' => $taxable,
                    'income_tax' => $ir,
                    'net_salary' => $net,
                    'updated_at' => now(),
                ]);

                // Insertion des rubriques
                $items = [
                    ['name' => 'Salaire de base', 'type' => 'earning', 'base_amount' => $base, 'rate' => 100, 'amount' => $base, 'is_earning' => true, 'display_order' => 1],
                    ['name' => "Prime d'ancienneté (5%)", 'type' => 'earning', 'base_amount' => $base, 'rate' => 5, 'amount' => $anc, 'is_earning' => true, 'display_order' => 2],
                    ['name' => 'Prime de transport', 'type' => 'earning', 'base_amount' => $trans, 'rate' => 0, 'amount' => $trans, 'is_earning' => true, 'display_order' => 3],
                    ['name' => 'CNPS Retraite (part salariale)', 'type' => 'employee_contribution', 'base_amount' => $cnpsBase, 'rate' => 4.25, 'amount' => $cnpsSal, 'is_earning' => false, 'display_order' => 10],
                    ['name' => 'Impôt sur salaire (IR)', 'type' => 'deduction', 'base_amount' => $taxable, 'rate' => 5, 'amount' => $ir, 'is_earning' => false, 'display_order' => 11],
                    ['name' => 'CNPS Retraite (part patronale)', 'type' => 'employer_contribution', 'base_amount' => $cnpsBase, 'rate' => 7.7, 'amount' => $cnpsPat, 'is_earning' => false, 'display_order' => 20],
                    ['name' => 'Accidents du travail', 'type' => 'employer_contribution', 'base_amount' => $cnpsBase, 'rate' => 2, 'amount' => $at, 'is_earning' => false, 'display_order' => 21],
                    ['name' => 'Prestations familiales', 'type' => 'employer_contribution', 'base_amount' => $gross, 'rate' => 5.75, 'amount' => $pf, 'is_earning' => false, 'display_order' => 22],
                ];

                foreach ($items as $item) {
                    try {
                        DB::table('payslip_items')->insert(array_merge($item, [
                            'payslip_id' => $ps->id, 'created_at' => now(), 'updated_at' => now(),
                        ]));
                    } catch (\Throwable $e) {}
                }

                $fixed++;
            }
        }

        echo "✓ {$fixed} bulletin(s) réparés (rubriques + totaux + numéros)\n";
    }
}
