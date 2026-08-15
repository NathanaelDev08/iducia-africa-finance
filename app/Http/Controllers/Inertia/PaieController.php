<?php
namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\StorePayRunRequest;
use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Payroll\Models\PayItem;
use App\Modules\Payroll\Services\PayrollCalculationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaieController extends Controller
{
    public function __construct(protected PayrollCalculationService $payrollService)
    {
    }

    public function periodes(Request $request)
    {
        $this->authorize('viewAny', PayRun::class);
        $company = app()->bound('current_company') ? app('current_company') : null;
        $payruns = $company ? PayRun::where('company_id', $company->id)
            ->withCount('payslips')
            ->latest('period_start')->get() : collect();
        return Inertia::render('Payroll/Index', ['payruns' => $payruns, 'activeTab' => 'periodes']);
    }

    public function createPeriode(Request $request)
    {
        $this->authorize('create', PayRun::class);

        return Inertia::render('Payroll/CreatePeriode', [
            'activeTab' => 'periodes',
        ]);
    }

    public function storePeriode(StorePayRunRequest $request)
    {
        $company = app()->bound('current_company') ? app('current_company') : null;

        if (!$company) {
            return back()->withErrors(['error' => 'Aucune entreprise active sélectionnée.']);
        }

        $data = $request->validated();

        $payRun = PayRun::create([
            'company_id' => $company->id,
            'name' => $data['name'],
            'reference' => $data['reference'] ?? 'PAIE-' . now()->format('Ym'),
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'payment_date' => $data['payment_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'draft',
        ]);

        return redirect()->route('payroll.index')
            ->with('success', "Période de paie '{$payRun->name}' créée avec succès.");
    }

    public function calculerPaie(Request $request, PayRun $payRun)
    {
        $this->authorize('validate', $payRun);

        try {
            $payslips = $this->payrollService->calculatePayRun($payRun);
            return back()->with('success', count($payslips) . ' bulletin(s) calculé(s) avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function bulletins(Request $request)
    {
        $company = app()->bound('current_company') ? app('current_company') : null;
        $payslips = $company ? Payslip::where('company_id', $company->id)
            ->with(['employee', 'payRun'])
            ->latest('id')->take(50)->get() : collect();
        return Inertia::render('Payroll/Bulletins', ['payslips' => $payslips, 'activeTab' => 'bulletins']);
    }

    public function rubriques(Request $request)
    {
        $company = app()->bound('current_company') ? app('current_company') : null;
        $items = PayItem::where(function($q) use ($company) {
            $q->whereNull('company_id')->orWhere('company_id', $company?->id);
        })->orderBy('display_order')->get();
        return Inertia::render('Payroll/Rubriques', ['items' => $items, 'activeTab' => 'rubriques']);
    }

    public function journalPaie(Request $request)
    {
        return Inertia::render('Payroll/Journal', ['activeTab' => 'journal']);
    }
}
