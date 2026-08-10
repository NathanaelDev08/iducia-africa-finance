<?php
namespace App\Modules\Payroll\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipItem extends Model
{
    protected $fillable = [
        'payslip_id', 'pay_item_id', 'name', 'type', 'base_amount',
        'rate', 'amount', 'is_earning', 'display_order',
    ];
    protected $casts = [
        'base_amount' => 'decimal:2', 'rate' => 'decimal:4',
        'amount' => 'decimal:2', 'is_earning' => 'boolean',
    ];
    public function payslip(): BelongsTo { return $this->belongsTo(Payslip::class); }
    public function payItem(): BelongsTo { return $this->belongsTo(PayItem::class); }
}
