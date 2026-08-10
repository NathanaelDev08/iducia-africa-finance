<?php
namespace App\Modules\Payroll\Models;
use App\Models\Company;
use App\Modules\Hr\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model
{
    protected $fillable = [
        'company_id', 'pay_run_id', 'employee_id', 'status',
        'period_start', 'period_end', 'base_salary', 'gross_salary',
        'total_earnings', 'total_deductions', 'net_salary', 'employer_contributions',
    ];
    protected $casts = [
        'period_start' => 'date', 'period_end' => 'date',
        'base_salary' => 'decimal:2', 'gross_salary' => 'decimal:2',
        'total_earnings' => 'decimal:2', 'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2', 'employer_contributions' => 'decimal:2',
    ];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function payRun(): BelongsTo { return $this->belongsTo(PayRun::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function items(): HasMany { return $this->hasMany(PayslipItem::class); }
}
