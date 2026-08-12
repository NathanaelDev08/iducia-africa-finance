<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SystemModule;
use App\Models\User;
use App\Services\UserProvisioningService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function __construct(private UserProvisioningService $service)
    {
        // L'autorisation est gérée par le middleware 'super.admin' sur les routes
    }

    public function index()
    {
        return Inertia::render('SuperAdmin/Users/Index', [
            'users' => User::with(['companies', 'modules'])->latest()->get(),
            'companies' => Company::where('is_active', true)->get(['id', 'name']),
            'modules' => SystemModule::where('is_active', true)->orderBy('display_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'company_id' => 'required|exists:companies,id',
            'role' => 'required|string',
            'modules' => 'required|array|min:1',
            'module_permissions' => 'sometimes|array',
            'module_permissions.*.module_id' => 'required_with:module_permissions|integer',
            'module_permissions.*.can_view' => 'sometimes|boolean',
            'module_permissions.*.can_create' => 'sometimes|boolean',
            'module_permissions.*.can_edit' => 'sometimes|boolean',
            'module_permissions.*.can_delete' => 'sometimes|boolean',
        ]);

        $user = $request->user();
        if (! $user->isSuperAdmin() && in_array($v['role'], ['super-admin', 'admin-company', 'admin'], true)) {
            abort(403, 'Seul le super-admin principal peut créer un compte administrateur système.');
        }

        $r = $this->service->createUser($v, $user);

        $msg = $r['email_sent']
            ? "Utilisateur créé. Email envoyé à {$r['user']->email}."
            : "Email non envoyé. MDP temporaire : {$r['temp_password']}";

        return redirect()->back()->with('success', $msg);
    }

    public function resetPassword(User $user)
    {
        $r = $this->service->createUser([
            'name' => $user->name,
            'email' => $user->email,
            'company_id' => $user->companies->first()?->id,
            'role' => 'employee',
            'modules' => $user->modules->pluck('id')->toArray(),
        ], request()->user());

        return redirect()->back()->with('success',
            $r['email_sent'] ? 'MDP réinitialisé et envoyé.' : "MDP temporaire : {$r['temp_password']}");
    }

    public function toggle(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return redirect()->back()->with('success', 'Statut mis à jour.');
    }
}
