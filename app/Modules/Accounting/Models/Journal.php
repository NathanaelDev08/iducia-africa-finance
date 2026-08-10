<?php

namespace App\Modules\Accounting\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Journal extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'default_account_id',
        'next_number_pattern',
        'next_number',
        'is_active',
        'requires_attachment',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_attachment' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function defaultAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'default_account_id');
    }

    public function generateNextNumber(): string
    {
        $year = now()->format('Y');
        $sequence = str_pad((string) $this->next_number, 6, '0', STR_PAD_LEFT);

        $pattern = $this->next_number_pattern ?? '{CODE}-{YYYY}-{SEQ:6}';

        $number = str_replace(
            ['{CODE}', '{YYYY}', '{SEQ:6}'],
            [$this->code, $year, $sequence],
            $pattern
        );

        $this->increment('next_number');

        return $number;
    }
}
