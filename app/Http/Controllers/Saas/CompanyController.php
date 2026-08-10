<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(Request $request): Response
    {
        $companies = $request->user()
            ->companies()
            ->orderBy('companies.name')
            ->get();

        return Inertia::render('Saas/Companies/Index', [
            'companies' => $companies,
        ]);
    }

    public function switch(Request $request, Company $company): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403
        );

        if (! $company->is_active) {
            abort(403, 'Cette entreprise est inactive.');
        }

        session(['active_company_id' => $company->id]);

        return redirect()->route('dashboard');
    }
}
