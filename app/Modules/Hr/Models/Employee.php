<?php

namespace App\Modules\Hr\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Employee extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'user_id',
        'matricule',
        'last_name',
        'first_name',
        'birth_date',
        'birth_place',
        'sex',
        'nationality',
        'id_card_number',
        'cnps_number',
        'tax_id',
        'address',
        'phone',
        'email',
        'marital_status',
        'dependents_count',
        'hire_date',
        'seniority_date',
        'department_id',
        'position_id',
        'superior_id',
        'professional_category',
        'collective_agreement',
        'status',
        'exit_date',
        'exit_reason',
        'bank_name',
        'bank_account',
        'mobile_money',
        'payment_method',
        'payment_currency',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'seniority_date' => 'date',
        'exit_date' => 'date',
        'dependents_count' => 'integer',
    ];

    protected $hidden = [
        'bank_account',
        'mobile_money',
        'id_card_number',
        'cnps_number',
    ];

    protected $appends = [
        'full_name',
        'seniority_years',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'matricule',
                'last_name',
                'first_name',
                'status',
                'department_id',
                'position_id',
                'hire_date',
                'exit_date',
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Employé {$eventName}");
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function superior(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'superior_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class, 'employee_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getSeniorityYearsAttribute(): int
    {
        if (! $this->hire_date) {
            return 0;
        }

        return (int) $this->hire_date->diffInYears(now());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
