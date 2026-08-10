<?php

namespace App\Modules\Settings\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tax extends Model
{
    protected $table = 'taxes';

    protected $fillable = [
        'company_id', 'code', 'name', 'type', 'rate', 'account_number',
        'is_active', 'is_default', 'effective_from', 'effective_to', 'description',
    ];

    protected $casts = [
        'rate' => 'float',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
