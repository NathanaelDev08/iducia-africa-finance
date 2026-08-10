<?php
namespace App\Modules\Payroll\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialContribution extends Model
{
    protected $fillable = [
        'code', 'name', 'organism', 'employee_account_code',
        'employer_account_code', 'is_active',
    ];
    protected $casts = ['is_active' => 'boolean'];
    public function rates(): HasMany { return $this->hasMany(SocialContributionRate::class); }

    public function getActiveRateForDate($date)
    {
        return $this->rates()
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')->orWhere('effective_until', '>=', $date);
            })
            ->where('is_active', true)->first();
    }
}
