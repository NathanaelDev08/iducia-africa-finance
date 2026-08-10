<?php
namespace App\Modules\Treasury\Models;
use App\Modules\Accounting\Models\JournalItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BankStatementLine extends Model
{
    protected $fillable = ['bank_statement_id','transaction_date','reference','description','debit','credit','matched_journal_item_id','status'];
    protected $casts = ['transaction_date'=>'date','debit'=>'decimal:2','credit'=>'decimal:2'];
    public function statement(): BelongsTo { return $this->belongsTo(BankStatement::class, 'bank_statement_id'); }
    public function matchedItem(): BelongsTo { return $this->belongsTo(JournalItem::class, 'matched_journal_item_id'); }
}
