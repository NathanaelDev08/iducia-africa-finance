<?php
namespace App\Modules\Purchasing\Models;
use App\Models\Company;
use App\Modules\Accounting\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $fillable = ['company_id','supplier_id','purchase_invoice_id','reference','payment_date','payment_method','amount','accounting_entry_id','notes'];
    protected $casts = ['payment_date'=>'date','amount'=>'decimal:2'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function purchaseInvoice(): BelongsTo { return $this->belongsTo(PurchaseInvoice::class); }
    public function accountingEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class, 'accounting_entry_id'); }
}
