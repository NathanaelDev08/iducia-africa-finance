<?php

namespace App\Modules\Tax\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model
{
    protected $fillable = ['company_id', 'name', 'code', 'type', 'scope', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function rates(): HasMany { return $this->hasMany(TaxRate::class); }
}
