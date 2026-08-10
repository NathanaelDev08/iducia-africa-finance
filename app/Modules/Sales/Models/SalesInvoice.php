<?php
namespace App\Modules\Sales\Models;
use App\Models\Company;
use App\Modules\Accounting\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class SalesInvoice extends Model
{
    protected $fillable = ['company_id','client_id','sales_order_id','reference','invoice_date','due_date','status','total_ht','total_tax','total_ttc','amount_paid','accounting_entry_id','notes'];
    protected $casts = ['invoice_date'=>'date','due_date'=>'date','total_ht'=>'decimal:2','total_tax'=>'decimal:2','total_ttc'=>'decimal:2','amount_paid'=>'decimal:2'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function salesOrder(): BelongsTo { return $this->belongsTo(SalesOrder::class); }
    public function items(): HasMany { return $this->hasMany(SalesInvoiceItem::class); }
    public function accountingEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class, 'accounting_entry_id'); }
    public function payments(): HasMany { return $this->hasMany(CustomerPayment::class); }
    public function remainingAmount(): float { return (float)$this->total_ttc - (float)$this->amount_paid; }
}
