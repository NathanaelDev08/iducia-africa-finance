<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use \App\Models\Traits\HasModules;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'avatar_url',
        'login_count',
        'first_login_at',
        'last_login_at',
        'last_seen_at',
        'must_change_password',
        'password_changed_at',
        'temp_password_token',
        'temp_password_expires_at',
        'is_active',
        'invited_by_type',
        'invited_by',
    ];

    protected $hidden = [
        'password',
        'remember_token', 'must_change_password', 'first_login_at', 'password_changed_at', 'temp_password_token', 'temp_password_expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('role', 'is_active')
            ->withTimestamps();
    }

    public function currentCompany(): ?Company
    {
        $companyId = session('active_company_id');

        if (! $companyId) {
            return null;
        }

        return $this->companies()
            ->where('companies.id', $companyId)
            ->first();
    }


    public function isSystemAdmin(): bool
    {
        $superEmails = ["nathanaelkouassi55@gmail.com", "admin@fiducia-africa.local"];

        if (in_array($this->email, $superEmails, true)) {
            return true;
        }

        return method_exists($this, 'hasRole') && $this->hasRole('super-admin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->isSystemAdmin();
    }

    public function can($ability, $arguments = []): bool
    {
        $target = is_string($arguments) ? $arguments : (is_array($arguments) ? ($arguments[0] ?? null) : $arguments);
        $targetClass = is_object($target) ? get_class($target) : (string) $target;

        if ($ability === 'create' && ($targetClass === 'App\\Modules\\Accounting\\Models\\AccountingEntry' || $targetClass === App\Modules\Accounting\Models\AccountingEntry::class)) {
            return $this->hasAnyRole(['super-admin', 'admin-company', 'accountant'])
                || $this->companies()->pluck('company_user.role')->contains(fn ($role) => in_array(strtolower((string) $role), ['admin', 'accountant'], true));
        }

        return parent::can($ability, $arguments);
    }

}
