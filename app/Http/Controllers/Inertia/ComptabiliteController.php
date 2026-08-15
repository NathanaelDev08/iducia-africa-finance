<?php
namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreAccountingEntryRequest;
use App\Modules\Accounting\Models\AccountingEntry;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\ChartAccount;
use App\Modules\Accounting\Models\FiscalYear;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\JournalEntry;
use App\Modules\Accounting\Models\JournalItem;
use App\Modules\Accounting\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ComptabiliteController extends Controller
{
    public function ecritures(Request $request)
    {
        $this->authorize('viewAny', AccountingEntry::class);
        $company = app()->bound('current_company') ? app('current_company') : null;

        $entries = $company ? JournalEntry::where('company_id', $company->id)
            ->with(['journal', 'items'])
            ->latest('entry_date')->latest('id')->take(50)->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'entry_date' => $e->entry_date->toDateString(),
                'reference' => $e->reference,
                'description' => $e->description,
                'journal' => $e->journal?->code,
                'status' => $e->status,
                'total_debit' => (float) $e->items->sum('debit'),
                'total_credit' => (float) $e->items->sum('credit'),
            ]) : collect();

        $chart = $company ? ChartAccount::where('company_id', $company->id)->first() : null;

        $accounts = $chart ? Account::where('chart_account_id', $chart->id)
            ->orderBy('number')->get()
            ->map(fn ($a) => [
                'id' => $a->id, 'number' => $a->number, 'name' => $a->name,
                'class_number' => $a->class_number, 'type' => $a->type,
                'is_active' => (bool) $a->is_active,
            ]) : collect();

        $journals = $company ? Journal::where('company_id', $company->id)
            ->orderBy('code')->get()
            ->map(fn ($j) => [
                'id' => $j->id, 'code' => $j->code, 'name' => $j->name,
                'type' => $j->type, 'is_active' => (bool) $j->is_active,
            ]) : collect();

        $fiscalYears = $company ? FiscalYear::where('company_id', $company->id)
            ->orderByDesc('start_date')->get()
            ->map(fn ($fy) => [
                'id' => $fy->id, 'name' => $fy->name,
                'start_date' => $fy->start_date->toDateString(),
                'end_date' => $fy->end_date->toDateString(),
                'status' => $fy->status,
            ]) : collect();

        $periods = $company ? Period::where('company_id', $company->id)
            ->orderByDesc('start_date')->get()
            ->map(fn ($p) => [
                'id' => $p->id, 'name' => $p->name,
                'start_date' => $p->start_date->toDateString(),
                'end_date' => $p->end_date->toDateString(),
                'status' => $p->status,
            ]) : collect();

        return Inertia::render('Accounting/Index', [
            'accounts' => $accounts,
            'journals' => $journals,
            'fiscalYears' => $fiscalYears,
            'periods' => $periods,
            'entries' => $entries,
            'chartAccount' => $chart ? ['id' => $chart->id, 'name' => $chart->name] : null,
            'initialTab' => $request->query('tab', 'ecritures'),
        ]);
    }

    public function createEcriture(Request $request)
    {
        $this->authorize('create', AccountingEntry::class);
        $company = app()->bound('current_company') ? app('current_company') : null;

        $journals = $company ? Journal::where('company_id', $company->id)->active()->get() : collect();
        $accounts = $company ? Account::where('company_id', $company->id)->active()->orderBy('number')->get() : collect();

        return Inertia::render('Accounting/CreateEcriture', [
            'journals' => $journals,
            'accounts' => $accounts,
            'activeTab' => 'ecritures',
        ]);
    }

    public function storeEcriture(StoreAccountingEntryRequest $request)
    {
        $company = app()->bound('current_company') ? app('current_company') : null;

        if (!$company) {
            return back()->withErrors(['error' => 'Aucune entreprise active sélectionnée.']);
        }

        $data = $request->validated();

        $entry = DB::transaction(function () use ($company, $data) {
            $entry = JournalEntry::create([
                'company_id' => $company->id,
                'journal_id' => $data['journal_id'],
                'reference' => $data['reference'] ?? null,
                'entry_date' => $data['entry_date'],
                'description' => $data['description'],
                'status' => 'draft',
            ]);

            foreach ($data['lines'] as $line) {
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ]);
            }

            return $entry;
        });

        if ($request->boolean('validate_immediately')) {
            try {
                $entry->validateAndPost();
            } catch (\Exception $e) {
                return back()->withErrors(['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('accounting.index')
            ->with('success', 'Écriture comptable créée avec succès.');
    }

    public function validateEcriture(Request $request, JournalEntry $entry)
    {
        $this->authorizeEntryCompany($entry);

        try {
            $entry->validateAndPost();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Écriture validée.');
    }

    public function reverseEcriture(Request $request, JournalEntry $entry)
    {
        $this->authorizeEntryCompany($entry);

        $data = $request->validate(['reason' => 'required|string|max:500']);

        if ($entry->status !== 'posted') {
            return back()->with('error', 'Seules les écritures validées peuvent être contre-passées.');
        }

        DB::transaction(function () use ($entry, $data) {
            $reversal = JournalEntry::create([
                'company_id' => $entry->company_id,
                'journal_id' => $entry->journal_id,
                'reference' => 'EXT-' . ($entry->reference ?? $entry->id),
                'entry_date' => now()->toDateString(),
                'description' => "Contre-passation de {$entry->description} : {$data['reason']}",
                'status' => 'draft',
                'source_type' => JournalEntry::class,
                'source_id' => $entry->id,
            ]);

            foreach ($entry->items as $line) {
                JournalItem::create([
                    'journal_entry_id' => $reversal->id,
                    'account_id' => $line->account_id,
                    'description' => $line->description,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                ]);
            }

            $reversal->validateAndPost();
            $entry->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Écriture contre-passée avec succès.');
    }

    protected function authorizeEntryCompany(JournalEntry $entry): void
    {
        $company = app()->bound('current_company') ? app('current_company') : null;

        if (!$company || $entry->company_id !== $company->id) {
            abort(403, "Cette écriture n'appartient pas à l'entreprise active.");
        }
    }

    public function planComptable(Request $request)
    {
        $company = app()->bound('current_company') ? app('current_company') : null;
        $accounts = $company ? Account::where('company_id', $company->id)
            ->orderBy('number')->get() : collect();
        return Inertia::render('Accounting/PlanComptable', ['accounts' => $accounts, 'activeTab' => 'plan']);
    }

    public function journaux(Request $request)
    {
        $company = app()->bound('current_company') ? app('current_company') : null;
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
