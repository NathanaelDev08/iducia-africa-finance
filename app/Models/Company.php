<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'short_name',
        'logo_path',
        'address',
        'phone',
        'email',
        'rccm',
        'ncc',
        'tax_id',
        'social_id',
        'currency',
        'timezone',
        'is_active',
        'suspended_at',
        'archived_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'suspended_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Company $company): void {
            if (empty($company->slug)) {
                $slug = Str::slug($company->name);
                $base = $slug;
                $index = 1;

                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $index++;
                }

                $company->slug = $slug;
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'is_active')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
