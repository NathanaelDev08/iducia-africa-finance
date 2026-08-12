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
        'payment_date', 'status', 'is_locked', 'approved_by', 'approved_at',
        'locked_by', 'locked_at', 'accounting_entry_id', 'notes',
    ];
    protected $casts = [
        'period_start' => 'date', 'period_end' => 'date', 'payment_date' => 'date',
        'is_locked' => 'boolean', 'approved_at' => 'datetime', 'locked_at' => 'datetime',
    ];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function payslips(): HasMany { return $this->hasMany(Payslip::class); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function validatedBy(): BelongsTo { return $this->approvedBy(); }
    public function getValidatedByAttribute() { return $this->approved_by; }
    public function setValidatedByAttribute($value) { $this->attributes['approved_by'] = $value; }
    public function getValidatedAtAttribute() { return $this->approved_at; }
    public function setValidatedAtAttribute($value) { $this->attributes['approved_at'] = $value; }
    public function lockedBy(): BelongsTo { return $this->belongsTo(User::class, 'locked_by'); }
    public function accountingEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class, 'accounting_entry_id'); }
    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isCalculated(): bool { return $this->status === 'calculated'; }
}
