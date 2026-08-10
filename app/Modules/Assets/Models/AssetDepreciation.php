<?php
namespace App\Modules\Assets\Models;
use App\Models\Company;
use App\Modules\Accounting\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AssetDepreciation extends Model
{
    protected $fillable = ['company_id','asset_id','period','depreciation_date','amount','accumulated','net_book_value','accounting_entry_id','status'];
    protected $casts = ['depreciation_date'=>'date','amount'=>'decimal:2','accumulated'=>'decimal:2','net_book_value'=>'decimal:2'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function accountingEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class, 'accounting_entry_id'); }
}
