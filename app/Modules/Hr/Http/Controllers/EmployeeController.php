<?php

namespace App\Modules\Hr\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Hr\Models\ContractType;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeeContract;
use App\Modules\Hr\Models\EmployeeDocument;
use App\Modules\Hr\Models\Leave;
use App\Modules\Hr\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    protected function company(Request $request): Company
    {
        return $request->attributes->get('company') ?? Company::first();
    }

    private function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'sex' => 'nullable|in:M,F',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'id_card_number' => 'nullable|string|max:100',
            'cnps_number' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'dependents_count' => 'nullable|integer|min:0',
            'hire_date' => 'required|date',
            'seniority_date' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'payment_method' => 'nullable|in:bank,cash,mobile_money',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'mobile_money' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive,suspended,terminated',
        ];
    }

    private function mapEmployee(Employee $e): array
    {
        return [
            'id' => $e->id, 'matricule' => $e->matricule,
            'first_name' => $e->first_name, 'last_name' => $e->last_name,
            'full_name' => trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? '')),
            'sex' => $e->sex, 'birth_date' => $e->birth_date?->toDateString(),
            'birth_place' => $e->birth_place, 'nationality' => $e->nationality,
            'id_card_number' => $e->id_card_number, 'cnps_number' => $e->cnps_number,
            'email' => $e->email, 'phone' => $e->phone, 'address' => $e->address,
            'marital_status' => $e->marital_status, 'dependents_count' => (int) ($e->dependents_count ?? 0),
            'hire_date' => $e->hire_date?->toDateString(), 'seniority_date' => $e->seniority_date?->toDateString(),
            'department_id' => $e->department_id, 'position_id' => $e->position_id,
            'department' => $e->department, 'position' => $e->position,
            'payment_method' => $e->payment_method, 'bank_name' => $e->bank_name,
            'bank_account' => $e->bank_account, 'mobile_money' => $e->mobile_money,
            'status' => $e->status,
        ];
    }

    private function references($company): array
    {
        return [
            'departments' => Department::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'positions' => Position::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'department_id']),
            'contractTypes' => ContractType::where('is_active', true)->get(['id', 'name', 'code']),
        ];
    }

    /* ===== FORMULAIRE CRÉATION ===== */
    public function create(Request $request)
    {
        $company = $this->company($request);
        return Inertia::render('Hr/Employees/Form', array_merge($this->references($company), [
            'employee' => null,
        ]));
    }

    /* ===== FORMULAIRE ÉDITION ===== */
    public function edit(Request $request, Employee $employee)
    {
        if ($employee->company_id !== $this->company($request)->id) abort(403);
        $employee->load(['department', 'position']);
        $company = $this->company($request);
        return Inertia::render('Hr/Employees/Form', array_merge($this->references($company), [
            'employee' => $this->mapEmployee($employee),
        ]));
    }

    /* ===== ENREGISTRER (CREATE) ===== */
    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $company = $this->company($request);

        $n = Employee::where('company_id', $company->id)->withTrashed()->count() + 1;
        $employee = Employee::create(array_merge($data, [
            'company_id' => $company->id,
            'matricule' => 'EMP-' . now()->format('Y') . '-' . str_pad($n, 4, '0', STR_PAD_LEFT),
            'status' => $data['status'] ?? 'active',
            'seniority_date' => $data['seniority_date'] ?? $data['hire_date'],
        ]));

        activity()->performedOn($employee)->causedBy(auth()->user())->log('Création employé');

        return redirect()->route('hr.index', ['tab' => 'employes'])
            ->with('success', "Employé {$employee->full_name} créé (Matricule: {$employee->matricule}).");
    }

    /* ===== METTRE À JOUR (UPDATE) ===== */
    public function update(Request $request, Employee $employee)
    {
        if ($employee->company_id !== $this->company($request)->id) abort(403);
        $data = $request->validate($this->rules());
        $employee->update($data);

        activity()->performedOn($employee)->causedBy(auth()->user())
            ->withProperties(['changes' => $employee->getChanges()])->log('Mise à jour employé');

        return redirect()->route('hr.employees.show', $employee->id)
            ->with('success', "Employé {$employee->full_name} mis à jour.");
    }

    /* ===== PAGE UNIFIÉE /hr ===== */
    public function hub(Request $request)
    {
        $company = $this->company($request);

        $employees = Employee::where('company_id', $company->id)
            ->with(['department', 'position'])->orderBy('last_name')->get()
            ->map(fn ($e) => [
                'id' => $e->id, 'matricule' => $e->matricule,
                'first_name' => $e->first_name, 'last_name' => $e->last_name,
                'full_name' => trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? '')),
                'email' => $e->email, 'phone' => $e->phone, 'sex' => $e->sex,
                'hire_date' => $e->hire_date?->toDateString(), 'status' => $e->status,
                'department' => $e->department ? ['id' => $e->department->id, 'name' => $e->department->name] : null,
                'position' => $e->position ? ['id' => $e->position->id, 'name' => $e->position->name] : null,
            ]);

        $departments = Department::where('company_id', $company->id)
            ->withCount(['positions', 'employees'])->orderBy('code')->get()
            ->map(fn ($d) => ['id' => $d->id, 'code' => $d->code, 'name' => $d->name,
                'is_active' => (bool) ($d->is_active ?? true), 'positions_count' => $d->positions_count, 'employees_count' => $d->employees_count]);

        $positions = Position::where('company_id', $company->id)
            ->with('department')->withCount('employees')->orderBy('code')->get()
            ->map(fn ($p) => ['id' => $p->id, 'code' => $p->code, 'name' => $p->name,
                'is_active' => (bool) ($p->is_active ?? true),
                'department' => $p->department ? ['id' => $p->department->id, 'name' => $p->department->name] : null,
                'employees_count' => $p->employees_count]);

        $contractTypes = ContractType::orderBy('code')->get()
            ->map(fn ($c) => ['id' => $c->id, 'code' => $c->code, 'name' => $c->name, 'is_active' => (bool) ($c->is_active ?? true)]);

        $contracts = EmployeeContract::where('company_id', $company->id)
            ->with(['employee', 'contractType'])->orderByDesc('start_date')->get()
            ->map(fn ($c) => ['id' => $c->id, 'contract_number' => $c->contract_number,
                'employee' => ['id' => $c->employee->id, 'full_name' => trim(($c->employee->first_name ?? '') . ' ' . ($c->employee->last_name ?? '')), 'matricule' => $c->employee->matricule],
                'contract_type' => $c->contractType->name ?? '—', 'start_date' => $c->start_date?->toDateString(),
                'end_date' => $c->end_date?->toDateString(), 'base_salary' => (float) $c->base_salary, 'status' => $c->status]);

        $leaves = Leave::where('company_id', $company->id)->with('employee')->orderByDesc('start_date')->get()
            ->map(fn ($l) => ['id' => $l->id,
                'employee' => ['id' => $l->employee->id, 'full_name' => trim(($l->employee->first_name ?? '') . ' ' . ($l->employee->last_name ?? '')), 'matricule' => $l->employee->matricule],
                'leave_type' => $l->leave_type, 'start_date' => $l->start_date?->toDateString(),
                'end_date' => $l->end_date?->toDateString(), 'days_count' => $l->days_count, 'status' => $l->status]);

        $documents = EmployeeDocument::with('employee')->orderByDesc('created_at')->get()
            ->map(fn ($d) => ['id' => $d->id,
                'employee' => ['id' => $d->employee->id, 'full_name' => trim(($d->employee->first_name ?? '') . ' ' . ($d->employee->last_name ?? '')), 'matricule' => $d->employee->matricule],
                'document_type' => $d->document_type, 'name' => $d->name, 'file_path' => $d->file_path,
                'expires_at' => $d->expires_at?->toDateString(), 'status' => $d->status]);

        $allEmployees = Employee::where('company_id', $company->id)->where('status', 'active')
            ->orderBy('last_name')->get(['id', 'matricule', 'first_name', 'last_name'])
            ->map(fn ($e) => ['id' => $e->id, 'full_name' => trim($e->first_name . ' ' . $e->last_name), 'matricule' => $e->matricule]);

        return Inertia::render('Hr/Index', [
            'employees' => $employees, 'departments' => $departments, 'positions' => $positions,
            'contractTypes' => $contractTypes, 'contracts' => $contracts, 'leaves' => $leaves,
            'documents' => $documents, 'allEmployees' => $allEmployees,
            'allDepartments' => $departments->map(fn ($d) => ['id' => $d['id'], 'name' => $d['name']])->values(),
            'stats' => [
                'total' => Employee::where('company_id', $company->id)->count(),
                'active' => Employee::where('company_id', $company->id)->where('status', 'active')->count(),
                'inactive' => Employee::where('company_id', $company->id)->where('status', '!=', 'active')->count(),
            ],
            'initialTab' => $request->query('tab', 'employes'),
        ]);
    }

    public function index(Request $request) { return $this->hub($request); }

    public function show(Request $request, Employee $employee)
    {
        if ($employee->company_id !== $this->company($request)->id) abort(403);
        $employee->load(['department', 'position', 'contracts.contractType', 'documents']);
        $company = $this->company($request);

        return Inertia::render('Hr/Employees/Show', [
            'employee' => array_merge($this->mapEmployee($employee), [
                'contracts' => $employee->contracts->map(fn ($c) => [
                    'id' => $c->id, 'contract_number' => $c->contract_number,
                    'contract_type' => $c->contractType->name ?? '—', 'start_date' => $c->start_date?->toDateString(),
                    'end_date' => $c->end_date?->toDateString(), 'base_salary' => (float) $c->base_salary, 'status' => $c->status,
                ]),
                'documents' => $employee->documents->map(fn ($d) => [
                    'id' => $d->id, 'document_type' => $d->document_type, 'name' => $d->name,
                    'status' => $d->status, 'expires_at' => $d->expires_at?->toDateString(),
                ]),
            ]),
            'departments' => Department::where('company_id', $company->id)->where('is_active', true)->get(['id', 'name']),
            'positions' => Position::where('company_id', $company->id)->where('is_active', true)->get(['id', 'name', 'department_id']),
            'contractTypes' => ContractType::where('is_active', true)->get(['id', 'name', 'code']),
        ]);
    }

    public function destroy(Request $request, Employee $employee)
    {
        if ($employee->company_id !== $this->company($request)->id) abort(403);
        if ($employee->status === 'active') return back()->with('error', 'Impossible de supprimer un employé actif.');
        $fullName = $employee->full_name;
        $employee->delete();
        activity()->causedBy(auth()->user())->log("Suppression employé {$fullName}");
        return redirect()->route('hr.index', ['tab' => 'employes'])->with('success', "Employé {$fullName} supprimé.");
    }

    public function deactivate(Request $request, Employee $employee)
    {
        if ($employee->company_id !== $this->company($request)->id) abort(403);
        $employee->update(['status' => 'inactive']);
        return back()->with('success', "Employé {$employee->full_name} désactivé.");
    }

    public function activate(Request $request, Employee $employee)
    {
        if ($employee->company_id !== $this->company($request)->id) abort(403);
        $employee->update(['status' => 'active']);
        return back()->with('success', "Employé {$employee->full_name} réactivé.");
    }
}
