<?php

namespace App\Modules\Tax\Models;

use App\Modules\Accounting\Models\Tax;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VatDeclarationLine extends Model
{
    protected $fillable = [
        'vat_declaration_id', 'tax_id', 'type', 'description',
        'base_amount', 'tax_rate', 'tax_amount',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
    ];

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(VatDeclaration::class, 'vat_declaration_id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
