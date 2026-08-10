<?php
namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreAccountingEntryRequest;
use App\Modules\Accounting\Models\AccountingEntry;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\Period;
use App\Modules\Accounting\Services\EntryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComptabiliteController extends Controller
{
    public function __construct(protected EntryService $entryService)
    {
    }

    public function ecritures(Request $request)
    {
        $this->authorize('viewAny', AccountingEntry::class);
        $company = app()->bound('currentCompany') ? app('currentCompany') : null;
        $entries = $company ? AccountingEntry::where('company_id', $company->id)
            ->with(['journal', 'period'])
            ->latest('entry_date')->take(50)->get() : collect();
        return Inertia::render('Accounting/Index', ['entries' => $entries, 'activeTab' => 'ecritures']);
    }

    public function createEcriture(Request $request)
    {
        $this->authorize('create', AccountingEntry::class);
        $company = app()->bound('currentCompany') ? app('currentCompany') : null;

        $journals = $company ? Journal::where('company_id', $company->id)->active()->get() : collect();
        $periods = $company ? Period::where('company_id', $company->id)->where('status', 'open')->orderByDesc('start_date')->get() : collect();
        $accounts = $company ? Account::where('company_id', $company->id)->active()->orderBy('number')->get() : collect();

        return Inertia::render('Accounting/CreateEcriture', [
            'journals' => $journals,
            'periods' => $periods,
            'accounts' => $accounts,
            'activeTab' => 'ecritures',
        ]);
    }

    public function storeEcriture(StoreAccountingEntryRequest $request)
    {
        $company = app()->bound('currentCompany') ? app('currentCompany') : null;

        if (!$company) {
            return back()->withErrors(['error' => 'Aucune entreprise active sélectionnée.']);
        }

        $data = $request->validated();

        $entry = $this->entryService->createDraft($company, [
            'journal_id' => $data['journal_id'],
            'period_id' => $data['period_id'],
            'reference' => $data['reference'] ?? null,
            'entry_date' => $data['entry_date'],
            'description' => $data['description'],
        ], $data['lines']);

        // Valider immédiatement si demandé
        if ($request->input('validate_immediately', false)) {
            try {
                $this->entryService->validate($entry, $request->user());
            } catch (\Exception $e) {
                return back()->withErrors(['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('accounting.index')
            ->with('success', 'Écriture comptable créée avec succès.');
    }

    public function planComptable(Request $request)
    {
        $company = app()->bound('currentCompany') ? app('currentCompany') : null;
        $accounts = $company ? Account::where('company_id', $company->id)
            ->orderBy('number')->get() : collect();
        return Inertia::render('Accounting/PlanComptable', ['accounts' => $accounts, 'activeTab' => 'plan']);
    }

    public function journaux(Request $request)
    {
        $company = app()->bound('currentCompany') ? app('currentCompany') : null;
        $journals = $company ? Journal::where('company_id', $company->id)->get() : collect();
        return Inertia::render('Accounting/Journaux', ['journals' => $journals, 'activeTab' => 'journaux']);
    }

    public function balance(Request $request)
    {
        return Inertia::render('Accounting/Balance', ['activeTab' => 'balance']);
    }

    public function grandLivre(Request $request)
    {
        return Inertia::render('Accounting/GrandLivre', ['activeTab' => 'grand-livre']);
    }
}
