<?php
namespace App\Modules\Treasury\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\JournalItem;
use App\Modules\Treasury\Models\BankStatement;
use App\Modules\Treasury\Models\BankStatementLine;
use App\Modules\Treasury\Models\CashRegister;
use App\Modules\Treasury\Models\CashTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TreasuryController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:treasury');
    }

    protected function company(Request $request): Company
    { return $request->attributes->get('company') ?? Company::first(); }

    public function index(Request $request)
    {
        $company = $this->company($request);

        $statements = BankStatement::where('company_id', $company->id)
            ->withCount(['lines', 'lines as matched_count' => fn ($q) => $q->where('status', 'matched')])
            ->orderByDesc('period_start')->get()
            ->map(fn ($s) => [
                'id'=>$s->id,
                'account'=>$s->account?->number ?? '521',
                'period_start'=>$s->period_start->toDateString(),
                'period_end'=>$s->period_end->toDateString(),
                'opening_balance'=>(float)$s->opening_balance,'closing_balance'=>(float)$s->closing_balance,
                'status'=>$s->status,'lines_count'=>$s->lines_count,'matched_count'=>$s->matched_count,
            ]);

        $selectedId = $request->query('statement') ?? ($statements->first()['id'] ?? null);
        $selected = $statements->firstWhere('id', (int) $selectedId);

        $lines = collect(); $unmatchedItems = collect();
        if ($selected) {
            $lines = BankStatementLine::where('bank_statement_id', $selected['id'])->orderBy('transaction_date')->get()
                ->map(fn ($l) => [
                    'id'=>$l->id,'transaction_date'=>$l->transaction_date->toDateString(),'reference'=>$l->reference,
                    'description'=>$l->description,'debit'=>(float)$l->debit,'credit'=>(float)$l->credit,
                    'net'=>(float)$l->debit-(float)$l->credit,'status'=>$l->status,'is_matched'=>!is_null($l->matched_journal_item_id),
                ]);

            $matchedIds = BankStatementLine::whereNotNull('matched_journal_item_id')->pluck('matched_journal_item_id');
            $unmatchedItems = JournalItem::join('journal_entries','journal_items.journal_entry_id','=','journal_entries.id')
                ->join('accounts','journal_items.account_id','=','accounts.id')
                ->where('journal_entries.company_id',$company->id)
                ->where('journal_entries.status','posted')
                ->where('accounts.number','like','52%')
                ->whereNotIn('journal_items.id',$matchedIds)
                ->select('journal_items.id','journal_entries.entry_date','journal_entries.reference','journal_items.description','journal_items.debit','journal_items.credit')
                ->orderBy('journal_entries.entry_date')->limit(300)->get()
                ->map(fn ($i) => ['id'=>$i->id,'entry_date'=>is_string($i->entry_date) ? $i->entry_date : $i->entry_date->toDateString(),'reference'=>$i->reference,'description'=>$i->description,'debit'=>(float)$i->debit,'credit'=>(float)$i->credit,'net'=>(float)$i->debit-(float)$i->credit]);
        }

        $bankAccounts = Account::where('company_id', $company->id)->where('number','like','52%')->get(['id','number','name']);

        $cashRegisters = CashRegister::where('company_id', $company->id)
            ->withCount('transactions')
            ->orderByDesc('period_start')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'period_start' => $r->period_start->toDateString(),
                'period_end' => $r->period_end->toDateString(),
                'opening_balance' => (float) $r->opening_balance,
                'closing_balance' => (float) $r->closing_balance,
                'status' => $r->status,
                'transactions_count' => $r->transactions_count,
            ]);

        $selectedCashId = $request->query('register') ?? ($cashRegisters->first()['id'] ?? null);
        $selectedCash = $cashRegisters->firstWhere('id', (int) $selectedCashId);

        $cashTransactions = collect();
        if ($selectedCash) {
            $cashTransactions = CashTransaction::where('cash_register_id', $selectedCash['id'])
                ->orderBy('transaction_date')
                ->get()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'transaction_date' => $t->transaction_date->toDateString(),
                    'reference' => $t->reference,
                    'description' => $t->description,
                    'type' => $t->type,
                    'amount' => (float) $t->amount,
                ]);
        }

        return Inertia::render('Treasury/Index', [
            'statements'=>$statements,'selected'=>$selected,'lines'=>$lines,'unmatchedItems'=>$unmatchedItems,
            'bankAccounts'=>$bankAccounts,
            'cashRegisters'=>$cashRegisters,'selectedCash'=>$selectedCash,'cashTransactions'=>$cashTransactions,
            'initialTab'=>$request->query('tab','statements'),
        ]);
    }

    public function storeStatement(Request $request)
    {
        $d = $request->validate(['account_id'=>'nullable|exists:accounts,id','period_start'=>'required|date','period_end'=>'required|date|after_or_equal:period_start','opening_balance'=>'nullable|numeric','closing_balance'=>'nullable|numeric']);
        BankStatement::create(['company_id'=>$this->company($request)->id,'account_id'=>$d['account_id']??null,'period_start'=>$d['period_start'],'period_end'=>$d['period_end'],'opening_balance'=>$d['opening_balance']??0,'closing_balance'=>$d['closing_balance']??0,'status'=>'draft']);
        return back()->with('success', 'Relevé créé.');
    }

    public function destroyStatement(Request $request, BankStatement $statement)
    {
        $statement->lines()->update(['matched_journal_item_id'=>null,'status'=>'unmatched']);
        $statement->delete();
        return back()->with('success', 'Relevé supprimé.');
    }

    public function storeLine(Request $request)
    {
        $d = $request->validate(['bank_statement_id'=>'required|exists:bank_statements,id','transaction_date'=>'required|date','reference'=>'nullable|string','description'=>'nullable|string','debit'=>'nullable|numeric|min:0','credit'=>'nullable|numeric|min:0']);
        BankStatementLine::create(['bank_statement_id'=>$d['bank_statement_id'],'transaction_date'=>$d['transaction_date'],'reference'=>$d['reference']??null,'description'=>$d['description']??null,'debit'=>$d['debit']??0,'credit'=>$d['credit']??0,'status'=>'unmatched']);
        return back()->with('success', 'Ligne ajoutée.');
    }

    public function matchLine(Request $request, BankStatementLine $line)
    {
        $d = $request->validate(['journal_item_id'=>'required|exists:journal_items,id']);
        $line->update(['matched_journal_item_id'=>$d['journal_item_id'],'status'=>'matched']);
        return back()->with('success', 'Ligne rapprochée.');
    }

    public function unmatchLine(Request $request, BankStatementLine $line)
    {
        $line->update(['matched_journal_item_id'=>null,'status'=>'unmatched']);
        return back()->with('success', 'Rapprochement annulé.');
    }

    public function destroyLine(Request $request, BankStatementLine $line)
    {
        $line->delete();
        return back()->with('success', 'Ligne supprimée.');
    }

    public function reconcile(Request $request, BankStatement $statement)
    {
        $unmatched = $statement->lines()->where('status','unmatched')->count();
        if ($unmatched > 0) return back()->with('error', $unmatched . ' ligne(s) non rapprochée(s).');
        $statement->update(['status'=>'reconciled']);
        return back()->with('success', 'Relevé rapproché.');
    }
}
