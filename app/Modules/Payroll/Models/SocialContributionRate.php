<?php
namespace App\Modules\Payroll\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialContributionRate extends Model
{
    protected $fillable = [
        'social_contribution_id', 'employee_rate', 'employer_rate',
        'ceiling', 'effective_from', 'effective_until', 'is_active',
    ];
    protected $casts = [
        'effective_from' => 'date', 'effective_until' => 'date', 'is_active' => 'boolean',
        'employee_rate' => 'decimal:4', 'employer_rate' => 'decimal:4', 'ceiling' => 'decimal:2',
    ];
    public function contribution(): BelongsTo { return $this->belongsTo(SocialContribution::class); }
}
