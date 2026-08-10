<?php
namespace App\Modules\Assets\Models;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Asset extends Model
{
    protected $fillable = ['company_id','code','name','acquisition_date','acquisition_cost','residual_value','useful_life_months','depreciation_method','account_asset','account_depreciation','account_expense','status'];
    protected $casts = ['acquisition_date'=>'date','acquisition_cost'=>'decimal:2','residual_value'=>'decimal:2'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function depreciations(): HasMany { return $this->hasMany(AssetDepreciation::class); }
    public function monthlyDepreciation(): float
    {
        $months = max(1, (int) $this->useful_life_months);
        return round(((float) $this->acquisition_cost - (float) $this->residual_value) / $months, 2);
    }
}
