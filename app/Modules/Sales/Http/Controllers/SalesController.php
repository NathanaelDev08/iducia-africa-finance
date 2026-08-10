<?php
namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Accounting\Models\Account;
use App\Modules\Sales\Models\{Client, SalesOrder, SalesOrderItem, SalesInvoice, SalesInvoiceItem, CustomerPayment};
use App\Modules\Sales\Services\SalesAccountingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SalesController extends Controller
{
    protected function company(Request $request): Company
    { return $request->attributes->get('company') ?? Company::first(); }

    private function nextRef(string $prefix, string $table): string
    {
        $year = now()->format('Y');
        $last = \DB::table($table)->where('reference', 'like', "$prefix-$year-%")->max('reference');
        $n = $last ? (int) substr($last, -4) + 1 : 1;
        return "$prefix-$year-" . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $company = $this->company($request);

        $clients = Client::where('company_id', $company->id)->orderBy('name')->get()
            ->map(fn ($c) => ['id'=>$c->id,'code'=>$c->code,'name'=>$c->name,'contact_name'=>$c->contact_name,'email'=>$c->email,'phone'=>$c->phone,'tax_number'=>$c->tax_number,'account_number'=>$c->account_number,'is_active'=>(bool)$c->is_active]);

        $orders = SalesOrder::where('company_id', $company->id)->with('client')->orderByDesc('order_date')->get()
            ->map(fn ($o) => ['id'=>$o->id,'reference'=>$o->reference,'client'=>['id'=>$o->client->id,'name'=>$o->client->name],'order_date'=>$o->order_date->toDateString(),'validity_date'=>$o->validity_date?->toDateString(),'status'=>$o->status,'total_ttc'=>(float)$o->total_ttc]);

        $invoices = SalesInvoice::where('company_id', $company->id)->with('client')->orderByDesc('invoice_date')->get()
            ->map(fn ($i) => ['id'=>$i->id,'reference'=>$i->reference,'client'=>['id'=>$i->client->id,'name'=>$i->client->name],'invoice_date'=>$i->invoice_date->toDateString(),'due_date'=>$i->due_date?->toDateString(),'status'=>$i->status,'total_ttc'=>(float)$i->total_ttc,'amount_paid'=>(float)$i->amount_paid,'remaining'=>(float)$i->total_ttc-(float)$i->amount_paid,'is_posted'=>!is_null($i->accounting_entry_id)]);

        $payments = CustomerPayment::where('company_id', $company->id)->with(['client','salesInvoice'])->orderByDesc('payment_date')->get()
            ->map(fn ($p) => ['id'=>$p->id,'reference'=>$p->reference,'client'=>['id'=>$p->client->id,'name'=>$p->client->name],'invoice_reference'=>$p->salesInvoice->reference ?? '—','payment_date'=>$p->payment_date->toDateString(),'payment_method'=>$p->payment_method,'amount'=>(float)$p->amount,'is_posted'=>!is_null($p->accounting_entry_id)]);

        $revenueAccounts = Account::where('company_id', $company->id)->where('class_number', 7)->where('is_active', true)->orderBy('number')->get(['id','number','name']);

        $stats = [
            'clients_count' => $clients->count(),
            'orders_count' => $orders->count(),
            'invoices_count' => $invoices->count(),
            'uncollected_total' => $invoices->sum('remaining'),
        ];

        return Inertia::render('Sales/Index', [
            'clients'=>$clients,'orders'=>$orders,'invoices'=>$invoices,'payments'=>$payments,
            'revenueAccounts'=>$revenueAccounts,'stats'=>$stats,
            'initialTab'=>$request->query('tab','clients'),
        ]);
    }

    /* CLIENTS */
    public function storeClient(Request $request){ $d=$request->validate(['code' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('clients')->where('company_id', $this->company($request)->id)],'name'=>'required|string|max:255','contact_name'=>'nullable|string','email'=>'nullable|email','phone'=>'nullable|string','address'=>'nullable|string','tax_number'=>'nullable|string','account_number'=>'nullable|string']); Client::create(array_merge($d,['company_id'=>$this->company($request)->id])); return back()->with('success','Client créé.'); }
    public function updateClient(Request $request, Client $client){ $d=$request->validate(['code' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('clients')->where('company_id', $this->company($request)->id)],'name'=>'required|string|max:255','contact_name'=>'nullable|string','email'=>'nullable|email','phone'=>'nullable|string','address'=>'nullable|string','tax_number'=>'nullable|string','account_number'=>'nullable|string']); $client->update($d); return back()->with('success','Client mis à jour.'); }
    public function destroyClient(Request $request, Client $client){ if($client->invoices()->count()>0) return back()->with('error','Impossible : ce client a des factures.'); $client->delete(); return back()->with('success','Client supprimé.'); }

    /* DEVIS */
    public function storeOrder(Request $request){
        $d=$request->validate(['client_id'=>'required|exists:clients,id','validity_date'=>'nullable|date','items'=>'required|array|min:1','items.*.description'=>'required|string','items.*.quantity'=>'required|numeric|min:0.01','items.*.unit_price'=>'required|numeric|min:0','items.*.tax_rate'=>'required|numeric|min:0']);
        $company=$this->company($request);
        $order=SalesOrder::create(['company_id'=>$company->id,'client_id'=>$d['client_id'],'reference'=>$this->nextRef('DEV','sales_orders'),'order_date'=>now(),'validity_date'=>$d['validity_date']??null,'status'=>'draft']);
        $this->saveOrderItems($order,$d['items']);
        return back()->with('success','Devis '.$order->reference.' créé.');
    }
    public function updateOrderStatus(Request $request, SalesOrder $order){ $d=$request->validate(['status'=>'required|in:draft,sent,accepted,refused,invoiced']); $order->update($d); return back()->with('success','Statut mis à jour.'); }
    public function destroyOrder(Request $request, SalesOrder $order){ if($order->status!=='draft') return back()->with('error','Seuls les brouillons peuvent être supprimés.'); $order->delete(); return back()->with('success','Devis supprimé.'); }
    private function saveOrderItems(SalesOrder $order, array $items): void {
        $order->items()->delete(); $ht=$tax=0;
        foreach($items as $it){ $h=(float)$it['quantity']*(float)$it['unit_price']; $t=$h*(float)$it['tax_rate']/100; $ht+=$h; $tax+=$t;
            SalesOrderItem::create(['sales_order_id'=>$order->id,'description'=>$it['description'],'quantity'=>$it['quantity'],'unit_price'=>$it['unit_price'],'tax_rate'=>$it['tax_rate'],'total_ht'=>$h,'total_tax'=>$t,'total_ttc'=>$h+$t]); }
        $order->update(['total_ht'=>$ht,'total_tax'=>$tax,'total_ttc'=>$ht+$tax]);
    }

    /* FACTURES */
    public function storeInvoice(Request $request){
        $d=$request->validate(['client_id'=>'required|exists:clients,id','invoice_date'=>'required|date','due_date'=>'nullable|date','items'=>'required|array|min:1','items.*.description'=>'required|string','items.*.account_id'=>'nullable|exists:accounts,id','items.*.quantity'=>'required|numeric|min:0.01','items.*.unit_price'=>'required|numeric|min:0','items.*.tax_rate'=>'required|numeric|min:0']);
        $company=$this->company($request);
        $invoice=SalesInvoice::create(['company_id'=>$company->id,'client_id'=>$d['client_id'],'reference'=>$this->nextRef('FV','sales_invoices'),'invoice_date'=>$d['invoice_date'],'due_date'=>$d['due_date']??null,'status'=>'draft']);
        $this->saveInvoiceItems($invoice,$d['items']);
        return back()->with('success','Facture '.$invoice->reference.' créée.');
    }
    public function postInvoice(Request $request, SalesInvoice $invoice){ try{ app(SalesAccountingService::class)->postInvoice($invoice); return back()->with('success','Facture comptabilisée.'); }catch(\Exception $e){ return back()->with('error',$e->getMessage()); } }
    public function destroyInvoice(Request $request, SalesInvoice $invoice){ if($invoice->accounting_entry_id) return back()->with('error','Facture déjà comptabilisée.'); if($invoice->status!=='draft') return back()->with('error','Seuls les brouillons peuvent être supprimés.'); $invoice->delete(); return back()->with('success','Facture supprimée.'); }
    private function saveInvoiceItems(SalesInvoice $invoice, array $items): void {
        $invoice->items()->delete(); $ht=$tax=0;
        foreach($items as $it){ $h=(float)$it['quantity']*(float)$it['unit_price']; $t=$h*(float)$it['tax_rate']/100; $ht+=$h; $tax+=$t;
            SalesInvoiceItem::create(['sales_invoice_id'=>$invoice->id,'account_id'=>$it['account_id']??null,'description'=>$it['description'],'quantity'=>$it['quantity'],'unit_price'=>$it['unit_price'],'tax_rate'=>$it['tax_rate'],'total_ht'=>$h,'total_tax'=>$t,'total_ttc'=>$h+$t]); }
        $invoice->update(['total_ht'=>$ht,'total_tax'=>$tax,'total_ttc'=>$ht+$tax]);
    }

    /* ENCAISSEMENTS */
    public function storePayment(Request $request){
        $d=$request->validate(['client_id'=>'required|exists:clients,id','sales_invoice_id'=>'nullable|exists:sales_invoices,id','payment_date'=>'required|date','payment_method'=>'required|in:bank,cash,check,mobile','amount'=>'required|numeric|min:0.01','notes'=>'nullable|string']);
        if($d['sales_invoice_id']){ $inv=SalesInvoice::find($d['sales_invoice_id']); if((float)$d['amount']>$inv->remainingAmount()) return back()->with('error','Montant supérieur au reste à payer.'); }
        CustomerPayment::create(['company_id'=>$this->company($request)->id,'client_id'=>$d['client_id'],'sales_invoice_id'=>$d['sales_invoice_id']??null,'reference'=>$this->nextRef('ENC','customer_payments'),'payment_date'=>$d['payment_date'],'payment_method'=>$d['payment_method'],'amount'=>$d['amount'],'notes'=>$d['notes']??null]);
        return back()->with('success','Encaissement enregistré.');
    }
    public function postPayment(Request $request, CustomerPayment $payment){ try{ app(SalesAccountingService::class)->postPayment($payment); return back()->with('success','Encaissement comptabilisé.'); }catch(\Exception $e){ return back()->with('error',$e->getMessage()); } }
    public function destroyPayment(Request $request, CustomerPayment $payment){ if($payment->accounting_entry_id) return back()->with('error','Encaissement déjà comptabilisé.'); $payment->delete(); return back()->with('success','Encaissement supprimé.'); }
}
