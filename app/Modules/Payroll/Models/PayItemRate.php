<?php
namespace App\Modules\Payroll\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayItemRate extends Model
{
    protected $fillable = [
        'pay_item_id', 'rate', 'fixed_amount', 'ceiling',
        'effective_from', 'effective_until', 'is_active',
    ];
    protected $casts = [
        'effective_from' => 'date', 'effective_until' => 'date',
        'is_active' => 'boolean', 'rate' => 'decimal:4', 'fixed_amount' => 'decimal:2', 'ceiling' => 'decimal:2',
    ];
    public function payItem(): BelongsTo { return $this->belongsTo(PayItem::class); }
}
