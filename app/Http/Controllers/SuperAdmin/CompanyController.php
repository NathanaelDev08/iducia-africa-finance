<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function __construct(private TenantProvisioningService $provisioning)
    {
        $this->middleware('module:settings');
    }

    public function index(Request $request)
    {
        $companies = Company::query()
            ->withCount(['users', 'employees'])
            ->with(['subscriptions' => fn ($q) => $q->whereIn('status', ['active', 'trial'])->with('plan')->latest('starts_at')->limit(1)])
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

        $subscription = $company->subscriptions()->with('plan')->latest('starts_at')->first();

        return Inertia::render('SuperAdmin/Companies/Show', [
            'company' => $company,
            'stats' => $stats,
            'subscription' => $subscription,
            'plans' => Plan::where('is_active', true)->orderBy('price')->get(),
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
        // is_active pilote l'UI d'administration ; is_blocked est ce que
        // EnforceCompanyBlock vérifie pour déconnecter immédiatement les
        // utilisateurs de l'entreprise. Les deux doivent rester synchronisés,
        // sinon suspendre une entreprise ici ne l'empêche pas réellement
        // d'utiliser le système tant que ses utilisateurs restent connectés.
        $willBeActive = !$company->is_active;

        $company->update([
            'is_active' => $willBeActive,
            'suspended_at' => $willBeActive ? null : now(),
            'is_blocked' => !$willBeActive,
            'blocked_at' => $willBeActive ? null : now(),
        ]);

        return back()->with('success',
            $willBeActive
                ? "Entreprise '{$company->name}' réactivée."
                : "Entreprise '{$company->name}' suspendue."
        );
    }

    public function updateSubscription(Request $request, Company $company)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|in:trial,active,cancelled,expired',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        // Une entreprise n'a qu'un abonnement courant : on ferme les précédents
        // encore actifs/en essai avant d'ouvrir le nouveau.
        $company->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $company->subscriptions()->create($validated);

        return back()->with('success', 'Abonnement mis à jour pour ' . $company->name . '.');
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
