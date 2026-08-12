<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanySwitchController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:settings');
    }

    /** Liste JSON des entreprises autorisées pour l'utilisateur connecté */
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->companies()->get()->map(fn ($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'role' => $c->pivot->role ?? 'admin',
            ])
        );
    }

    /** Changement d'entreprise active — REFUSE si non autorisée */
    public function switch(Request $request, Company $company)
    {
        $user = $request->user();

        if (!$user->companies()->where('companies.id', $company->id)->exists()) {
            return back()->with('error', 'Accès non autorisé à cette entreprise.');
        }

        session(['active_company_id' => $company->id]);

        return back()->with('success', 'Entreprise active : ' . $company->name);
    }
}
