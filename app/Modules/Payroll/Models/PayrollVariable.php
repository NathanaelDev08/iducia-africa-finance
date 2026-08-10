<?php

namespace App\Modules\Payroll\Models;

use App\Models\Company;
use App\Modules\Hr\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollVariable extends Model
{
    protected $fillable = [
        'company_id', 'employee_id', 'pay_run_id',
        'pay_item_id', 'amount', 'quantity',
        'effective_date', 'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'decimal:2',
        'effective_date' => 'date',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function payRun(): BelongsTo { return $this->belongsTo(PayRun::class); }
    public function payItem(): BelongsTo { return $this->belongsTo(PayItem::class); }
}
