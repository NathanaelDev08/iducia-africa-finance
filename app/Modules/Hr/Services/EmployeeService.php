<?php

namespace App\Modules\Hr\Services;

use App\Models\Company;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeeContract;
use App\Modules\Hr\Models\EmployeeDocument;
use App\Modules\Hr\Services\Exceptions\DuplicateMatriculeException;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function createEmployee(Company $company, array $data): Employee
    {
        $matricule = $data['matricule'] ?? null;

        if ($matricule) {
            $exists = Employee::where('company_id', $company->id)
                ->where('matricule', $matricule)
                ->exists();

            if ($exists) {
                throw new DuplicateMatriculeException($matricule);
            }
        } else {
            $matricule = $this->generateMatricule($company);
        }

        return DB::transaction(function () use ($company, $data, $matricule) {
            $employee = Employee::create([
                'company_id' => $company->id,
                'user_id' => $data['user_id'] ?? null,
                'matricule' => $matricule,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'birth_date' => $data['birth_date'] ?? null,
                'birth_place' => $data['birth_place'] ?? null,
                'sex' => $data['sex'] ?? null,
                'nationality' => $data['nationality'] ?? null,
                'id_card_number' => $data['id_card_number'] ?? null,
                'cnps_number' => $data['cnps_number'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'dependents_count' => $data['dependents_count'] ?? 0,
                'hire_date' => $data['hire_date'],
                'seniority_date' => $data['seniority_date'] ?? $data['hire_date'],
                'department_id' => $data['department_id'] ?? null,
                'position_id' => $data['position_id'] ?? null,
                'superior_id' => $data['superior_id'] ?? null,
                'professional_category' => $data['professional_category'] ?? null,
                'collective_agreement' => $data['collective_agreement'] ?? null,
                'status' => $data['status'] ?? 'active',
                'exit_date' => $data['exit_date'] ?? null,
                'exit_reason' => $data['exit_reason'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account' => $data['bank_account'] ?? null,
                'mobile_money' => $data['mobile_money'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'bank',
                'payment_currency' => $data['payment_currency'] ?? 'XOF',
            ]);

            activity()
                ->performedOn($employee)
                ->causedBy(auth()->user())
                ->withProperties(['matricule' => $matricule])
                ->log('Création employé');

            return $employee;
        });
    }

    public function addContract(Employee $employee, array $data): EmployeeContract
    {
        return DB::transaction(function () use ($employee, $data) {
            $contract = EmployeeContract::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'contract_type_id' => $data['contract_type_id'],
                'contract_number' => $data['contract_number'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'trial_period_end_date' => $data['trial_period_end_date'] ?? null,
                'working_hours_per_week' => $data['working_hours_per_week'] ?? null,
                'base_salary' => $data['base_salary'] ?? 0,
                'status' => $data['status'] ?? 'active',
            ]);

            activity()
                ->performedOn($contract)
                ->causedBy(auth()->user())
                ->withProperties(['employee_matricule' => $employee->matricule])
                ->log('Ajout contrat employé');

            return $contract;
        });
    }

    public function addDocument(Employee $employee, array $data): EmployeeDocument
    {
        return DB::transaction(function () use ($employee, $data) {
            $document = EmployeeDocument::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'uploaded_by' => auth()->id(),
                'document_type' => $data['document_type'],
                'name' => $data['name'],
                'file_path' => $data['file_path'] ?? null,
                'issued_at' => $data['issued_at'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
                'status' => $data['status'] ?? 'valid',
                'notes' => $data['notes'] ?? null,
            ]);

            activity()
                ->performedOn($document)
                ->causedBy(auth()->user())
                ->withProperties(['employee_matricule' => $employee->matricule])
                ->log('Ajout document employé');

            return $document;
        });
    }

    protected function generateMatricule(Company $company): string
    {
        $year = now()->format('Y');
        $prefix = "EMP-{$year}-";

        $last = Employee::where('company_id', $company->id)
            ->where('matricule', 'like', $prefix . '%')
            ->orderByDesc('matricule')
            ->value('matricule');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        do {
            $matricule = sprintf('%s%04d', $prefix, $sequence);
            $sequence++;
        } while (Employee::where('company_id', $company->id)->where('matricule', $matricule)->exists());

        return $matricule;
    }
}
