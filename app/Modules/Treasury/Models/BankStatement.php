<?php
namespace App\Modules\Treasury\Models;
use App\Models\Company;
use App\Modules\Accounting\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class BankStatement extends Model
{
    protected $fillable = ['company_id','account_id','period_start','period_end','opening_balance','closing_balance','status'];
    protected $casts = ['period_start'=>'date','period_end'=>'date','opening_balance'=>'decimal:2','closing_balance'=>'decimal:2'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
    public function lines(): HasMany { return $this->hasMany(BankStatementLine::class); }
}
