<?php

namespace App\Console\Commands;

use App\Mail\LeaveBalanceThresholdMail;
use App\Models\Company;
use App\Models\Setting;
use App\Modules\Hr\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckLeaveBalances extends Command
{
    protected $signature = 'hr:check-leave-balances';
    protected $description = "Vérifie le solde de congés acquis des employés actifs et alerte l'employé + les RH au-delà du seuil";

    public function handle(): int
    {
        $threshold = Employee::LEAVE_BALANCE_ALERT_THRESHOLD;
        $alerted = 0;
        $reset = 0;

        Company::query()->orderBy('id')->each(function (Company $company) use ($threshold, &$alerted, &$reset) {
            $recipients = null; // résolu paresseusement, une seule fois par entreprise si besoin

            Employee::where('company_id', $company->id)
                ->active()
                ->whereNotNull('hire_date')
                ->each(function (Employee $employee) use ($company, $threshold, &$alerted, &$reset, &$recipients) {
                    $balance = $employee->accruedLeaveBalance();

                    if ($balance >= $threshold) {
                        if (! $employee->leave_alert_sent_at) {
                            if ($recipients === null) {
                                $recipients = $this->resolveHrRecipients($company);
                            }
                            $this->notifyThreshold($employee, $balance, $recipients);
                            $employee->forceFill(['leave_alert_sent_at' => now()])->save();
                            $alerted++;
                        }
                    } elseif ($employee->leave_alert_sent_at) {
                        $employee->forceFill(['leave_alert_sent_at' => null])->save();
                        $reset++;
                    }
                });
        });

        $this->info("{$alerted} alerte(s) de solde de congés envoyée(s), {$reset} réinitialisée(s).");

        return 0;
    }

    private function notifyThreshold(Employee $employee, float $balance, array $recipients): void
    {
        if ($employee->email) {
            try {
                Mail::to($employee->email)->send(new LeaveBalanceThresholdMail($employee, $balance, false));
            } catch (\Exception $e) {
                Log::error('Email solde congés (employé) échoué: ' . $e->getMessage());
            }
        }

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new LeaveBalanceThresholdMail($employee, $balance, true));
            } catch (\Exception $e) {
                Log::error('Email solde congés (RH) échoué: ' . $e->getMessage());
            }
        }
    }

    /**
     * Résout les destinataires RH : email dédié dans les paramètres de l'entreprise ('hr_email'),
     * sinon tous les utilisateurs ayant un rôle admin/RH pour cette entreprise.
     */
    private function resolveHrRecipients(Company $company): array
    {
        $hrEmailSetting = Setting::where('company_id', $company->id)->where('key', 'hr_email')->first();
        if ($hrEmailSetting && ! empty($hrEmailSetting->value)) {
            return array_values(array_filter(array_map('trim', explode(',', $hrEmailSetting->value))));
        }

        return $company->users()
            ->wherePivotIn('role', ['admin', 'admin-company', 'hr-manager'])
            ->get()
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
