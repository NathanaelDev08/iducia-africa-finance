<?php

namespace App\Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipLine extends Model
{
    protected $fillable = [
        'payslip_id', 'pay_item_id', 'code', 'label', 'type',
        'base_amount', 'rate', 'amount', 'employer_amount',
        'is_visible', 'display_order',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'rate' => 'decimal:4',
        'amount' => 'decimal:2',
        'employer_amount' => 'decimal:2',
    ];

    public function payslip(): BelongsTo { return $this->belongsTo(Payslip::class); }
    public function payItem(): BelongsTo { return $this->belongsTo(PayItem::class); }
}
