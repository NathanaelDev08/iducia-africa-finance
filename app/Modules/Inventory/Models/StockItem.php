<?php
namespace App\Modules\Inventory\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    protected $fillable = [
        'company_id', 'code', 'name', 'category', 'unit',
        'quantity_on_hand', 'unit_cost', 'reorder_threshold', 'is_active',
    ];

    protected $casts = [
        'quantity_on_hand' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'reorder_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isBelowThreshold(): bool
    {
        return (float) $this->reorder_threshold > 0 && (float) $this->quantity_on_hand <= (float) $this->reorder_threshold;
    }
}
