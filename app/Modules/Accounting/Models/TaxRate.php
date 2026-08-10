<?php

namespace App\Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    protected $fillable = [
        'tax_id',
        'rate',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function appliesOnDate(string $date): bool
    {
        if ($this->effective_from->isAfter($date)) {
            return false;
        }

        if ($this->effective_until && $this->effective_until->isBefore($date)) {
            return false;
        }

        return true;
    }
}
