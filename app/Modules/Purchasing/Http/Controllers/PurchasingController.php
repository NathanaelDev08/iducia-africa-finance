<?php
namespace App\Modules\Purchasing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Accounting\Models\Account;
use App\Modules\Purchasing\Models\{Supplier, PurchaseOrder, PurchaseOrderItem, PurchaseInvoice, PurchaseInvoiceItem, SupplierPayment};
use App\Modules\Purchasing\Services\PurchasingAccountingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PurchasingController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:purchasing');
    }

    protected function company(Request $request): Company
    {
        return $request->attributes->get('company') ?? Company::first();
    }

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

        $suppliers = Supplier::where('company_id', $company->id)->orderBy('name')->get()
            ->map(fn ($s) => ['id'=>$s->id,'code'=>$s->code,'name'=>$s->name,'contact_name'=>$s->contact_name,'email'=>$s->email,'phone'=>$s->phone,'tax_number'=>$s->tax_number,'account_number'=>$s->account_number,'is_active'=>(bool)$s->is_active]);

        $orders = PurchaseOrder::where('company_id', $company->id)->with('supplier')->orderByDesc('order_date')->get()
            ->map(fn ($o) => ['id'=>$o->id,'reference'=>$o->reference,'supplier'=>['id'=>$o->supplier->id,'name'=>$o->supplier->name],'order_date'=>$o->order_date->toDateString(),'expected_date'=>$o->expected_date?->toDateString(),'status'=>$o->status,'total_ttc'=>(float)$o->total_ttc]);

        $invoices = PurchaseInvoice::where('company_id', $company->id)->with('supplier')->orderByDesc('invoice_date')->get()
            ->map(fn ($i) => ['id'=>$i->id,'reference'=>$i->reference,'supplier_invoice_number'=>$i->supplier_invoice_number,'supplier'=>['id'=>$i->supplier->id,'name'=>$i->supplier->name],'invoice_date'=>$i->invoice_date->toDateString(),'due_date'=>$i->due_date?->toDateString(),'status'=>$i->status,'total_ttc'=>(float)$i->total_ttc,'amount_paid'=>(float)$i->amount_paid,'remaining'=>(float)$i->total_ttc-(float)$i->amount_paid,'is_posted'=>!is_null($i->accounting_entry_id)]);

        $payments = SupplierPayment::where('company_id', $company->id)->with(['supplier','purchaseInvoice'])->orderByDesc('payment_date')->get()
            ->map(fn ($p) => ['id'=>$p->id,'reference'=>$p->reference,'supplier'=>['id'=>$p->supplier->id,'name'=>$p->supplier->name],'invoice_reference'=>$p->purchaseInvoice->reference ?? '—','payment_date'=>$p->payment_date->toDateString(),'payment_method'=>$p->payment_method,'amount'=>(float)$p->amount,'is_posted'=>!is_null($p->accounting_entry_id)]);

        $expenseAccounts = Account::where('company_id', $company->id)->whereIn('class_number', [6])->where('is_active', true)->orderBy('number')->get(['id','number','name']);

        $stats = [
            'suppliers_count' => $suppliers->count(),
            'orders_count' => $orders->count(),
            'invoices_count' => $invoices->count(),
            'unpaid_total' => $invoices->sum('remaining'),
        ];

        return Inertia::render('Purchasing/Index', [
            'suppliers' => $suppliers,
            'orders' => $orders,
            'invoices' => $invoices,
            'payments' => $payments,
            'expenseAccounts' => $expenseAccounts,
            'stats' => $stats,
            'initialTab' => $request->query('tab', 'suppliers'),
        ]);
    }

    /* ===== SUPPLIERS ===== */
    public function storeSupplier(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('suppliers')->where('company_id', $this->company($request)->id)],'name'=>'required|string|max:255','contact_name'=>'nullable|string','email'=>'nullable|email','phone'=>'nullable|string','address'=>'nullable|string','tax_number'=>'nullable|string','account_number'=>'nullable|string']);
        Supplier::create(array_merge($data, ['company_id' => $this->company($request)->id]));
        return back()->with('success', 'Fournisseur créé.');
    }
    public function updateSupplier(Request $request, Supplier $supplier)
    {
        if ($supplier->company_id !== $this->company($request)->id) abort(403);
        $data = $request->validate(['code' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('suppliers')->where('company_id', $this->company($request)->id)],'name'=>'required|string|max:255','contact_name'=>'nullable|string','email'=>'nullable|email','phone'=>'nullable|string','address'=>'nullable|string','tax_number'=>'nullable|string','account_number'=>'nullable|string']);
        $supplier->update($data);
        return back()->with('success', 'Fournisseur mis à jour.');
    }
    public function destroySupplier(Request $request, Supplier $supplier)
    {
        if ($supplier->company_id !== $this->company($request)->id) abort(403);
        if ($supplier->invoices()->count() > 0) return back()->with('error', 'Impossible : ce fournisseur a des factures.');
        $supplier->delete();
        return back()->with('success', 'Fournisseur supprimé.');
    }

    /* ===== ORDERS ===== */
    public function storeOrder(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('company_id', $company->id)],
            'expected_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
        ]);
        $order = PurchaseOrder::create([
            'company_id' => $company->id,
            'supplier_id' => $data['supplier_id'],
            'reference' => $this->nextRef('BC', 'purchase_orders'),
            'order_date' => now(),
            'expected_date' => $data['expected_date'] ?? null,
            'status' => 'draft',
        ]);
        $this->saveOrderItems($order, $data['items']);
        return back()->with('success', 'Bon de commande ' . $order->reference . ' créé.');
    }
    public function updateOrderStatus(Request $request, PurchaseOrder $order)
    {
        if ($order->company_id !== $this->company($request)->id) abort(403);
        $data = $request->validate(['status' => 'required|in:draft,sent,received,cancelled']);
        $order->update($data);
        return back()->with('success', 'Statut mis à jour.');
    }
    public function destroyOrder(Request $request, PurchaseOrder $order)
    {
        if ($order->company_id !== $this->company($request)->id) abort(403);
        if ($order->status !== 'draft') return back()->with('error', 'Seuls les brouillons peuvent être supprimés.');
        $order->delete();
        return back()->with('success', 'Commande supprimée.');
    }

    private function saveOrderItems(PurchaseOrder $order, array $items): void
    {
        $order->items()->delete();
        $totalHt = $totalTax = 0;
        foreach ($items as $it) {
            $ht = (float) $it['quantity'] * (float) $it['unit_price'];
            $tax = $ht * (float) $it['tax_rate'] / 100;
            $totalHt += $ht; $totalTax += $tax;
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'description' => $it['description'],
                'quantity' => $it['quantity'],
                'unit_price' => $it['unit_price'],
                'tax_rate' => $it['tax_rate'],
                'total_ht' => $ht,
                'total_tax' => $tax,
                'total_ttc' => $ht + $tax,
            ]);
        }
        $order->update(['total_ht' => $totalHt, 'total_tax' => $totalTax, 'total_ttc' => $totalHt + $totalTax]);
    }

    /* ===== INVOICES ===== */
    public function storeInvoice(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('company_id', $company->id)],
            'supplier_invoice_number' => 'nullable|string',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.account_id' => ['nullable', Rule::exists('accounts', 'id')->where('company_id', $company->id)],
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
        ]);
        $invoice = PurchaseInvoice::create([
            'company_id' => $company->id,
            'supplier_id' => $data['supplier_id'],
            'reference' => $this->nextRef('FAC', 'purchase_invoices'),
            'supplier_invoice_number' => $data['supplier_invoice_number'] ?? null,
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'] ?? null,
            'status' => 'draft',
        ]);
        $this->saveInvoiceItems($invoice, $data['items']);
        return back()->with('success', 'Facture ' . $invoice->reference . ' créée.');
    }

    private function saveInvoiceItems(PurchaseInvoice $invoice, array $items): void
    {
        $invoice->items()->delete();
        $totalHt = $totalTax = 0;
        foreach ($items as $it) {
            $ht = (float) $it['quantity'] * (float) $it['unit_price'];
            $tax = $ht * (float) $it['tax_rate'] / 100;
            $totalHt += $ht; $totalTax += $tax;
            PurchaseInvoiceItem::create([
                'purchase_invoice_id' => $invoice->id,
                'account_id' => $it['account_id'] ?? null,
                'description' => $it['description'],
                'quantity' => $it['quantity'],
                'unit_price' => $it['unit_price'],
                'tax_rate' => $it['tax_rate'],
                'total_ht' => $ht,
                'total_tax' => $tax,
                'total_ttc' => $ht + $tax,
            ]);
        }
        $invoice->update(['total_ht' => $totalHt, 'total_tax' => $totalTax, 'total_ttc' => $totalHt + $totalTax]);
    }

    public function postInvoice(Request $request, PurchaseInvoice $invoice)
    {
        if ($invoice->company_id !== $this->company($request)->id) abort(403);
        try {
            app(PurchasingAccountingService::class)->postInvoice($invoice);
            return back()->with('success', 'Facture comptabilisée (OD générée).');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyInvoice(Request $request, PurchaseInvoice $invoice)
    {
        if ($invoice->company_id !== $this->company($request)->id) abort(403);
        if ($invoice->accounting_entry_id) return back()->with('error', 'Impossible : facture déjà comptabilisée.');
        if ($invoice->status !== 'draft') return back()->with('error', 'Seuls les brouillons peuvent être supprimés.');
        $invoice->delete();
        return back()->with('success', 'Facture supprimée.');
    }

    /* ===== PAYMENTS ===== */
    public function storePayment(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('company_id', $company->id)],
            'purchase_invoice_id' => ['nullable', Rule::exists('purchase_invoices', 'id')->where('company_id', $company->id)],
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank,cash,check',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);
        if ($data['purchase_invoice_id']) {
            $invoice = PurchaseInvoice::find($data['purchase_invoice_id']);
            if ((float) $data['amount'] > $invoice->remainingAmount()) {
                return back()->with('error', 'Montant supérieur au reste à payer (' . number_format($invoice->remainingAmount(), 0, ',', ' ') . ' FCFA).');
            }
        }
        SupplierPayment::create([
            'company_id' => $company->id,
            'supplier_id' => $data['supplier_id'],
            'purchase_invoice_id' => $data['purchase_invoice_id'] ?? null,
            'reference' => $this->nextRef('REG', 'supplier_payments'),
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'amount' => $data['amount'],
            'notes' => $data['notes'] ?? null,
        ]);
        return back()->with('success', 'Paiement enregistré.');
    }

    public function postPayment(Request $request, SupplierPayment $payment)
    {
        if ($payment->company_id !== $this->company($request)->id) abort(403);
        try {
            app(PurchasingAccountingService::class)->postPayment($payment);
            return back()->with('success', 'Paiement comptabilisé.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyPayment(Request $request, SupplierPayment $payment)
    {
        if ($payment->company_id !== $this->company($request)->id) abort(403);
        if ($payment->accounting_entry_id) return back()->with('error', 'Impossible : paiement déjà comptabilisé.');
        $payment->delete();
        return back()->with('success', 'Paiement supprimé.');
    }
}
