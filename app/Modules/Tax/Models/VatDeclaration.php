<?php

namespace App\Modules\Tax\Models;

use App\Models\Company;
use App\Models\User;
use App\Modules\Accounting\Models\Period;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VatDeclaration extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id', 'period_id', 'reference', 'name',
        'period_start', 'period_end', 'due_date',
        'total_sales_ht', 'total_vat_collected',
        'total_purchases_ht', 'total_vat_deductible',
        'vat_credit_previous', 'vat_to_pay', 'vat_credit_to_carry',
        'status', 'is_locked', 'validated_by', 'validated_at',
        'accounting_entry_id', 'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'total_sales_ht' => 'decimal:2',
        'total_vat_collected' => 'decimal:2',
        'total_purchases_ht' => 'decimal:2',
        'total_vat_deductible' => 'decimal:2',
        'vat_credit_previous' => 'decimal:2',
        'vat_to_pay' => 'decimal:2',
        'vat_credit_to_carry' => 'decimal:2',
        'is_locked' => 'boolean',
        'validated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_locked' => false,
        'status' => 'draft',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'vat_to_pay', 'vat_credit_to_carry', 'is_locked'])
            ->logOnlyDirty();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(VatDeclarationLine::class, 'vat_declaration_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    public function isLocked(): bool
    {
        return (bool) ($this->is_locked ?? false);
    }
}
