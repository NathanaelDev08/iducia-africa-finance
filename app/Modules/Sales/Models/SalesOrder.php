<?php
namespace App\Modules\Sales\Models;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class SalesOrder extends Model
{
    protected $fillable = ['company_id','client_id','reference','order_date','validity_date','status','total_ht','total_tax','total_ttc','notes'];
    protected $casts = ['order_date'=>'date','validity_date'=>'date','total_ht'=>'decimal:2','total_tax'=>'decimal:2','total_ttc'=>'decimal:2'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function items(): HasMany { return $this->hasMany(SalesOrderItem::class); }
}
