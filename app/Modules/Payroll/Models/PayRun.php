<?php
namespace App\Modules\Payroll\Models;
use App\Models\Company;
use App\Models\User;
use App\Modules\Accounting\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayRun extends Model
{
    protected $fillable = [
        'company_id', 'name', 'reference', 'period_start', 'period_end',
        'payment_date', 'status', 'is_locked', 'validated_by', 'validated_at',
        'locked_by', 'locked_at', 'accounting_entry_id', 'notes',
    ];
    protected $casts = [
        'period_start' => 'date', 'period_end' => 'date', 'payment_date' => 'date',
        'is_locked' => 'boolean', 'validated_at' => 'datetime', 'locked_at' => 'datetime',
    ];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function payslips(): HasMany { return $this->hasMany(Payslip::class); }
    public function validatedBy(): BelongsTo { return $this->belongsTo(User::class, 'validated_by'); }
    public function lockedBy(): BelongsTo { return $this->belongsTo(User::class, 'locked_by'); }
    public function accountingEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class, 'accounting_entry_id'); }
    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isCalculated(): bool { return $this->status === 'calculated'; }
}
