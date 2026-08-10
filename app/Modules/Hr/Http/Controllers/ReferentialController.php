<?php

namespace App\Modules\Hr\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Hr\Models\ContractType;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferentialController extends Controller
{
    protected function company(Request $request): Company
    {
        return $request->attributes->get('company') ?? Company::first();
    }

    public function index(Request $request)
    {
        $company = $this->company($request);

        return Inertia::render('Hr/Referentials/Index', [
            'departments' => Department::where('company_id', $company->id)
                ->withCount(['positions', 'employees'])
                ->orderBy('code')->get()
                ->map(fn ($d) => [
                    'id' => $d->id, 'code' => $d->code, 'name' => $d->name,
                    'is_active' => (bool) $d->is_active,
                    'positions_count' => $d->positions_count,
                    'employees_count' => $d->employees_count,
                ]),
            'positions' => Position::where('company_id', $company->id)
                ->with('department')->withCount('employees')
                ->orderBy('code')->get()
                ->map(fn ($p) => [
                    'id' => $p->id, 'code' => $p->code, 'name' => $p->name,
                    'is_active' => (bool) $p->is_active,
                    'department' => $p->department ? ['id' => $p->department->id, 'name' => $p->department->name] : null,
                    'employees_count' => $p->employees_count,
                ]),
            'contractTypes' => ContractType::orderBy('code')->get()
                ->map(fn ($c) => [
                    'id' => $c->id, 'code' => $c->code, 'name' => $c->name, 'is_active' => (bool) $c->is_active,
                ]),
            'allDepartments' => Department::where('company_id', $company->id)->where('is_active', true)->get(['id', 'name']),
        ]);
    }

    /* ===== DÉPARTEMENTS ===== */
    public function storeDepartment(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:20', 'name' => 'required|string|max:255']);
        Department::create(array_merge($data, ['company_id' => $this->company($request)->id, 'is_active' => true]));
        return back()->with('success', 'Département créé.');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $data = $request->validate(['code' => 'required|string|max:20', 'name' => 'required|string|max:255', 'is_active' => 'boolean']);
        $department->update($data);
        return back()->with('success', 'Département mis à jour.');
    }

    public function destroyDepartment(Request $request, Department $department)
    {
        if ($department->employees()->count() > 0 || $department->positions()->count() > 0) {
            return back()->with('error', 'Impossible : ce département contient des postes ou des employés.');
        }
        $department->delete();
        return back()->with('success', 'Département supprimé.');
    }

    /* ===== POSTES ===== */
    public function storePosition(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20', 'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
        ]);
        Position::create(array_merge($data, ['company_id' => $this->company($request)->id, 'is_active' => true]));
        return back()->with('success', 'Poste créé.');
    }

    public function updatePosition(Request $request, Position $position)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20', 'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id', 'is_active' => 'boolean',
        ]);
        $position->update($data);
        return back()->with('success', 'Poste mis à jour.');
    }

    public function destroyPosition(Request $request, Position $position)
    {
        if ($position->employees()->count() > 0) {
            return back()->with('error', 'Impossible : des employés occupent ce poste.');
        }
        $position->delete();
        return back()->with('success', 'Poste supprimé.');
    }

    /* ===== TYPES DE CONTRAT ===== */
    public function storeContractType(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:20', 'name' => 'required|string|max:255']);
        ContractType::create(array_merge($data, ['is_active' => true]));
        return back()->with('success', 'Type de contrat créé.');
    }

    public function updateContractType(Request $request, ContractType $contractType)
    {
        $data = $request->validate(['code' => 'required|string|max:20', 'name' => 'required|string|max:255', 'is_active' => 'boolean']);
        $contractType->update($data);
        return back()->with('success', 'Type de contrat mis à jour.');
    }

    public function destroyContractType(Request $request, ContractType $contractType)
    {
        $contractType->delete();
        return back()->with('success', 'Type de contrat supprimé.');
    }
}
