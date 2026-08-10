<?php
namespace App\Modules\Purchasing\Models;
use App\Models\Company;
use App\Modules\Accounting\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model
{
    protected $fillable = ['company_id','supplier_id','purchase_order_id','reference','supplier_invoice_number','invoice_date','due_date','status','total_ht','total_tax','total_ttc','amount_paid','accounting_entry_id','notes'];
    protected $casts = ['invoice_date'=>'date','due_date'=>'date','total_ht'=>'decimal:2','total_tax'=>'decimal:2','total_ttc'=>'decimal:2','amount_paid'=>'decimal:2'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseInvoiceItem::class); }
    public function accountingEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class, 'accounting_entry_id'); }
    public function payments(): HasMany { return $this->hasMany(SupplierPayment::class); }

    public function remainingAmount(): float
    {
        return (float) $this->total_ttc - (float) $this->amount_paid;
    }
}
