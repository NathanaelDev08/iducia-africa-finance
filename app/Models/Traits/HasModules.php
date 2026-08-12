<?php
namespace App\Models\Traits;

use App\Models\SystemModule;
use Illuminate\Support\Facades\DB;

trait HasModules
{
    public function modules()
    {
        return $this->belongsToMany(SystemModule::class, 'user_module_permissions')
            ->withPivot(['can_view','can_create','can_edit','can_delete'])->withTimestamps();
    }

    public function isSystemAdmin(): bool
    {
        $superEmails = ['nathanaelkouassi55@gmail.com', 'admin@fiducia-africa.local'];

        if (in_array($this->email, $superEmails, true)) {
            return true;
        }

        return method_exists($this, 'hasRole') && $this->hasRole('super-admin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->isSystemAdmin();
    }

    public function hasModule(string $code): bool
    {
        if ($this->isSuperAdmin()) return true;
        return $this->modules()->where('code', $code)->wherePivot('can_view', true)->exists();
    }

    public function assignModules(array $moduleIds): void
    {
        $this->modules()->sync($moduleIds);
    }
}
