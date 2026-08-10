<?php
namespace App\Modules\Purchasing\Services;

use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\JournalEntry;
use App\Modules\Accounting\Models\JournalItem;
use App\Modules\Purchasing\Models\PurchaseInvoice;
use App\Modules\Purchasing\Models\SupplierPayment;

class PurchasingAccountingService
{
    /**
     * Comptabilise une facture d'achat :
     * 6xx (charge) au débit
     * 445 (TVA déductible) au débit
     * 401 (fournisseur) au crédit
     */
    public function postInvoice(PurchaseInvoice $invoice): JournalEntry
    {
        if ($invoice->accounting_entry_id) {
            throw new \Exception('Cette facture est déjà comptabilisée.');
        }

        $company = $invoice->company;

        // Récupérer les comptes
        $journal = Journal::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'ACH'],
            ['name' => 'Achats', 'type' => 'purchase']
        );

        $defaultExpense = Account::where('company_id', $company->id)->where('number', 'like', '601%')->first()
            ?? Account::where('company_id', $company->id)->where('class_number', 6)->first();

        $vatAccount = Account::where('company_id', $company->id)->where('number', 'like', '445%')->first();

        $supplierAccountNumber = $invoice->supplier->account_number ?: '401' . str_pad($invoice->supplier_id, 5, '0', STR_PAD_LEFT);
        $supplierAccount = Account::firstOrCreate(
            ['company_id' => $company->id, 'number' => $supplierAccountNumber],
            ['name' => 'Fournisseur ' . $invoice->supplier->name, 'class_number' => 4, 'type' => 'supplier']
        );

        // Créer l'écriture
        $entry = JournalEntry::create([
            'company_id' => $company->id,
            'journal_id' => $journal->id,
            'entry_date' => $invoice->invoice_date,
            'reference' => 'ACH-' . $invoice->reference,
            'description' => 'Facture ' . ($invoice->supplier_invoice_number ?: $invoice->reference) . ' - ' . $invoice->supplier->name,
            'status' => 'draft',
            'source_type' => PurchaseInvoice::class,
            'source_id' => $invoice->id,
        ]);

        // Lignes par article (compte de charge)
        foreach ($invoice->items as $item) {
            $account = $item->account ?? $defaultExpense;
            if ($account && (float) $item->total_ht > 0) {
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $account->id,
                    'debit' => $item->total_ht,
                    'credit' => 0,
                    'description' => $item->description,
                ]);
            }
        }

        // Ligne TVA
        if ($vatAccount && (float) $invoice->total_tax > 0) {
            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $vatAccount->id,
                'debit' => $invoice->total_tax,
                'credit' => 0,
                'description' => 'TVA déductible',
            ]);
        }

        // Ligne fournisseur (créditeur)
        JournalItem::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $supplierAccount->id,
            'debit' => 0,
            'credit' => $invoice->total_ttc,
            'description' => $invoice->supplier->name,
        ]);

        $entry->validateAndPost();
        $invoice->update(['accounting_entry_id' => $entry->id, 'status' => 'validated']);

        return $entry;
    }

    /**
     * Comptabilise un paiement fournisseur :
     * 401 (fournisseur) au débit
     * 521 (banque) au crédit
     */
    public function postPayment(SupplierPayment $payment): JournalEntry
    {
        if ($payment->accounting_entry_id) {
            throw new \Exception('Ce paiement est déjà comptabilisé.');
        }

        $company = $payment->company;

        $journal = Journal::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'BQ'],
            ['name' => 'Banque', 'type' => 'bank']
        );

        $supplierAccountNumber = $payment->supplier->account_number ?: '401' . str_pad($payment->supplier_id, 5, '0', STR_PAD_LEFT);
        $supplierAccount = Account::firstOrCreate(
            ['company_id' => $company->id, 'number' => $supplierAccountNumber],
            ['name' => 'Fournisseur ' . $payment->supplier->name, 'class_number' => 4, 'type' => 'supplier']
        );

        $bankAccount = Account::where('company_id', $company->id)->where('number', 'like', '521%')->first()
            ?? Account::where('company_id', $company->id)->where('type', 'bank')->first();

        if (!$bankAccount) {
            throw new \Exception('Aucun compte bancaire (521) configuré.');
        }

        $entry = JournalEntry::create([
            'company_id' => $company->id,
            'journal_id' => $journal->id,
            'entry_date' => $payment->payment_date,
            'reference' => 'REG-' . $payment->reference,
            'description' => 'Paiement ' . $payment->supplier->name,
            'status' => 'draft',
            'source_type' => SupplierPayment::class,
            'source_id' => $payment->id,
        ]);

        JournalItem::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $supplierAccount->id,
            'debit' => $payment->amount,
            'credit' => 0,
            'description' => $payment->supplier->name,
        ]);

        JournalItem::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $bankAccount->id,
            'debit' => 0,
            'credit' => $payment->amount,
            'description' => 'Paiement ' . $payment->reference,
        ]);

        $entry->validateAndPost();
        $payment->update(['accounting_entry_id' => $entry->id]);

        // Mettre à jour le montant payé de la facture liée
        if ($payment->purchase_invoice_id) {
            $invoice = $payment->purchaseInvoice;
            $totalPaid = $invoice->payments()->sum('amount');
            $invoice->update([
                'amount_paid' => $totalPaid,
                'status' => $totalPaid >= (float) $invoice->total_ttc ? 'paid' : 'validated',
            ]);
        }

        return $entry;
    }
}
