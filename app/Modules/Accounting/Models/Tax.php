<?php

namespace App\Modules\Accounting\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'type',
        'scope',
        'sales_account_id',
        'purchase_account_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }

    public function salesAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_account_id');
    }

    public function purchaseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'purchase_account_id');
    }

    public function getCurrentRateAttribute(): ?TaxRate
    {
        return $this->rates()
            ->where('effective_from', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now()->toDateString());
            })
            ->orderByDesc('effective_from')
            ->first();
    }
}
