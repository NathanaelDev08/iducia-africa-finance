<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function __construct(private TenantProvisioningService $provisioning)
    {
    }

    public function index(Request $request)
    {
        $companies = Company::query()
            ->withCount(['users', 'employees'])
            ->when($request->search, fn($q) => $q->where('name', 'ilike', '%' . $request->search . '%'))
            ->latest()
            ->paginate(15);

        return Inertia::render('SuperAdmin/Companies/Index', [
            'companies' => $companies,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('SuperAdmin/Companies/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'short_name' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'rccm' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'currency' => 'required|in:XOF,EUR,USD,GBP',
            'timezone' => 'required|string',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        try {
            // 1. Créer l'entreprise avec configuration complète
            $company = $this->provisioning->createCompany($validated);

            // 2. Créer l'admin de l'entreprise
            $admin = $this->provisioning->createUser([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => $validated['admin_password'],
                'company_id' => $company->id,
                'role' => 'admin-company',
            ]);

            return redirect()
                ->route('super-admin.companies.index')
                ->with('success', "Entreprise '{$company->name}' créée avec succès. Admin : {$admin->email}");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    public function show(Company $company)
    {
        $company->load(['users' => fn($q) => $q->withPivot(['role', 'is_active'])]);

        $stats = [
            'employees' => $company->employees()->count(),
            'payslips' => $company->payslips()->count(),
            'clients' => $company->clients()->count(),
            'invoices' => $company->salesInvoices()->count(),
        ];

        return Inertia::render('SuperAdmin/Companies/Show', [
            'company' => $company,
            'stats' => $stats,
        ]);
    }

    public function edit(Company $company)
    {
        return Inertia::render('SuperAdmin/Companies/Edit', ['company' => $company]);
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'short_name' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'rccm' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'currency' => 'required|in:XOF,EUR,USD,GBP',
            'timezone' => 'required|string',
        ]);

        $company->update($validated);

        return redirect()
            ->route('super-admin.companies.index')
            ->with('success', 'Entreprise mise à jour.');
    }

    public function toggleActive(Company $company)
    {
        $company->update([
            'is_active' => !$company->is_active,
            'suspended_at' => $company->is_active ? now() : null,
        ]);

        return back()->with('success',
            $company->is_active
                ? "Entreprise '{$company->name}' réactivée."
                : "Entreprise '{$company->name}' suspendue."
        );
    }

    public function destroy(Company $company)
    {
        try {
            $company->delete(); // Cascade grâce aux FK ON DELETE CASCADE
            return redirect()
                ->route('super-admin.companies.index')
                ->with('success', "Entreprise '{$company->name}' supprimée avec toutes ses données.");
        } catch (\Exception $e) {
            return back()->with('error', 'Impossible de supprimer : ' . $e->getMessage());
        }
    }
}
