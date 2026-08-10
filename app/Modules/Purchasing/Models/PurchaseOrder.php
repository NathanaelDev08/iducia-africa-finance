<?php
namespace App\Modules\Purchasing\Models;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = ['company_id','supplier_id','reference','order_date','expected_date','status','total_ht','total_tax','total_ttc','notes'];
    protected $casts = ['order_date'=>'date','expected_date'=>'date','total_ht'=>'decimal:2','total_tax'=>'decimal:2','total_ttc'=>'decimal:2'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseOrderItem::class); }
}
