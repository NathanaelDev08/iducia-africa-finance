<?php

namespace App\Modules\Settings\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequenceNumber extends Model
{
    protected $table = 'sequence_numbers';

    protected $fillable = ['company_id', 'code', 'name', 'prefix', 'next_number', 'format'];

    protected $casts = ['next_number' => 'integer'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }

    /** Génère le prochain numéro formaté puis incrémente le compteur */
    public function next(): string
    {
        $number = (int) $this->next_number;

        $value = str_replace(['{prefix}', '{year}'], [$this->prefix, (string) now()->year], $this->format);
        $value = preg_replace_callback('/\{number(?::(\d+))?\}/', function ($m) use ($number) {
            $pad = isset($m[1]) ? (int) $m[1] : 4;
            return str_pad((string) $number, $pad, '0', STR_PAD_LEFT);
        }, $value);

        $this->increment('next_number');

        return $value;
    }
}
