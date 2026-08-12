<?php

namespace App\Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\ChartAccount;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\FiscalYear;
use App\Modules\Accounting\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AccountingController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:accounting');
    }

    protected function company(Request $request): Company
    {
        return $request->attributes->get('company') ?? Company::first();
    }

    public function index(Request $request)
    {
        $company = $this->company($request);
        $chart = ChartAccount::where('company_id', $company->id)->first();

        $accounts = $chart ? Account::where('chart_account_id', $chart->id)
            ->orderBy('number')->get()
            ->map(fn ($a) => [
                'id' => $a->id, 'number' => $a->number, 'name' => $a->name,
                'class_number' => $a->class_number, 'type' => $a->type,
                'is_active' => (bool) $a->is_active,
            ]) : collect();

        $journals = Journal::where('company_id', $company->id)
            ->orderBy('code')->get()
            ->map(fn ($j) => [
                'id' => $j->id, 'code' => $j->code, 'name' => $j->name,
                'type' => $j->type, 'is_active' => (bool) $j->is_active,
            ]);

        $fiscalYears = FiscalYear::where('company_id', $company->id)
            ->orderByDesc('start_date')->get()
            ->map(fn ($fy) => [
                'id' => $fy->id, 'name' => $fy->name,
                'start_date' => $fy->start_date->toDateString(),
                'end_date' => $fy->end_date->toDateString(),
                'status' => $fy->status,
            ]);

        $periods = AccountingPeriod::where('company_id', $company->id)
            ->with('fiscalYear')->orderByDesc('start_date')->get()
            ->map(fn ($p) => [
                'id' => $p->id, 'name' => $p->name,
                'start_date' => $p->start_date->toDateString(),
                'end_date' => $p->end_date->toDateString(),
                'status' => $p->status,
                'fiscal_year_name' => $p->fiscalYear->name ?? '—',
            ]);

        return Inertia::render('Accounting/Index', [
            'accounts' => $accounts,
            'journals' => $journals,
            'fiscalYears' => $fiscalYears,
            'periods' => $periods,
            'chartAccount' => $chart ? ['id' => $chart->id, 'name' => $chart->name] : null,
            'initialTab' => $request->query('tab', 'accounts'),
        ]);
    }

    // CRUD Comptes
    public function storeAccount(Request $request)
    {
        $company = $this->company($request);
        $chart = ChartAccount::firstOrCreate(['company_id' => $company->id], ['name' => 'Plan SYSCOHADA', 'standard' => 'SYSCOHADA', 'is_active' => true]);
        
        $data = $request->validate(['number' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('accounts')->where(function ($q) use ($request) { $q->where('company_id', $this->company($request)->id); })], 'name' => 'required|string|max:255', 'class_number' => 'required|integer|between:1,9', 'type' => 'required|string']);
        Account::create(array_merge($data, ['chart_account_id' => $chart->id, 'is_active' => true]));
        return back()->with('success', 'Compte créé.');
    }

    public function updateAccount(Request $request, Account $account)
    {
        $data = $request->validate(['number' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('accounts')->where(function ($q) use ($request) { $q->where('company_id', $this->company($request)->id); })], 'name' => 'required|string|max:255', 'is_active' => 'boolean']);
        $account->update($data);
        return back()->with('success', 'Compte mis à jour.');
    }

    public function destroyAccount(Request $request, Account $account)
    {
        $account->delete();
        return back()->with('success', 'Compte supprimé.');
    }

    // CRUD Journaux
    public function storeJournal(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:10', \Illuminate\Validation\Rule::unique('journals')->where('company_id', $this->company($request)->id)], 'name' => 'required|string|max:255', 'type' => 'required|string']);
        Journal::create(array_merge($data, ['company_id' => $this->company($request)->id, 'is_active' => true]));
        return back()->with('success', 'Journal créé.');
    }

    public function updateJournal(Request $request, Journal $journal)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:10', \Illuminate\Validation\Rule::unique('journals')->where('company_id', $this->company($request)->id)], 'name' => 'required|string|max:255', 'is_active' => 'boolean']);
        $journal->update($data);
        return back()->with('success', 'Journal mis à jour.');
    }

    public function destroyJournal(Request $request, Journal $journal)
    {
        $journal->delete();
        return back()->with('success', 'Journal supprimé.');
    }

    // CRUD Exercices
    public function storeFiscalYear(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'start_date' => 'required|date', 'end_date' => 'required|date|after:start_date']);
        $fy = FiscalYear::create(array_merge($data, ['company_id' => $this->company($request)->id, 'status' => 'open']));
        
        // Créer automatiquement les 12 périodes mensuelles
        $start = \Carbon\Carbon::parse($data['start_date']);
        for ($i = 0; $i < 12; $i++) {
            $periodStart = $start->copy()->addMonths($i)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();
            AccountingPeriod::create([
                'company_id' => $fy->company_id,
                'fiscal_year_id' => $fy->id,
                'name' => $periodStart->format('F Y'),
                'start_date' => $periodStart,
                'end_date' => $periodEnd,
                'status' => 'open',
            ]);
        }
        return back()->with('success', 'Exercice créé avec 12 périodes.');
    }

    public function closeFiscalYear(Request $request, FiscalYear $fiscalYear)
    {
        $fiscalYear->update(['status' => 'closed']);
        $fiscalYear->periods()->update(['status' => 'closed']);
        return back()->with('success', 'Exercice clôturé.');
    }

    // CRUD Périodes
    public function closePeriod(Request $request, AccountingPeriod $period)
    {
        $period->update(['status' => 'closed']);
        return back()->with('success', 'Période clôturée.');
    }

    public function reopenPeriod(Request $request, AccountingPeriod $period)
    {
        $period->update(['status' => 'open']);
        return back()->with('success', 'Période rouverte.');
    }
}
