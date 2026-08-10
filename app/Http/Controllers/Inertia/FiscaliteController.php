<?php
namespace App\Http\Controllers\Inertia;
use App\Http\Controllers\Controller;
use App\Modules\Tax\Models\VatDeclaration;
use App\Modules\Tax\Models\FiscalDeadline;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FiscaliteController extends Controller
{
    public function declarations(Request $request)
    {
        $company = app()->bound('currentCompany') ? app('currentCompany') : null;
        $declarations = $company ? VatDeclaration::where('company_id', $company->id)
            ->latest('period_start')->get() : collect();
        return Inertia::render('Tax/Index', ['declarations' => $declarations, 'activeTab' => 'declarations']);
    }

    public function echeancier(Request $request)
    {
        $company = app()->bound('currentCompany') ? app('currentCompany') : null;
        $deadlines = $company ? FiscalDeadline::where('company_id', $company->id)
            ->orderBy('due_date')->get() : collect();
        return Inertia::render('Tax/Echeancier', ['deadlines' => $deadlines, 'activeTab' => 'echeancier']);
    }

    public function parametres(Request $request)
    {
        return Inertia::render('Tax/Parametres', ['activeTab' => 'parametres']);
    }
}
