<?php
namespace App\Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\JournalEntry;
use App\Modules\Accounting\Models\JournalItem;
use App\Modules\Hr\Models\Employee;
use App\Modules\Purchasing\Models\Supplier;
use App\Modules\Sales\Models\Client;
use App\Modules\System\Models\ExchangeRate;
use App\Modules\System\Services\AlertService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SystemController extends Controller
{
    protected function company(Request $request): Company
    { return $request->attributes->get('company') ?? Company::first(); }

    /* ===== MULTI-DEVISES ===== */
    public function currencies(Request $request)
    {
        $rates = ExchangeRate::where(fn($q)=>$q->whereNull('company_id')->orWhere('company_id',$this->company($request)->id))
            ->orderByDesc('effective_from')->get()
            ->map(fn($r)=>['id'=>$r->id,'currency_code'=>$r->currency_code,'currency_name'=>$r->currency_name,'rate_to_base'=>(float)$r->rate_to_base,'effective_from'=>$r->effective_from->toDateString(),'is_active'=>(bool)$r->is_active]);
        return Inertia::render('System/Currencies', ['rates'=>$rates]);
    }
    public function storeRate(Request $request)
    {
        $d=$request->validate(['currency_code'=>'required|string|max:10','currency_name'=>'nullable|string','rate_to_base'=>'required|numeric|min:0','effective_from'=>'required|date']);
        ExchangeRate::create(array_merge($d,['company_id'=>$this->company($request)->id,'is_active'=>true]));
        return back()->with('success','Taux ajouté.');
    }
    public function destroyRate(Request $request, ExchangeRate $rate){ $rate->delete(); return back()->with('success','Taux supprimé.'); }

    /* ===== NOTIFICATIONS / ALERTES ===== */
    public function notifications(Request $request)
    {
        $alerts = app(AlertService::class)->build($this->company($request));
        $high = collect($alerts)->where('severity','high')->count();
        return Inertia::render('System/Notifications', ['alerts'=>$alerts,'high_count'=>$high]);
    }

    /* ===== RECHERCHE GLOBALE (JSON) ===== */
    public function search(Request $request)
    {
        $q = $request->query('q'); $company = $this->company($request);
        if (strlen($q) < 2) return response()->json([]);
        $like = "%$q%"; $results = [];

        Employee::where('company_id',$company->id)->where(fn($w)=>$w->where('first_name','ilike',$like)->orWhere('last_name','ilike',$like)->orWhere('matricule','ilike',$like))->limit(5)->get()
            ->each(fn($e)=>$results[]=['group'=>'Employés','label'=>$e->matricule.' · '.$e->full_name,'link'=>route('hr.employees.show',$e->id)]);
        Client::where('company_id',$company->id)->where(fn($w)=>$w->where('name','ilike',$like)->orWhere('code','ilike',$like))->limit(5)->get()
            ->each(fn($c)=>$results[]=['group'=>'Clients','label'=>$c->code.' · '.$c->name,'link'=>route('sales.index',['tab'=>'clients'])]);
        Supplier::where('company_id',$company->id)->where(fn($w)=>$w->where('name','ilike',$like)->orWhere('code','ilike',$like))->limit(5)->get()
            ->each(fn($s)=>$results[]=['group'=>'Fournisseurs','label'=>$s->code.' · '.$s->name,'link'=>route('purchasing.index',['tab'=>'suppliers'])]);
        Account::where('company_id',$company->id)->where(fn($w)=>$w->where('number','ilike',$like)->orWhere('name','ilike',$like))->limit(5)->get()
            ->each(fn($a)=>$results[]=['group'=>'Comptes','label'=>$a->number.' · '.$a->name,'link'=>route('accounting.index',['tab'=>'accounts'])]);

        return response()->json($results);
    }

    /* ===== IMPORTS ===== */
    public function importIndex(Request $request)
    {
        return Inertia::render('System/Import', ['lastImport'=>session('lastImport')]);
    }

    public function importEmployees(Request $request)
    {
        $request->validate(['file'=>'required|file|mimes:csv,txt']);
        $company = $this->company($request);
        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle); // first_name,last_name,email,phone,hire_date
        $created = 0; $errors = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) { $errors++; continue; }
            try {
                $n = Employee::where('company_id',$company->id)->withTrashed()->count() + 1;
                Employee::create([
                    'company_id'=>$company->id,
                    'matricule'=>'EMP-'.now()->format('Y').'-'.str_pad($n,4,'0',STR_PAD_LEFT),
                    'first_name'=>$row[0]??'','last_name'=>$row[1]??'','email'=>$row[2]??null,'phone'=>$row[3]??null,
                    'hire_date'=>$row[4]??now()->toDateString(),'status'=>'active',
                ]);
                $created++;
            } catch (\Throwable $e) { $errors++; }
        }
        fclose($handle);
        return back()->with('lastImport', ['type'=>'employés','created'=>$created,'errors'=>$errors])
            ->with('success', "$created employé(s) importé(s), $errors erreur(s).");
    }

    public function importJournal(Request $request)
    {
        $request->validate(['file'=>'required|file|mimes:csv,txt']);
        $company = $this->company($request);
        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle); // entry_date,journal_code,reference,description,account_number,debit,credit
        $groups = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 7) continue;
            $groups[$row[2]][] = $row;
        }
        fclose($handle);
        $created = 0;
        foreach ($groups as $ref => $rows) {
            $first = $rows[0];
            $journal = Journal::where('company_id',$company->id)->where('code',$first[1])->first();
            if (!$journal) continue;
            $entry = JournalEntry::create(['company_id'=>$company->id,'journal_id'=>$journal->id,'entry_date'=>$first[0],'reference'=>$ref,'description'=>$first[3],'status'=>'draft']);
            foreach ($rows as $r) {
                $account = Account::where('company_id',$company->id)->where('number',$r[4])->first();
                if (!$account) continue;
                JournalItem::create(['journal_entry_id'=>$entry->id,'account_id'=>$account->id,'debit'=>(float)$r[5],'credit'=>(float)$r[6],'description'=>$r[3]]);
            }
            $created++;
        }
        return back()->with('success', "$created écriture(s) importée(s) en brouillon.");
    }

    /**
     * Affiche la page de recherche globale
     */
    public function searchPage(Request $request)
    {
        return Inertia::render('System/Search');
    }

    /**
     * API JSON pour l'autocomplétion de recherche
     */
    public function searchJson(Request $request)
    {
        return $this->search($request);
    }
}
