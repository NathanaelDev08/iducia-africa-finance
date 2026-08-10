<?php
namespace App\Modules\System\Services;

use App\Modules\Hr\Models\EmployeeContract;
use App\Modules\Hr\Models\EmployeeDocument;
use App\Modules\Purchasing\Models\PurchaseInvoice;
use App\Modules\Sales\Models\SalesInvoice;
use App\Modules\Tax\Models\TaxDeclaration;

class AlertService
{
    public function build($company): array
    {
        $alerts = [];
        $today = now()->startOfDay();
        $in30 = $today->copy()->addDays(30);

        // Échéances fiscales
        TaxDeclaration::where('company_id', $company->id)->whereNotIn('status', ['paid'])
            ->where('due_date', '<=', $in30)->get()
            ->each(function ($d) use ($today, &$alerts) {
                $overdue = $d->due_date->lt($today);
                $alerts[] = ['type'=>'tax','severity'=>$overdue?'high':'medium',
                    'message'=>'Échéance ' . ($d->reference ?? $d->type) . ' (' . number_format((float)$d->tax_amount,0,',',' ') . ' FCFA) ' . ($overdue?'en retard':'le ' . $d->due_date->format('d/m/Y')),
                    'link'=>route('tax.index')];
            });

        // Documents expirant
        EmployeeDocument::whereHas('employee', fn($q)=>$q->where('company_id',$company->id))
            ->whereNotNull('expires_at')->where('expires_at','<=',$in30)->get()
            ->each(function ($d) use ($today, &$alerts) {
                $expired = $d->expires_at->lt($today);
                $alerts[] = ['type'=>'document','severity'=>$expired?'high':'low',
                    'message'=>'Document « ' . $d->name . ' » de ' . $d->employee->full_name . ' ' . ($expired?'expiré':'expire le ' . $d->expires_at->format('d/m/Y')),
                    'link'=>route('hr.index',['tab'=>'documents'])];
            });

        // Factures fournisseurs en retard
        PurchaseInvoice::where('company_id',$company->id)->whereNotIn('status',['paid','cancelled'])
            ->whereNotNull('due_date')->where('due_date','<',$today)->get()
            ->each(fn ($i) => $alerts[] = ['type'=>'supplier','severity'=>'medium',
                'message'=>'Facture fournisseur ' . $i->reference . ' (' . $i->supplier->name . ') en retard : ' . number_format($i->remainingAmount(),0,',',' ') . ' FCFA',
                'link'=>route('purchasing.index',['tab'=>'invoices'])]);

        // Factures clients en retard
        SalesInvoice::where('company_id',$company->id)->whereNotIn('status',['paid','cancelled'])
            ->whereNotNull('due_date')->where('due_date','<',$today)->get()
            ->each(fn ($i) => $alerts[] = ['type'=>'customer','severity'=>'medium',
                'message'=>'Facture client ' . $i->reference . ' (' . $i->client->name . ') en retard : ' . number_format($i->remainingAmount(),0,',',' ') . ' FCFA',
                'link'=>route('sales.index',['tab'=>'invoices'])]);

        // Contrats expirant
        EmployeeContract::where('company_id',$company->id)->where('status','active')
            ->whereNotNull('end_date')->where('end_date','<=',$in30)->get()
            ->each(fn ($c) => $alerts[] = ['type'=>'contract','severity'=>'low',
                'message'=>'Contrat de ' . $c->employee->full_name . ' expire le ' . $c->end_date->format('d/m/Y'),
                'link'=>route('hr.index',['tab'=>'contrats'])]);

        return $alerts;
    }
}
