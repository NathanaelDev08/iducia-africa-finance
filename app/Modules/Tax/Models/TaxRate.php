<?php

namespace App\Modules\Tax\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    protected $fillable = ['tax_id', 'rate', 'effective_from', 'effective_until', 'is_active'];
    protected $casts = ['effective_from' => 'date', 'effective_until' => 'date', 'is_active' => 'boolean', 'rate' => 'decimal:4'];

    public function tax(): BelongsTo { return $this->belongsTo(Tax::class); }
}
