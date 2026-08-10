<?php

namespace App\Modules\Settings\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = ['company_id', 'key', 'value', 'group'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
