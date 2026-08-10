<?php
namespace App\Modules\System\Models;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ExchangeRate extends Model
{
    protected $fillable = ['company_id','currency_code','currency_name','rate_to_base','effective_from','is_active'];
    protected $casts = ['effective_from'=>'date','rate_to_base'=>'decimal:6','is_active'=>'boolean'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
