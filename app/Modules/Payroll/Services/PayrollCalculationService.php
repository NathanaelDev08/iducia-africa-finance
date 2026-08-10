<?php

namespace App\Modules\Payroll\Services;

use App\Modules\Hr\Models\Employee;
use App\Modules\Payroll\Models\PayItem;
use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Payroll\Models\PayslipLine;
use App\Modules\Payroll\Models\PayrollVariable;
use App\Modules\Payroll\Models\SocialContribution;
use Illuminate\Support\Facades\DB;

class PayrollCalculationService
{
    /**
     * Calcule le bulletin d'un employé pour une période de paie.
     */
    public function calculatePayslip(PayRun $payRun, Employee $employee): Payslip
    {
        return DB::transaction(function () use ($payRun, $employee) {
            // 1. Récupérer le salaire de base du contrat actif
            $contract = $employee->contracts()->where('status', 'active')->first();
            $baseSalary = $contract ? (float) $contract->base_salary : 0;

            $lines = [];
            $totalEarnings = $baseSalary;
            $totalDeductions = 0;

            // 2. Ligne salaire de base
            $salaryPayItem = PayItem::where('code', 'SALAIRE_BASE')
                ->where(function ($query) use ($payRun) {
                    $query->whereNull('company_id')
                        ->orWhere('company_id', $payRun->company_id);
                })
                ->first();

            if ($baseSalary > 0) {
                $lines[] = [
                    'pay_item_id' => $salaryPayItem?->id,
                    'code' => 'SALAIRE_BASE',
                    'label' => 'Salaire de base',
                    'type' => 'earning',
                    'base_amount' => $baseSalary,
                    'rate' => null,
                    'amount' => $baseSalary,
                    'employer_amount' => 0,
                    'display_order' => 0,
                ];
            }

            // 3. Récupérer les éléments variables de la période
            $variables = PayrollVariable::where('employee_id', $employee->id)
                ->where('effective_date', '>=', $payRun->period_start)
                ->where('effective_date', '<=', $payRun->period_end)
                ->with('payItem')
                ->get();

            foreach ($variables as $variable) {
                if (! $variable->payItem) {
                    continue;
                }

                if ($variable->payItem->type === 'earning') {
                    $totalEarnings += (float) $variable->amount;

                    $lines[] = [
                        'pay_item_id' => $variable->pay_item_id,
                        'code' => $variable->payItem->code,
                        'label' => $variable->payItem->name,
                        'type' => 'earning',
                        'base_amount' => $variable->amount,
                        'rate' => null,
                        'amount' => $variable->amount,
                        'employer_amount' => 0,
                        'display_order' => $variable->payItem->display_order,
                    ];
                }

                if ($variable->payItem->type === 'deduction') {
                    $totalDeductions += (float) $variable->amount;

                    $lines[] = [
                        'pay_item_id' => $variable->pay_item_id,
                        'code' => $variable->payItem->code,
                        'label' => $variable->payItem->name,
                        'type' => 'deduction',
                        'base_amount' => $variable->amount,
                        'rate' => null,
                        'amount' => $variable->amount,
                        'employer_amount' => 0,
                        'display_order' => $variable->payItem->display_order,
                    ];
                }
            }

            $grossSalary = $totalEarnings;

            // 4. Calculer les cotisations salariales et patronales
            $totalEmployeeContributions = 0;
            $totalEmployerContributions = 0;

            $contributions = SocialContribution::active()->get();

            foreach ($contributions as $contribution) {
                $rate = $contribution->getCurrentRate($payRun->period_end->toDateString());

                if (! $rate) {
                    continue;
                }

                // Base de cotisation avec plafonnement
                $contributionBase = $grossSalary;

                if ($rate->ceiling && $contributionBase > (float) $rate->ceiling) {
                    $contributionBase = (float) $rate->ceiling;
                }

                $employeeAmount = round($contributionBase * ((float) $rate->employee_rate / 100), 2);
                $employerAmount = round($contributionBase * ((float) $rate->employer_rate / 100), 2);

                $totalEmployeeContributions += $employeeAmount;
                $totalEmployerContributions += $employerAmount;

                if ($employeeAmount > 0) {
                    $lines[] = [
                        'pay_item_id' => null,
                        'code' => $contribution->code,
                        'label' => $contribution->name . ' (salarial)',
                        'type' => 'employee_contribution',
                        'base_amount' => $contributionBase,
                        'rate' => $rate->employee_rate,
                        'amount' => $employeeAmount,
                        'employer_amount' => 0,
                        'display_order' => 100,
                    ];
                }

                if ($employerAmount > 0) {
                    $lines[] = [
                        'pay_item_id' => null,
                        'code' => $contribution->code . '-PAT',
                        'label' => $contribution->name . ' (patronal)',
                        'type' => 'employer_contribution',
                        'base_amount' => $contributionBase,
                        'rate' => $rate->employer_rate,
                        'amount' => 0,
                        'employer_amount' => $employerAmount,
                        'display_order' => 101,
                    ];
                }
            }

            // 5. Calculer le net imposable
            $taxableIncome = $grossSalary - $totalEmployeeContributions;

            // 6. Calculer l'impôt sur salaire
            $incomeTax = $this->calculateIncomeTax($taxableIncome, $payRun->period_end->toDateString());

            // 7. Calculer le net à payer
            $netSalary = max(0, $taxableIncome - $incomeTax - $totalDeductions);

            // 8. Créer ou mettre à jour le bulletin
            $payslip = Payslip::updateOrCreate([
                'pay_run_id' => $payRun->id,
                'employee_id' => $employee->id,
            ], [
                'company_id' => $payRun->company_id,
                'base_salary' => $baseSalary,
                'gross_salary' => $grossSalary,
                'total_earnings' => $totalEarnings,
                'total_deductions' => $totalDeductions,
                'total_employee_contributions' => $totalEmployeeContributions,
                'total_employer_contributions' => $totalEmployerContributions,
                'taxable_income' => $taxableIncome,
                'income_tax' => $incomeTax,
                'net_salary' => $netSalary,
                'status' => 'calculated',
                'calculation_snapshot' => [
                    'calculated_at' => now()->toISOString(),
                    'base_salary' => $baseSalary,
                    'gross_salary' => $grossSalary,
                    'net_salary' => $netSalary,
                ],
            ]);

            // 9. Enregistrer les lignes du bulletin
            $payslip->lines()->delete();

            foreach ($lines as $line) {
                PayslipLine::create([
                    'payslip_id' => $payslip->id,
                    'pay_item_id' => $line['pay_item_id'],
                    'code' => $line['code'],
                    'label' => $line['label'],
                    'type' => $line['type'],
                    'base_amount' => $line['base_amount'],
                    'rate' => $line['rate'],
                    'amount' => $line['amount'],
                    'employer_amount' => $line['employer_amount'],
                    'display_order' => $line['display_order'],
                ]);
            }

            return $payslip;
        });
    }

    /**
     * Calcule l'impôt sur salaire.
     *
     * IMPORTANT : Le barème réel ivoirien doit être paramétré et validé
     * par un fiscaliste. Cette méthode utilise un calcul simplifié de démonstration.
     */
    protected function calculateIncomeTax(float $taxableIncome, string $date): float
    {
        $allowance = 150000;

        if ($taxableIncome <= $allowance) {
            return 0;
        }

        return round(($taxableIncome - $allowance) * 0.05, 2);
    }

    /**
     * Calcule tous les bulletins d'une période de paie.
     */
    public function calculatePayRun(PayRun $payRun): array
    {
        $employees = Employee::where('company_id', $payRun->company_id)
            ->where('status', 'active')
            ->get();

        $payslips = [];

        foreach ($employees as $employee) {
            $payslips[] = $this->calculatePayslip($payRun, $employee);
        }

        $payRun->update(['status' => 'calculated']);

        return $payslips;
    }
}
