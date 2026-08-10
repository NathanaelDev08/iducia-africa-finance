<?php

namespace App\Modules\Accounting\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = [
        'company_id', 'journal_id', 'entry_date', 'reference', 'description',
        'status', 'source_type', 'source_id', 'total_debit', 'total_credit',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function journal(): BelongsTo { return $this->belongsTo(Journal::class); }
    public function items(): HasMany { return $this->hasMany(JournalItem::class); }

    /**
     * Vérifie que l'écriture est équilibrée et la publie
     */
    public function validateAndPost(): void
    {
        $totalDebit = $this->items()->sum('debit');
        $totalCredit = $this->items()->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new \Exception("L'écriture n'est pas équilibrée. Débit: {$totalDebit}, Crédit: {$totalCredit}");
        }

        $this->update([
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'status' => 'posted',
        ]);
    }
}
