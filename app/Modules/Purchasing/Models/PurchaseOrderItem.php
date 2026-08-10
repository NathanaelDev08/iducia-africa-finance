<?php
namespace App\Modules\Purchasing\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id','description','quantity','unit_price','tax_rate','total_ht','total_tax','total_ttc'];
    protected $casts = ['quantity'=>'decimal:3','unit_price'=>'decimal:2','tax_rate'=>'decimal:2','total_ht'=>'decimal:2','total_tax'=>'decimal:2','total_ttc'=>'decimal:2'];
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
}
