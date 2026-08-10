<?php

namespace App\Modules\Accounting\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AccountingEntry extends Model
{
    use SoftDeletes, LogsActivity;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_id', 'journal_id', 'period_id',
        'entry_number', 'reference', 'entry_date', 'description',
        'status', 'is_locked',
        'reversal_of_id', 'reversed_by_id',
        'validated_by', 'validated_at',
        'cancelled_by', 'cancelled_at',
        'attachment_path',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'is_locked' => 'boolean',
        'validated_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['entry_number', 'reference', 'entry_date', 'description', 'status'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Écriture {$eventName}");
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function journal(): BelongsTo { return $this->belongsTo(Journal::class); }
    public function period(): BelongsTo { return $this->belongsTo(Period::class); }
    public function lines(): HasMany { return $this->hasMany(AccountingEntryLine::class, 'entry_id'); }

    public function validatedBy(): BelongsTo { return $this->belongsTo(User::class, 'validated_by'); }
    public function cancelledBy(): BelongsTo { return $this->belongsTo(User::class, 'cancelled_by'); }

    public function reversalOf(): BelongsTo { return $this->belongsTo(self::class, 'reversal_of_id'); }
    public function reversedBy(): BelongsTo { return $this->belongsTo(self::class, 'reversed_by_id'); }

    public function isDraft(): bool { return $this->status === self::STATUS_DRAFT; }
    public function isValidated(): bool { return $this->status === self::STATUS_VALIDATED; }
    public function isCancelled(): bool { return $this->status === self::STATUS_CANCELLED; }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lines()->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lines()->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }
}
