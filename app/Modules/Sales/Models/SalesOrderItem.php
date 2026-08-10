<?php
namespace App\Modules\Sales\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SalesOrderItem extends Model
{
    protected $fillable = ['sales_order_id','description','quantity','unit_price','tax_rate','total_ht','total_tax','total_ttc'];
    protected $casts = ['quantity'=>'decimal:3','unit_price'=>'decimal:2','tax_rate'=>'decimal:2','total_ht'=>'decimal:2','total_tax'=>'decimal:2','total_ttc'=>'decimal:2'];
    public function salesOrder(): BelongsTo { return $this->belongsTo(SalesOrder::class); }
}
