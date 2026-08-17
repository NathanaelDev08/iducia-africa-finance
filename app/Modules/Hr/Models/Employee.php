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

    /**
     * Nombre de jours de congés payés acquis par mois d'ancienneté.
     * Valeur usuelle en Côte d'Ivoire (2,2 à 2,5 j/mois selon la convention collective) :
     * À CONFIRMER avec la convention collective applicable avant utilisation en paie officielle.
     */
    public const ACCRUAL_DAYS_PER_MONTH = 2.5;

    /**
     * Seuil (en jours) à partir duquel le solde de congés acquis est considéré élevé :
     * déclenche le badge vert dans l'onglet Congés et l'alerte email RH/employé.
     */
    public const LEAVE_BALANCE_ALERT_THRESHOLD = 30.0;

    protected $fillable = [
        'company_id',
        'user_id',
        'matricule',
        'photo_path',
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
        'spouse_name',
        'spouse_profession',
        'spouse_employer',
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
        'leave_alert_sent_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'seniority_date' => 'date',
        'exit_date' => 'date',
        'dependents_count' => 'integer',
        'leave_alert_sent_at' => 'datetime',
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

    public function children(): HasMany
    {
        return $this->hasMany(EmployeeChild::class, 'employee_id');
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

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'employee_id');
    }

    /**
     * Cumul des congés acquis à date : (mois d'ancienneté × taux d'acquisition mensuel)
     * moins les jours de congés déjà approuvés. Ne descend jamais sous 0.
     */
    public function accruedLeaveBalance(): float
    {
        if (! $this->hire_date) {
            return 0.0;
        }

        $monthsOfSeniority = max(0, (int) $this->hire_date->diffInMonths(now()));
        $accrued = $monthsOfSeniority * self::ACCRUAL_DAYS_PER_MONTH;

        $takenDays = (float) $this->leaves()->where('status', 'approved')->sum('days_count');

        return max(0.0, round($accrued - $takenDays, 2));
    }

    public function hasReachedLeaveBalanceThreshold(): bool
    {
        return $this->accruedLeaveBalance() >= self::LEAVE_BALANCE_ALERT_THRESHOLD;
    }
}
