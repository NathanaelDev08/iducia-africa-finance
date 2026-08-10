<?php
namespace App\Modules\Sales\Services;

use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\JournalEntry;
use App\Modules\Accounting\Models\JournalItem;
use App\Modules\Sales\Models\CustomerPayment;
use App\Modules\Sales\Models\SalesInvoice;

class SalesAccountingService
{
    /**
     * Comptabilise une facture de vente :
     * 411 (client) au débit
     * 70x (produit) au crédit
     * 443 (TVA collectée) au crédit
     */
    public function postInvoice(SalesInvoice $invoice): JournalEntry
    {
        if ($invoice->accounting_entry_id) throw new \Exception('Facture déjà comptabilisée.');
        $company = $invoice->company;

        $journal = Journal::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'VTES'],
            ['name' => 'Ventes', 'type' => 'sale']
        );

        $defaultRevenue = Account::where('company_id', $company->id)->where('number', 'like', '701%')->first()
            ?? Account::where('company_id', $company->id)->where('class_number', 7)->first();

        $vatAccount = Account::where('company_id', $company->id)->where('number', 'like', '443%')->first();

        $clientAccountNumber = $invoice->client->account_number ?: '411' . str_pad($invoice->client_id, 5, '0', STR_PAD_LEFT);
        $clientAccount = Account::firstOrCreate(
            ['company_id' => $company->id, 'number' => $clientAccountNumber],
            ['name' => 'Client ' . $invoice->client->name, 'class_number' => 4, 'type' => 'customer']
        );

        $entry = JournalEntry::create([
            'company_id' => $company->id, 'journal_id' => $journal->id,
            'entry_date' => $invoice->invoice_date,
            'reference' => 'VTES-' . $invoice->reference,
            'description' => 'Facture ' . $invoice->reference . ' - ' . $invoice->client->name,
            'status' => 'draft', 'source_type' => SalesInvoice::class, 'source_id' => $invoice->id,
        ]);

        foreach ($invoice->items as $item) {
            $account = $item->account ?? $defaultRevenue;
            if ($account && (float) $item->total_ht > 0) {
                JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $account->id,
                    'debit' => 0, 'credit' => $item->total_ht, 'description' => $item->description]);
            }
        }

        if ($vatAccount && (float) $invoice->total_tax > 0) {
            JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $vatAccount->id,
                'debit' => 0, 'credit' => $invoice->total_tax, 'description' => 'TVA collectée']);
        }

        JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $clientAccount->id,
            'debit' => $invoice->total_ttc, 'credit' => 0, 'description' => $invoice->client->name]);

        $entry->validateAndPost();
        $invoice->update(['accounting_entry_id' => $entry->id, 'status' => 'validated']);
        return $entry;
    }

    /**
     * Comptabilise un encaissement :
     * 521 (banque) au débit
     * 411 (client) au crédit
     */
    public function postPayment(CustomerPayment $payment): JournalEntry
    {
        if ($payment->accounting_entry_id) throw new \Exception('Encaissement déjà comptabilisé.');
        $company = $payment->company;

        $journal = Journal::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'BQ'],
            ['name' => 'Banque', 'type' => 'bank']
        );

        $clientAccountNumber = $payment->client->account_number ?: '411' . str_pad($payment->client_id, 5, '0', STR_PAD_LEFT);
        $clientAccount = Account::firstOrCreate(
            ['company_id' => $company->id, 'number' => $clientAccountNumber],
            ['name' => 'Client ' . $payment->client->name, 'class_number' => 4, 'type' => 'customer']
        );

        $bankAccount = Account::where('company_id', $company->id)->where('number', 'like', '521%')->first()
            ?? Account::where('company_id', $company->id)->where('type', 'bank')->first();
        if (!$bankAccount) throw new \Exception('Aucun compte bancaire (521) configuré.');

        $entry = JournalEntry::create([
            'company_id' => $company->id, 'journal_id' => $journal->id,
            'entry_date' => $payment->payment_date,
            'reference' => 'ENC-' . $payment->reference,
            'description' => 'Encaissement ' . $payment->client->name,
            'status' => 'draft', 'source_type' => CustomerPayment::class, 'source_id' => $payment->id,
        ]);

        JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $bankAccount->id,
            'debit' => $payment->amount, 'credit' => 0, 'description' => 'Encaissement ' . $payment->reference]);

        JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $clientAccount->id,
            'debit' => 0, 'credit' => $payment->amount, 'description' => $payment->client->name]);

        $entry->validateAndPost();
        $payment->update(['accounting_entry_id' => $entry->id]);

        if ($payment->sales_invoice_id) {
            $invoice = $payment->salesInvoice;
            $totalPaid = $invoice->payments()->sum('amount');
            $invoice->update(['amount_paid' => $totalPaid,
                'status' => $totalPaid >= (float) $invoice->total_ttc ? 'paid' : 'validated']);
        }
        return $entry;
    }
}
