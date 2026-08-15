<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class DocumentController extends Controller
{
    protected function company(): ?Company
    {
        return request()->attributes->get('company') ?? Company::first();
    }

    protected function b64(?string $relative): ?string
    {
        if (!$relative) return null;
        $p = public_path($relative);
        if (!file_exists($p)) return null;
        $mime = str_ends_with(strtolower($p), '.png') ? 'image/png' : 'image/jpeg';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($p));
    }

    protected function output($pdf, string $filename, string $mode)
    {
        $pdf->setPaper('a4', 'portrait');
        return $mode === 'stream' ? $pdf->stream($filename) : $pdf->download($filename);
    }

    // ═══════════ FACTURE CLIENT / FOURNISSEUR ═══════════
    public function invoiceView($id) { return $this->invoice($id, 'stream', 'sale'); }
    public function invoicePdf($id)  { return $this->invoice($id, 'download', 'sale'); }
    public function purchaseView($id) { return $this->invoice($id, 'stream', 'purchase'); }
    public function purchasePdf($id)  { return $this->invoice($id, 'download', 'purchase'); }

    protected function invoice($id, string $mode, string $kind)
    {
        $company = $this->company();
        $table = $kind === 'sale' ? 'sales_invoices' : 'purchase_invoices';
        if (!Schema::hasTable($table)) abort(404, 'Table introuvable');

        $inv = DB::table($table)->where('id', $id)->first();
        if (!$inv || !$company || (int) ($inv->company_id ?? 0) !== (int) $company->id) {
            abort(404, 'Facture introuvable');
        }

        // Tiers (client ou fournisseur)
        $party = null;
        try {
            $ptable = $kind === 'sale' ? 'clients' : 'suppliers';
            $pid = $inv->client_id ?? $inv->supplier_id ?? null;
            if ($ptable && $pid && Schema::hasTable($ptable)) {
                $p = DB::table($ptable)->where('id', $pid)->first();
                if ($p) $party = [
                    'name' => $p->name ?? '—',
                    'contact' => $p->contact_name ?? null,
                    'address' => $p->address ?? null,
                    'phone' => $p->phone ?? null,
                    'email' => $p->email ?? null,
                    'tax_number' => $p->tax_number ?? null,
                    'code' => $p->code ?? null,
                ];
            }
        } catch (\Throwable $e) {}

        // Lignes de la facture
        $items = [];
        $cands = $kind === 'sale'
            ? [['sales_invoice_items', 'sales_invoice_id'], ['sales_invoice_items', 'invoice_id']]
            : [['purchase_invoice_items', 'purchase_invoice_id'], ['purchase_invoice_items', 'invoice_id']];
        foreach ($cands as $c) {
            if (Schema::hasTable($c[0]) && in_array($c[1], Schema::getColumnListing($c[0]))) {
                $items = DB::table($c[0])->where($c[1], $id)->get()->map(fn ($it) => [
                    'label' => $it->description ?? $it->label ?? $it->name ?? 'Prestation',
                    'qty' => (float) ($it->quantity ?? $it->qty ?? 1),
                    'pu' => (float) ($it->unit_price ?? $it->price ?? 0),
                    'total' => (float) ($it->total_ht ?? $it->amount ?? 0),
                    'tax' => (float) ($it->tax_rate ?? 0),
                ])->toArray();
                break;
            }
        }
        if (empty($items)) {
            $items = [[
                'label' => $kind === 'sale' ? 'Prestations / ventes' : 'Achats fournisseurs',
                'qty' => 1,
                'pu' => (float) ($inv->total_ht ?? 0),
                'total' => (float) ($inv->total_ht ?? 0),
                'tax' => 18,
            ]];
        }

        $doc = [
            'kind' => $kind,
            'title' => $kind === 'sale' ? 'FACTURE' : 'FACTURE FOURNISSEUR',
            'number' => $inv->reference ?? $inv->number ?? ('FAC-' . $id),
            'date' => $inv->invoice_date ?? $inv->issue_date ?? null,
            'due_date' => $inv->due_date ?? null,
            'status' => $inv->status ?? 'pending',
            'party' => $party,
            'items' => $items,
            'total_ht' => (float) ($inv->total_ht ?? 0),
            'total_tax' => (float) ($inv->total_tax ?? $inv->total_vat ?? 0),
            'total_ttc' => (float) ($inv->total_ttc ?? 0),
            'amount_paid' => (float) ($inv->amount_paid ?? 0),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.invoice-pdf', [
            'doc' => $doc,
            'company' => $company,
            'logo' => $this->b64('images/logo.png'),
            'companyLogo' => $this->b64($company->logo_path ?? null),
        ]);

        return $this->output($pdf, strtolower($doc['number']) . '.pdf', $mode);
    }

    // ═══════════ REÇUS DE PAIEMENT ═══════════
    public function receiptView($id) { return $this->receipt($id, 'stream', 'client'); }
    public function receiptPdf($id)  { return $this->receipt($id, 'download', 'client'); }
    public function supplierReceiptView($id) { return $this->receipt($id, 'stream', 'supplier'); }
    public function supplierReceiptPdf($id)  { return $this->receipt($id, 'download', 'supplier'); }

    protected function receipt($id, string $mode, string $source)
    {
        $company = $this->company();
        $table = $source === 'client' ? 'customer_payments' : (Schema::hasTable('supplier_payments') ? 'supplier_payments' : 'payments');
        if (!Schema::hasTable($table)) abort(404, 'Table paiements introuvable');

        $pay = DB::table($table)->where('id', $id)->first();
        if (!$pay || !$company || (int) ($pay->company_id ?? 0) !== (int) $company->id) {
            abort(404, 'Paiement introuvable');
        }

        // Facture liée + tiers
        $invoiceRef = null; $partyName = null; $restant = null;
        try {
            $invTable = $source === 'client' ? 'sales_invoices' : 'purchase_invoices';
            $invId = $pay->sales_invoice_id ?? $pay->invoice_id ?? $pay->purchase_invoice_id ?? null;
            if ($invId && Schema::hasTable($invTable)) {
                $inv = DB::table($invTable)->where('id', $invId)->first();
                if ($inv) {
                    $invoiceRef = $inv->reference ?? $inv->number ?? null;
                    $restant = (float) $inv->total_ttc - (float) $inv->amount_paid;
                    $pid = $inv->client_id ?? $inv->supplier_id ?? null;
                    $ptable = $source === 'client' ? 'clients' : 'suppliers';
                    if ($pid && Schema::hasTable($ptable)) {
                        $p = DB::table($ptable)->where('id', $pid)->first();
                        $partyName = $p->name ?? null;
                    }
                }
            }
            if (!$partyName) {
                $ptable = $source === 'client' ? 'clients' : 'suppliers';
                $pid = $pay->client_id ?? $pay->supplier_id ?? null;
                if ($pid && Schema::hasTable($ptable)) {
                    $p = DB::table($ptable)->where('id', $pid)->first();
                    $partyName = $p->name ?? null;
                }
            }
        } catch (\Throwable $e) {}

        $doc = [
            'source' => $source,
            'number' => $pay->reference ?? ('REC-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT)),
            'date' => $pay->payment_date ?? $pay->created_at ?? null,
            'amount' => (float) ($pay->amount ?? 0),
            'method' => $pay->payment_method ?? 'Virement bancaire',
            'party_name' => $partyName ?? '—',
            'invoice_ref' => $invoiceRef,
            'restant' => $restant,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.receipt-pdf', [
            'doc' => $doc,
            'company' => $company,
            'logo' => $this->b64('images/logo.png'),
            'companyLogo' => $this->b64($company->logo_path ?? null),
        ]);

        return $this->output($pdf, strtolower($doc['number']) . '.pdf', $mode);
    }

    // ═══════════ CENTRE DES DOCUMENTS (liste complète) ═══════════
    public function index(Request $request)
    {
        $company = $this->company();
        $cid = $company ? $company->id : 0;
        $docs = collect();

        try {
            if (Schema::hasTable('sales_invoices')) {
                foreach (DB::table('sales_invoices')->leftJoin('clients', 'sales_invoices.client_id', '=', 'clients.id')
                    ->where('sales_invoices.company_id', $cid)->orderByDesc('sales_invoices.id')->limit(500)
                    ->get(['sales_invoices.*', 'clients.name as party']) as $r) {
                    $docs->push(['id' => $r->id, 'type' => 'facture_vente', 'label' => 'Facture de vente',
                        'reference' => $r->reference ?? ('FV-' . $r->id), 'date' => $r->invoice_date ?? null,
                        'party' => $r->party ?? '—', 'total' => (float) ($r->total_ttc ?? 0), 'status' => $r->status ?? 'draft',
                        'view' => 'documents.invoice.view', 'pdf' => 'documents.invoice.pdf']);
                }
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('purchase_invoices')) {
                foreach (DB::table('purchase_invoices')->leftJoin('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
                    ->where('purchase_invoices.company_id', $cid)->orderByDesc('purchase_invoices.id')->limit(500)
                    ->get(['purchase_invoices.*', 'suppliers.name as party']) as $r) {
                    $docs->push(['id' => $r->id, 'type' => 'facture_achat', 'label' => "Facture d'achat",
                        'reference' => $r->reference ?? ('FA-' . $r->id), 'date' => $r->invoice_date ?? null,
                        'party' => $r->party ?? '—', 'total' => (float) ($r->total_ttc ?? 0), 'status' => $r->status ?? 'draft',
                        'view' => 'documents.purchase.view', 'pdf' => 'documents.purchase.pdf']);
                }
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('sales_orders')) {
                foreach (DB::table('sales_orders')->leftJoin('clients', 'sales_orders.client_id', '=', 'clients.id')
                    ->where('sales_orders.company_id', $cid)->orderByDesc('sales_orders.id')->limit(500)
                    ->get(['sales_orders.*', 'clients.name as party']) as $r) {
                    $docs->push(['id' => $r->id, 'type' => 'devis', 'label' => 'Devis',
                        'reference' => $r->reference ?? ('DEV-' . $r->id), 'date' => $r->order_date ?? null,
                        'party' => $r->party ?? '—', 'total' => (float) ($r->total_ttc ?? 0), 'status' => $r->status ?? 'draft',
                        'view' => 'documents.order.view', 'pdf' => 'documents.order.pdf']);
                }
            }
        } catch (\Throwable $e) {}

        try {
            $ot = Schema::hasTable('purchase_orders') ? 'purchase_orders' : (Schema::hasTable('purchasing_orders') ? 'purchasing_orders' : null);
            if ($ot) {
                foreach (DB::table($ot)->leftJoin('suppliers', $ot . '.supplier_id', '=', 'suppliers.id')
                    ->where($ot . '.company_id', $cid)->orderByDesc($ot . '.id')->limit(500)
                    ->get([$ot . '.*', 'suppliers.name as party']) as $r) {
                    $docs->push(['id' => $r->id, 'type' => 'commande', 'label' => 'Bon de commande',
                        'reference' => $r->reference ?? ('BC-' . $r->id), 'date' => $r->order_date ?? null,
                        'party' => $r->party ?? '—', 'total' => (float) ($r->total_ttc ?? 0), 'status' => $r->status ?? 'draft',
                        'view' => 'documents.purchase_order.view', 'pdf' => 'documents.purchase_order.pdf']);
                }
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('customer_payments')) {
                foreach (DB::table('customer_payments')->leftJoin('clients', 'customer_payments.client_id', '=', 'clients.id')
                    ->where('customer_payments.company_id', $cid)->orderByDesc('customer_payments.id')->limit(500)
                    ->get(['customer_payments.*', 'clients.name as party']) as $r) {
                    $docs->push(['id' => $r->id, 'type' => 'recu_client', 'label' => 'Reçu de paiement',
                        'reference' => $r->reference ?? ('REC-' . $r->id), 'date' => $r->payment_date ?? null,
                        'party' => $r->party ?? '—', 'total' => (float) ($r->amount ?? 0), 'status' => 'paid',
                        'view' => 'documents.receipt.view', 'pdf' => 'documents.receipt.pdf']);
                }
            }
        } catch (\Throwable $e) {}

        try {
            $pt = Schema::hasTable('supplier_payments') ? 'supplier_payments' : (Schema::hasTable('purchasing_payments') ? 'purchasing_payments' : null);
            if ($pt) {
                foreach (DB::table($pt)->leftJoin('suppliers', $pt . '.supplier_id', '=', 'suppliers.id')
                    ->where($pt . '.company_id', $cid)->orderByDesc($pt . '.id')->limit(500)
                    ->get([$pt . '.*', 'suppliers.name as party']) as $r) {
                    $docs->push(['id' => $r->id, 'type' => 'recu_fournisseur', 'label' => 'Reçu fournisseur',
                        'reference' => $r->reference ?? ('RECF-' . $r->id), 'date' => $r->payment_date ?? null,
                        'party' => $r->party ?? '—', 'total' => (float) ($r->amount ?? 0), 'status' => 'paid',
                        'view' => 'documents.supplier_receipt.view', 'pdf' => 'documents.supplier_receipt.pdf']);
                }
            }
        } catch (\Throwable $e) {}

        $docs = $docs->sortByDesc(fn ($d) => $d['date'] ?? '')->values();

        return Inertia::render('Documents/Index', [
            'documents' => $docs,
            'company' => ['name' => $company->name ?? '—', 'currency' => $company->currency ?? 'FCFA'],
        ]);
    }

    // ═══════════ DEVIS & BONS DE COMMANDE PDF ═══════════
    public function orderView($id) { return $this->order($id, 'stream', 'sale'); }
    public function orderPdf($id) { return $this->order($id, 'download', 'sale'); }
    public function purchaseOrderView($id) { return $this->order($id, 'stream', 'purchase'); }
    public function purchaseOrderPdf($id) { return $this->order($id, 'download', 'purchase'); }

    protected function order($id, string $mode, string $kind)
    {
        $company = $this->company();
        $table = $kind === 'sale' ? 'sales_orders' : (Schema::hasTable('purchase_orders') ? 'purchase_orders' : 'purchasing_orders');
        if (!Schema::hasTable($table)) abort(404, 'Table introuvable');

        $o = DB::table($table)->where('id', $id)->first();
        if (!$o || !$company || (int) ($o->company_id ?? 0) !== (int) $company->id) {
            abort(404);
        }

        $party = null;
        try {
            $pt = $kind === 'sale' ? 'clients' : 'suppliers';
            $pid = $o->client_id ?? $o->supplier_id ?? null;
            if ($pid && Schema::hasTable($pt)) {
                $p = DB::table($pt)->where('id', $pid)->first();
                if ($p) $party = ['name' => $p->name ?? '—', 'address' => $p->address ?? null, 'phone' => $p->phone ?? null, 'email' => $p->email ?? null, 'tax_number' => $p->tax_number ?? null];
            }
        } catch (\Throwable $e) {}

        $items = [];
        $cands = $kind === 'sale'
            ? [['sales_order_items', 'sales_order_id'], ['sales_order_items', 'order_id']]
            : [['purchase_order_items', 'purchase_order_id'], ['purchase_order_items', 'order_id'], ['purchase_order_lines', 'purchase_order_id']];
        foreach ($cands as $cand) {
            if (Schema::hasTable($cand[0]) && in_array($cand[1], Schema::getColumnListing($cand[0]))) {
                $items = DB::table($cand[0])->where($cand[1], $id)->get()->map(fn ($it) => [
                    'label' => $it->description ?? $it->label ?? $it->name ?? 'Article',
                    'qty' => (float) ($it->quantity ?? $it->qty ?? 1),
                    'pu' => (float) ($it->unit_price ?? $it->price ?? 0),
                    'total' => (float) ($it->total_ht ?? $it->amount ?? ((float) ($it->quantity ?? 1) * (float) ($it->unit_price ?? 0))),
                    'tax' => (float) ($it->tax_rate ?? 18),
                ])->toArray();
                break;
            }
        }

        $ht = (float) ($o->total_ht ?? array_sum(array_column($items, 'total')));
        $tax = (float) ($o->total_tax ?? $o->total_vat ?? round($ht * 0.18));
        $ttc = (float) ($o->total_ttc ?? ($ht + $tax));
        if (empty($items)) $items = [['label' => $kind === 'sale' ? 'Prestation proposée' : 'Articles commandés', 'qty' => 1, 'pu' => $ht, 'total' => $ht, 'tax' => 18]];

        $doc = [
            'kind' => $kind,
            'title' => $kind === 'sale' ? 'DEVIS' : 'BON DE COMMANDE',
            'number' => $o->reference ?? ('DEV-' . $id),
            'date' => $o->order_date ?? $o->created_at ?? null,
            'due_date' => null,
            'status' => $o->status ?? 'draft',
            'party' => $party,
            'items' => $items,
            'total_ht' => $ht, 'total_tax' => $tax, 'total_ttc' => $ttc,
            'amount_paid' => 0,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.invoice-pdf', [
            'doc' => $doc, 'company' => $company,
            'logo' => $this->b64('images/logo.png'),
            'companyLogo' => $this->b64($company->logo_path ?? null),
        ]);

        return $this->output($pdf, strtolower($doc['number']) . '.pdf', $mode);
    }
}
