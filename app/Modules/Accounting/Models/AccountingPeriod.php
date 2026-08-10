<?php
namespace App\Modules\Accounting\Models;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingPeriod extends Model
{
    protected $table = 'periods';
    protected $fillable = ['company_id','fiscal_year_id','name','start_date','end_date','status'];
    protected $casts = ['start_date'=>'date','end_date'=>'date'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function fiscalYear(): BelongsTo { return $this->belongsTo(FiscalYear::class, 'fiscal_year_id'); }
}
