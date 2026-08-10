<?php
namespace App\Modules\Sales\Models;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Client extends Model
{
    protected $fillable = ['company_id','code','name','contact_name','email','phone','address','tax_number','account_number','payment_terms','is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function salesOrders(): HasMany { return $this->hasMany(SalesOrder::class); }
    public function invoices(): HasMany { return $this->hasMany(SalesInvoice::class); }
    public function payments(): HasMany { return $this->hasMany(CustomerPayment::class); }
}
