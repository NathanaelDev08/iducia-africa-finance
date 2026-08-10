<?php
namespace App\Modules\Purchasing\Models;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['company_id','code','name','contact_name','email','phone','address','tax_number','account_number','payment_terms','is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function purchaseOrders(): HasMany { return $this->hasMany(PurchaseOrder::class); }
    public function invoices(): HasMany { return $this->hasMany(PurchaseInvoice::class); }
    public function payments(): HasMany { return $this->hasMany(SupplierPayment::class); }
}
