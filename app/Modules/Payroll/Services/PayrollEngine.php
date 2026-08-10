<?php

namespace App\Modules\Payroll\Services;

use App\Modules\Hr\Models\Employee;
use App\Modules\Payroll\Models\PayItem;
use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Payroll\Models\PayslipItem;
use App\Modules\Payroll\Models\SocialContribution;
use App\Modules\Payroll\Services\Exceptions\PayrollEngineException;
use Illuminate\Support\Facades\DB;

class PayrollEngine
{
    public function calculatePayRun(PayRun $payRun): void
    {
        if (!$payRun->isDraft() && !$payRun->isCalculated()) {
            throw new PayrollEngineException("La période de paie ne peut être calculée que si elle est au statut 'draft' ou 'calculated'.");
        }

        DB::transaction(function () use ($payRun) {
            $payRun->payslips()->delete(); // Recalcul propre
            $company = $payRun->company;
            
            $employees = Employee::where('company_id', $company->id)
                ->where('status', 'active')->get();

            foreach ($employees as $employee) {
                $this->calculatePayslip($payRun, $employee);
            }

            $payRun->status = 'calculated';
            $payRun->save();

            activity()->performedOn($payRun)->causedBy(auth()->user())->log('Calcul de la période de paie terminé');
        });
    }

    protected function calculatePayslip(PayRun $payRun, Employee $employee): Payslip
    {
        $payslip = Payslip::create([
            'company_id' => $employee->company_id, 'pay_run_id' => $payRun->id,
            'employee_id' => $employee->id, 'status' => 'calculated',
            'period_start' => $payRun->period_start, 'period_end' => $payRun->period_end,
        ]);

        // Trouver le contrat actif sur la période
        $contract = $employee->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $payRun->period_end)
            ->where(function ($query) use ($payRun) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $payRun->period_start);
            })->orderByDesc('start_date')->first();

        if (!$contract) return $payslip;

        $baseSalary = $contract->base_salary;
        $payslip->base_salary = $baseSalary;

        $totalEarnings = $baseSalary;
        $totalDeductions = 0;
        $totalEmployerContributions = 0;

        // 1. Calcul des rubriques dynamiques (Primes, indemnités, etc.)
        $payItems = PayItem::where(function ($q) use ($employee) {
                $q->whereNull('company_id')->orWhere('company_id', $employee->company_id);
            })->where('is_active', true)->orderBy('display_order')->get();

        foreach ($payItems as $item) {
            $rateObj = $item->getActiveRateForDate($payRun->period_start);
            if (!$rateObj) continue;

            $amount = 0;
            $baseAmount = ($item->base_type === 'base_salary') ? $baseSalary : $baseSalary;
            
            if ($rateObj->ceiling && $baseAmount > $rateObj->ceiling) $baseAmount = $rateObj->ceiling;

            if ($item->calculation_method === 'fixed' && $rateObj->fixed_amount !== null) {
                $amount = $rateObj->fixed_amount;
            } elseif (str_contains($item->calculation_method, 'percentage') && $rateObj->rate !== null) {
                $amount = $baseAmount * ($rateObj->rate / 100);
            }

            if ($amount > 0) {
                PayslipItem::create([
                    'payslip_id' => $payslip->id, 'pay_item_id' => $item->id, 'name' => $item->name,
                    'type' => $item->type, 'base_amount' => $baseAmount, 'rate' => $rateObj->rate,
                    'amount' => $amount, 'is_earning' => ($item->type === 'earning'),
                ]);
                ($item->type === 'earning') ? $totalEarnings += $amount : $totalDeductions += $amount;
            }
        }

        // 2. Calcul des cotisations sociales (CNPS Ivoirienne)
        $socialContributions = SocialContribution::where('is_active', true)->get();
        foreach ($socialContributions as $contrib) {
            $rateObj = $contrib->getActiveRateForDate($payRun->period_start);
            if (!$rateObj) continue;

            $cappedBase = $baseSalary;
            if ($rateObj->ceiling && $cappedBase > $rateObj->ceiling) $cappedBase = $rateObj->ceiling;

            if ($rateObj->employee_rate > 0) {
                $empAmount = $cappedBase * ($rateObj->employee_rate / 100);
                $deductionItem = PayItem::firstOrCreate(
                    ['code' => 'CNPS_EMP_' . $contrib->code, 'company_id' => $employee->company_id],
                    ['name' => $contrib->name . ' (Part Salariale)', 'type' => 'employee_contribution', 'display_order' => 90, 'is_active' => true]
                );
                PayslipItem::create([
                    'payslip_id' => $payslip->id, 'pay_item_id' => $deductionItem->id, 'name' => $deductionItem->name,
                    'type' => $deductionItem->type, 'base_amount' => $cappedBase, 'rate' => $rateObj->employee_rate,
                    'amount' => $empAmount, 'is_earning' => false, 'display_order' => 90,
                ]);
                $totalDeductions += $empAmount;
            }

            if ($rateObj->employer_rate > 0) {
                $totalEmployerContributions += ($cappedBase * ($rateObj->employer_rate / 100));
            }
        }

        // Totaux finaux
        $grossSalary = $totalEarnings;
        $netSalary = $grossSalary - $totalDeductions;

        $payslip->update([
            'gross_salary' => $grossSalary, 'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions, 'net_salary' => $netSalary,
            'employer_contributions' => $totalEmployerContributions,
        ]);

        return $payslip;
    }
}
