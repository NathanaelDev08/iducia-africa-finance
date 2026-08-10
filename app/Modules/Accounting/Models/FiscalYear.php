<?php
namespace App\Modules\Accounting\Models;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    protected $table = 'fiscal_years';
    protected $fillable = ['company_id','name','start_date','end_date','status','notes'];
    protected $casts = ['start_date'=>'date','end_date'=>'date'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function periods(): HasMany { return $this->hasMany(AccountingPeriod::class, 'fiscal_year_id'); }
}
