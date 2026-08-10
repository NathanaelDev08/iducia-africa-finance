<?php

namespace App\Modules\Accounting\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'chart_account_id',
        'parent_id',
        'number',
        'name',
        'class_number',
        'type',
        'category',
        'is_active',
        'is_reconcilable',
        'is_auxiliary',
        'is_cash_account',
        'is_bank_account',
        'is_tax_account',
        'default_tax_id',
        'opening_balance',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_reconcilable' => 'boolean',
        'is_auxiliary' => 'boolean',
        'is_cash_account' => 'boolean',
        'is_bank_account' => 'boolean',
        'is_tax_account' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function scopeByClass($query, int $classNumber)
    {
        return $query->where('class_number', $classNumber);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
