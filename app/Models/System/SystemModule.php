<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SystemModule extends Model
{
    protected $fillable = ['code', 'name', 'icon', 'route', 'description', 'is_base_module', 'display_order', 'is_active'];

    protected $casts = [
        'is_base_module' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class, 'user_module_permissions')
            ->withPivot(['can_view', 'can_create', 'can_edit', 'can_delete'])
            ->withTimestamps();
    }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeBase($q) { return $q->where('is_base_module', true); }
    public function scopeOrdered($q) { return $q->orderBy('display_order'); }
}
