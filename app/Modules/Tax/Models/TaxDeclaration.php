<?php

namespace App\Modules\Tax\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxDeclaration extends Model
{
    protected $fillable = [
        'company_id', 'type', 'reference', 'period', 'due_date', 'status',
        'base_amount', 'tax_amount', 'penalty_amount', 'notes', 'filed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'filed_at' => 'datetime',
        'base_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
