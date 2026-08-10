<?php

namespace App\Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;

class PayItem extends Model
{
    protected $fillable = [
        'company_id', 'code', 'name', 'type', 'calculation_method',
        'base_type', 'is_taxable', 'is_subject_to_contributions',
        'is_visible_on_payslip', 'display_order', 'is_active',
    ];

    protected $casts = [
        'is_taxable' => 'boolean', 'is_subject_to_contributions' => 'boolean',
        'is_visible_on_payslip' => 'boolean', 'is_active' => 'boolean',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function rates(): HasMany { return $this->hasMany(PayItemRate::class); }

    public function getActiveRateForDate($date)
    {
        return $this->rates()
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                      ->orWhere('effective_until', '>=', $date);
            })
            ->where('is_active', true)
            ->first();
    }
}
