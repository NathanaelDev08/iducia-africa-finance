<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('system_modules')) {
            return;
        }

        if (!DB::table('system_modules')->where('code', 'cash')->exists()) {
            DB::table('system_modules')->insert([
                'code' => 'cash',
                'name' => 'Caisse',
                'icon' => '💰',
                'route' => '/caisse',
                'is_base_module' => false,
                'display_order' => 75,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('role_module_templates')) {
            $module = DB::table('system_modules')->where('code', 'cash')->first();
            if ($module) {
                $roles = ['admin-company', 'accountant'];
                foreach ($roles as $role) {
                    $exists = DB::table('role_module_templates')->where('role_name', $role)->where('system_module_id', $module->id)->exists();
                    if (!$exists) {
                        DB::table('role_module_templates')->insert([
                            'role_name' => $role,
                            'system_module_id' => $module->id,
                            'can_view' => true,
                            'can_create' => true,
                            'can_edit' => true,
                            'can_delete' => false,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('system_modules')) {
            return;
        }

        $module = DB::table('system_modules')->where('code', 'cash')->first();
        if ($module) {
            if (Schema::hasTable('role_module_templates')) {
                DB::table('role_module_templates')->where('system_module_id', $module->id)->delete();
            }
            DB::table('system_modules')->where('id', $module->id)->delete();
        }
    }
};
