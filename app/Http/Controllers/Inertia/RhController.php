<?php
namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreEmployeeRequest;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeeContract;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Position;
use App\Modules\Hr\Models\ContractType;
use App\Modules\Hr\Services\EmployeeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RhController extends Controller
{
    public function __construct(protected EmployeeService $employeeService)
    {
    }

    public function employes(Request $request)
    {
        $this->authorize('viewAny', Employee::class);
        $company = app()->bound('current_company') ? app('current_company') : null;
        $employees = $company ? Employee::where('company_id', $company->id)
            ->with(['department', 'position'])
            ->orderBy('last_name')->get() : collect();
        return Inertia::render('Hr/Index', ['employees' => $employees, 'activeTab' => 'employes']);
    }

    public function createEmploye(Request $request)
    {
        $this->authorize('create', Employee::class);
        $company = app()->bound('current_company') ? app('current_company') : null;

        $departments = $company ? Department::where('company_id', $company->id)->active()->get() : collect();
        $positions = $company ? Position::where('company_id', $company->id)->active()->get() : collect();
        $contractTypes = ContractType::where('is_active', true)->get();

        return Inertia::render('Hr/CreateEmploye', [
            'departments' => $departments,
            'positions' => $positions,
            'contractTypes' => $contractTypes,
            'activeTab' => 'employes',
        ]);
    }

    public function storeEmploye(StoreEmployeeRequest $request)
    {
        $company = app()->bound('current_company') ? app('current_company') : null;

        if (!$company) {
            return back()->withErrors(['error' => 'Aucune entreprise active sélectionnée.']);
        }

        $data = $request->validated();

        // Créer l'employé
        $employeeData = collect($data)->except(['contract'])->toArray();
        $employee = $this->employeeService->createEmployee($company, $employeeData);

        // Créer le contrat si renseigné
        if (!empty($data['contract'])) {
            $this->employeeService->addContract($employee, $data['contract']);
        }

        return redirect()->route('hr.index')
            ->with('success', "Employé {$employee->full_name} créé avec succès.");
    }

    public function contrats(Request $request)
    {
        $company = app()->bound('current_company') ? app('current_company') : null;
        $contracts = $company ? EmployeeContract::where('company_id', $company->id)
            ->with(['employee', 'contractType'])
            ->latest('start_date')->get() : collect();
        return Inertia::render('Hr/Contrats', ['contracts' => $contracts, 'activeTab' => 'contrats']);
    }

    public function departements(Request $request)
    {
        $company = app()->bound('current_company') ? app('current_company') : null;
        $departments = $company ? Department::where('company_id', $company->id)
            ->withCount('employees')
            ->orderBy('name')->get() : collect();
        return Inertia::render('Hr/Departements', ['departments' => $departments, 'activeTab' => 'departements']);
    }

    public function conges(Request $request)
    {
        return Inertia::render('Hr/Conges', ['activeTab' => 'conges']);
    }
}
