<?php
namespace App\Modules\Sales\Models;
use App\Models\Company;
use App\Modules\Accounting\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CustomerPayment extends Model
{
    protected $fillable = ['company_id','client_id','sales_invoice_id','reference','payment_date','payment_method','amount','accounting_entry_id','notes'];
    protected $casts = ['payment_date'=>'date','amount'=>'decimal:2'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function salesInvoice(): BelongsTo { return $this->belongsTo(SalesInvoice::class); }
    public function accountingEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class, 'accounting_entry_id'); }
}
