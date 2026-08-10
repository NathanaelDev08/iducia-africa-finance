<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UserController extends Controller
{
    public const ROLES = [
        'super-admin' => 'Super Administrateur',
        'admin-company' => 'Administrateur d\'entreprise',
        'accountant' => 'Comptable',
        'hr-manager' => 'Responsable RH',
        'payroll-manager' => 'Gestionnaire de Paie',
        'tax-manager' => 'Fiscaliste',
        'auditor' => 'Auditeur',
        'manager' => 'Manager',
        'employee' => 'Employé',
    ];

    public function __construct(private TenantProvisioningService $provisioning)
    {
    }

    public function index(Request $request)
    {
        $users = User::query()
            ->with('companies:id,name')
            ->when($request->search, fn($q) => $q->where('name', 'ilike', '%' . $request->search . '%')
                ->orWhere('email', 'ilike', '%' . $request->search . '%'))
            ->latest()
            ->paginate(15);

        return Inertia::render('SuperAdmin/Users/Index', [
            'users' => $users,
            'roles' => self::ROLES,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('SuperAdmin/Users/Create', [
            'companies' => Company::where('is_active', true)->get(['id', 'name']),
            'roles' => self::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:' . implode(',', array_keys(self::ROLES)),
            'company_id' => 'required|exists:companies,id',
        ]);

        try {
            $user = $this->provisioning->createUser($validated);

            return redirect()
                ->route('super-admin.users.index')
                ->with('success', "Utilisateur '{$user->name}' créé avec succès.");

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        $user->load('companies');

        return Inertia::render('SuperAdmin/Users/Edit', [
            'user' => $user,
            'companies' => Company::where('is_active', true)->get(['id', 'name']),
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:' . implode(',', array_keys(self::ROLES)),
            'company_id' => 'required|exists:companies,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Mettre à jour le rôle dans company_user
        $user->companies()->sync([
            $validated['company_id'] => ['role' => $validated['role'], 'is_active' => true]
        ]);

        // Mettre à jour le rôle Spatie
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()
            ->route('super-admin.users.index')
            ->with('success', 'Utilisateur mis à jour.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $newPassword = Str::random(12) . '1A!';
        $user->update(['password' => Hash::make($newPassword)]);

        return back()->with('success', "Mot de passe réinitialisé : {$newPassword}");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()
            ->route('super-admin.users.index')
            ->with('success', 'Utilisateur supprimé.');
    }
}
