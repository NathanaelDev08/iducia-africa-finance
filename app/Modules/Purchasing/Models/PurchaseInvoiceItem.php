<?php
namespace App\Modules\Purchasing\Models;
use App\Modules\Accounting\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoiceItem extends Model
{
    protected $fillable = ['purchase_invoice_id','account_id','description','quantity','unit_price','tax_rate','total_ht','total_tax','total_ttc'];
    protected $casts = ['quantity'=>'decimal:3','unit_price'=>'decimal:2','tax_rate'=>'decimal:2','total_ht'=>'decimal:2','total_tax'=>'decimal:2','total_ttc'=>'decimal:2'];
    public function purchaseInvoice(): BelongsTo { return $this->belongsTo(PurchaseInvoice::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
}
