<?php

namespace App\Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Hr\Models\Employee;
use App\Modules\Payroll\Models\PayItem;
use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Payroll\Models\SocialContribution;
use App\Modules\Payroll\Services\PayrollAccountingService;
use App\Modules\Payroll\Services\PayrollEngine;
use App\Modules\Accounting\Models\JournalEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:payroll');
    }

    protected function company(Request $request): Company
    {
        return $request->attributes->get('company') ?? Company::first();
    }

    /* ===== ONGLET 1 : PÉRIODES (liste globale) ===== */
    public function index(Request $request)
    {
        $company = $this->company($request);

        $payRuns = PayRun::where('company_id', $company->id)
            ->withCount('payslips')
            ->withSum('payslips', 'net_salary')
            ->withSum('payslips', 'gross_salary')
            ->orderByDesc('period_start')->get()
            ->map(fn ($pr) => [
                'id' => $pr->id, 'name' => $pr->name, 'reference' => $pr->reference,
                'period_start' => $pr->period_start?->toDateString(),
                'period_end' => $pr->period_end?->toDateString(),
                'status' => $pr->status, 'is_locked' => (bool) $pr->is_locked,
                'payslips_count' => $pr->payslips_count,
                'total_net' => (float) ($pr->payslips_sum_net_salary ?? 0),
                'total_gross' => (float) ($pr->payslips_sum_gross_salary ?? 0),
            ]);

        return Inertia::render('Payroll/Index', [
            'activeTab' => 'periodes',
            'payRuns' => $payRuns,
            'stats' => [
                'total_runs' => $payRuns->count(),
                'total_employees_active' => Employee::where('company_id', $company->id)->where('status', 'active')->count(),
            ],
        ]);
    }

    public function bulletins(Request $request)
    {
        $company = $this->company($request);
        $payslips = Payslip::where('company_id', $company->id)
            ->with(['employee', 'payRun'])->orderByDesc('id')->limit(200)->get()
            ->map(fn ($ps) => [
                'id' => $ps->id,
                'employee' => [
                    'full_name' => trim(($ps->employee->first_name ?? '') . ' ' . ($ps->employee->last_name ?? '')),
                    'matricule' => $ps->employee->matricule ?? '',
                ],
                'pay_run' => ['name' => $ps->payRun->name ?? ''],
                'gross_salary' => (float) $ps->gross_salary,
                'total_deductions' => (float) $ps->total_deductions,
                'net_salary' => (float) $ps->net_salary,
            ]);
        return Inertia::render('Payroll/Index', ['activeTab' => 'bulletins', 'payslips' => $payslips]);
    }

    public function calculs(Request $request)
    {
        $company = $this->company($request);
        $today = now()->toDateString();
        $payItems = PayItem::where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))
            ->with('rates')->orderBy('display_order')->get()
            ->map(function ($item) use ($today) {
                $rate = $item->rates->first(fn ($r) => $r->effective_from <= $today && (!$r->effective_until || $r->effective_until >= $today) && $r->is_active);
                return [
                    'code' => $item->code, 'name' => $item->name, 'type' => $item->type,
                    'rate' => $rate && $rate->rate !== null ? (float) $rate->rate : null,
                    'fixed_amount' => $rate && $rate->fixed_amount !== null ? (float) $rate->fixed_amount : null,
                    'ceiling' => $rate && $rate->ceiling !== null ? (float) $rate->ceiling : null,
                ];
            });
        $contributions = SocialContribution::with('rates')->where('is_active', true)->get()
            ->map(function ($c) use ($today) {
                $rate = $c->rates->first(fn ($r) => $r->effective_from <= $today && (!$r->effective_until || $r->effective_until >= $today) && $r->is_active);
                return [
                    'code' => $c->code, 'name' => $c->name, 'organism' => $c->organism,
                    'employee_rate' => $rate ? (float) $rate->employee_rate : 0,
                    'employer_rate' => $rate ? (float) $rate->employer_rate : 0,
                    'ceiling' => $rate && $rate->ceiling !== null ? (float) $rate->ceiling : null,
                ];
            });
        return Inertia::render('Payroll/Index', ['activeTab' => 'calculs', 'payItems' => $payItems, 'contributions' => $contributions]);
    }

    public function integration(Request $request)
    {
        $company = $this->company($request);
        $payRuns = PayRun::where('company_id', $company->id)
            ->with('accountingEntry')
            ->withSum('payslips', 'net_salary')
            ->withSum('payslips', 'employer_contributions')
            ->orderByDesc('period_start')->get()
            ->map(fn ($pr) => [
                'id' => $pr->id, 'name' => $pr->name, 'reference' => $pr->reference,
                'status' => $pr->status,
                'total_net' => (float) ($pr->payslips_sum_net_salary ?? 0),
                'total_employer' => (float) ($pr->payslips_sum_employer_contributions ?? 0),
                'accounting_entry' => $pr->accountingEntry ? ['id' => $pr->accountingEntry->id, 'reference' => $pr->accountingEntry->reference] : null,
            ]);
        return Inertia::render('Payroll/Index', ['activeTab' => 'integration', 'payRuns' => $payRuns]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Payroll/CreatePeriode', [
            'activeTab' => 'periodes',
        ]);
    }

    /* ===== PAGE DÉTAIL D'UNE PÉRIODE (avec onglets) ===== */
    public function show(Request $request, PayRun $payRun)
    {
        $company = $this->company($request);
        if ($payRun->company_id !== $company->id) abort(403);

        $payRun->load('payslips.employee.department');

        // Charger l'écriture comptable avec ses lignes (si existe)
        $accountingEntry = null;
        if ($payRun->accounting_entry_id) {
            $entry = JournalEntry::with(['items.account', 'journal'])->find($payRun->accounting_entry_id);
            if ($entry) {
                $accountingEntry = [
                    'id' => $entry->id,
                    'reference' => $entry->reference,
                    'entry_date' => $entry->entry_date->toDateString(),
                    'description' => $entry->description,
                    'journal_name' => $entry->journal->name ?? '—',
                    'total_debit' => (float) $entry->total_debit,
                    'total_credit' => (float) $entry->total_credit,
                    'items' => $entry->items->map(fn ($item) => [
                        'account_number' => $item->account->number ?? '—',
                        'account_name' => $item->account->name ?? '—',
                        'description' => $item->description,
                        'debit' => (float) $item->debit,
                        'credit' => (float) $item->credit,
                    ]),
                ];
            }
        }

        // Historique d'audit (Spatie Activity Log)
        $history = \Spatie\Activitylog\Models\Activity::where('subject_type', PayRun::class)
            ->where('subject_id', $payRun->id)
            ->with('causer')
            ->orderByDesc('created_at')->limit(50)->get()
            ->map(fn ($a) => [
                'description' => $a->description,
                'user' => $a->causer ? ($a->causer->name ?? 'Système') : 'Système',
                'created_at' => $a->created_at->format('d/m/Y H:i'),
                'properties' => $a->properties ? json_encode($a->properties) : null,
            ]);

        return Inertia::render('Payroll/Show', [
            'payRun' => [
                'id' => $payRun->id, 'name' => $payRun->name, 'reference' => $payRun->reference,
                'period_start' => $payRun->period_start?->toDateString(),
                'period_end' => $payRun->period_end?->toDateString(),
                'payment_date' => $payRun->payment_date?->toDateString(),
                'status' => $payRun->status, 'is_locked' => (bool) $payRun->is_locked,
                'accounting_entry_id' => $payRun->accounting_entry_id,
                'payslips' => $payRun->payslips->map(fn ($ps) => [
                    'id' => $ps->id,
                    'employee' => [
                        'id' => $ps->employee->id,
                        'full_name' => trim(($ps->employee->first_name ?? '') . ' ' . ($ps->employee->last_name ?? '')),
                        'matricule' => $ps->employee->matricule ?? '',
                        'department' => $ps->employee->department->name ?? '—',
                    ],
                    'base_salary' => (float) $ps->base_salary,
                    'gross_salary' => (float) $ps->gross_salary,
                    'total_deductions' => (float) $ps->total_deductions,
                    'net_salary' => (float) $ps->net_salary,
                    'employer_contributions' => (float) $ps->employer_contributions,
                ]),
                'totals' => [
                    'gross' => (float) $payRun->payslips->sum('gross_salary'),
                    'net' => (float) $payRun->payslips->sum('net_salary'),
                    'deductions' => (float) $payRun->payslips->sum('total_deductions'),
                    'employer' => (float) $payRun->payslips->sum('employer_contributions'),
                ],
                'accounting_entry' => $accountingEntry,
                'history' => $history,
            ],
            'initialTab' => $request->query('tab', 'overview'),
        ]);
    }

    /* ===== ACTIONS ===== */
    public function store(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'reference' => 'required|string|max:50',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'payment_date' => 'nullable|date',
        ]);
        if (PayRun::where('company_id', $company->id)->where('period_start', $data['period_start'])->exists()) {
            return back()->withErrors(['period_start' => 'Une période existe déjà pour ces dates.']);
        }
        $payRun = PayRun::create(array_merge($data, ['company_id' => $company->id, 'status' => 'draft']));
        activity()->performedOn($payRun)->causedBy(auth()->user())->log('Création période de paie');
        return redirect()->route('payroll.show', $payRun->id)->with('success', 'Période créée. Lancez le calcul !');
    }

    public function calculate(Request $request, PayRun $payRun)
    {
        if ($payRun->company_id !== $this->company($request)->id || $payRun->is_locked) abort(403);
        try { app(PayrollEngine::class)->calculatePayRun($payRun); return back()->with('success', 'Calcul terminé !'); }
        catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function validateRun(Request $request, PayRun $payRun)
    {
        if ($payRun->company_id !== $this->company($request)->id || $payRun->is_locked) abort(403);
        if ($payRun->status !== 'calculated') return back()->with('error', 'La paie doit être calculée avant validation.');
        $payRun->update(['status' => 'validated', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        activity()->performedOn($payRun)->causedBy(auth()->user())->log('Validation période de paie');
        return back()->with('success', 'Période validée.');
    }

    public function postToAccounting(Request $request, PayRun $payRun)
    {
        if ($payRun->company_id !== $this->company($request)->id) abort(403);
        try { app(PayrollAccountingService::class)->postPayRunToAccounting($payRun); return back()->with('success', 'Écriture comptable générée !'); }
        catch (\Exception $e) { return back()->with('error', $e->getMessage()); }
    }

    public function lock(Request $request, PayRun $payRun)
    {
        if ($payRun->company_id !== $this->company($request)->id) abort(403);
        if (!$payRun->accounting_entry_id) return back()->with('error', 'La paie doit être comptabilisée avant verrouillage.');
        $payRun->update(['status' => 'locked', 'is_locked' => true, 'locked_by' => auth()->id(), 'locked_at' => now()]);
        activity()->performedOn($payRun)->causedBy(auth()->user())->log('Verrouillage période de paie');
        return back()->with('success', 'Période verrouillée.');
    }

    public function payslipPdf(Payslip $payslip)
    {
        $payslip->load(['company', 'employee.department', 'employee.position', 'payRun', 'items']);
        $pdf = Pdf::loadView('payroll.payslip-pdf', ['payslip' => $payslip]);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'bulletin_' . ($payslip->employee->matricule ?? $payslip->id) . '_' . now()->format('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }

    /** Aperçu du bulletin dans le navigateur (sans téléchargement) */
    public function payslipView(\App\Modules\Payroll\Models\Payslip $payslip)
    {
        $company = request()->attributes->get('company') ?? \App\Models\Company::first();
        if ($payslip->company_id !== $company->id) abort(403);

        $payslip->load(['company', 'employee.department', 'employee.position', 'payRun', 'items']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payroll.payslip-pdf', ['payslip' => $payslip]);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'bulletin_' . ($payslip->slip_number ?? $payslip->id) . '_' . now()->format('YmdHis') . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
    /** Suppression d'un bulletin */
    public function payslipDestroy(\App\Modules\Payroll\Models\Payslip $payslip)
    {
        $company = request()->attributes->get('company') ?? \App\Models\Company::first();
        if ($payslip->company_id !== $company->id) abort(403);

        \Illuminate\Support\Facades\DB::table('payslip_items')->where('payslip_id', $payslip->id)->delete();
        $payslip->delete();

        return back()->with('success', 'Bulletin supprimé.');
    }
}
