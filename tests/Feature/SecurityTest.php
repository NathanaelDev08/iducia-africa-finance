<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Modules\Accounting\Models\AccountingEntry;
use App\Modules\Accounting\Models\FiscalYear;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\Period;
use App\Modules\Hr\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Company $otherCompany;
    protected Journal $journal;
    protected Period $period;
    protected Journal $otherJournal;
    protected Period $otherPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $roles = [
            'super-admin', 'admin-company', 'accountant', 'hr-manager',
            'payroll-manager', 'tax-manager', 'auditor', 'manager', 'employee',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->company = Company::create([
            'name' => 'Entreprise Test', 'slug' => 'entreprise-test',
            'currency' => 'XOF', 'timezone' => 'Africa/Abidjan', 'is_active' => true,
        ]);

        $this->otherCompany = Company::create([
            'name' => 'Autre Entreprise', 'slug' => 'autre-entreprise',
            'currency' => 'XOF', 'timezone' => 'Africa/Abidjan', 'is_active' => true,
        ]);

        // Dépendances comptables pour l'entreprise principale
        $fy = FiscalYear::create(['company_id' => $this->company->id, 'name' => 'Y1', 'start_date' => now()->startOfYear(), 'end_date' => now()->endOfYear(), 'status' => 'open']);
        $this->period = Period::create(['company_id' => $this->company->id, 'fiscal_year_id' => $fy->id, 'name' => 'P1', 'number' => 1, 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
        $this->journal = Journal::create(['company_id' => $this->company->id, 'code' => 'OD', 'name' => 'OD', 'type' => 'misc']);

        // Dépendances comptables pour l'autre entreprise (pour éviter l'erreur de clé étrangère)
        $fy2 = FiscalYear::create(['company_id' => $this->otherCompany->id, 'name' => 'Y2', 'start_date' => now()->startOfYear(), 'end_date' => now()->endOfYear(), 'status' => 'open']);
        $this->otherPeriod = Period::create(['company_id' => $this->otherCompany->id, 'fiscal_year_id' => $fy2->id, 'name' => 'P2', 'number' => 1, 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'status' => 'open']);
        $this->otherJournal = Journal::create(['company_id' => $this->otherCompany->id, 'code' => 'OD', 'name' => 'OD', 'type' => 'misc']);
    }

    #[Test]
    public function user_cannot_access_other_company_data()
    {
        $user = User::create(['name' => 'Test User', 'email' => 'test@example.com', 'password' => bcrypt('password')]);
        $user->companies()->attach($this->company->id, ['role' => 'accountant', 'is_active' => true]);
        $user->assignRole('accountant');

        $entry = AccountingEntry::create([
            'company_id' => $this->otherCompany->id,
            'journal_id' => $this->otherJournal->id,
            'period_id' => $this->otherPeriod->id,
            'entry_date' => now(),
            'description' => 'Test',
            'status' => 'draft',
        ]);

        $this->actingAs($user);
        $this->assertFalse($user->can('view', $entry));
    }

    #[Test]
    public function employee_can_only_view_own_payslip()
    {
        $employeeUser = User::create(['name' => 'Employee User', 'email' => 'employee@example.com', 'password' => bcrypt('password')]);
        $employeeUser->companies()->attach($this->company->id, ['role' => 'employee', 'is_active' => true]);
        $employeeUser->assignRole('employee');

        $employee = Employee::create([
            'company_id' => $this->company->id, 'user_id' => $employeeUser->id, 'matricule' => 'EMP001',
            'first_name' => 'John', 'last_name' => 'Doe', 'hire_date' => now(), 'status' => 'active',
        ]);

        $otherEmployee = Employee::create([
            'company_id' => $this->company->id, 'matricule' => 'EMP002',
            'first_name' => 'Jane', 'last_name' => 'Smith', 'hire_date' => now(), 'status' => 'active',
        ]);

        $this->actingAs($employeeUser);
        $this->assertTrue($employeeUser->can('view', $employee));
        $this->assertFalse($employeeUser->can('view', $otherEmployee));
    }

    #[Test]
    public function only_accountant_can_create_accounting_entries()
    {
        $employee = User::create(['name' => 'Employee', 'email' => 'emp@example.com', 'password' => bcrypt('password')]);
        $employee->assignRole('employee');

        $accountant = User::create(['name' => 'Accountant', 'email' => 'acc@example.com', 'password' => bcrypt('password')]);
        $accountant->assignRole('accountant');

        $this->assertFalse($employee->can('create', AccountingEntry::class));
        $this->assertTrue($accountant->can('create', AccountingEntry::class));
    }
}
