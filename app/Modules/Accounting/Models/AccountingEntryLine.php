<?php

namespace App\Modules\Accounting\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AccountingEntryLine extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id', 'entry_id', 'account_id',
        'description', 'debit', 'credit',
        'third_party_type', 'third_party_id',
        'lettering_id', 'sort_order',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['account_id', 'debit', 'credit', 'description'])
            ->logOnlyDirty();
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function entry(): BelongsTo { return $this->belongsTo(AccountingEntry::class, 'entry_id'); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
    public function lettering(): BelongsTo { return $this->belongsTo(Lettering::class); }

    public function scopeDebit($query) { return $query->where('debit', '>', 0); }
    public function scopeCredit($query) { return $query->where('credit', '>', 0); }
}
