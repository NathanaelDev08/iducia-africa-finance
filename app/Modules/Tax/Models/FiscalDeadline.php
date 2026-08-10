<?php

namespace App\Modules\Tax\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalDeadline extends Model
{
    protected $fillable = [
        'company_id', 'type', 'name', 'due_date',
        'status', 'related_declaration_id', 'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->where('status', 'pending')
            ->where('due_date', '<=', now()->addDays($days));
    }
}
