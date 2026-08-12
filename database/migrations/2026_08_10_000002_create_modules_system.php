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
            Schema::create('system_modules', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name');
                $table->string('icon', 50)->nullable();
                $table->string('route')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_base_module')->default(false); // accessible à tous
                $table->integer('display_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_module_permissions')) {
            Schema::create('user_module_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('system_module_id')->constrained()->onDelete('cascade');
                $table->boolean('can_view')->default(true);
                $table->boolean('can_create')->default(false);
                $table->boolean('can_edit')->default(false);
                $table->boolean('can_delete')->default(false);
                $table->timestamps();
                $table->unique(['user_id', 'system_module_id']);
            });
        }

        if (!Schema::hasTable('role_module_templates')) {
            Schema::create('role_module_templates', function (Blueprint $table) {
                $table->id();
                $table->string('role_name');
                $table->foreignId('system_module_id')->constrained()->onDelete('cascade');
                $table->boolean('can_view')->default(true);
                $table->boolean('can_create')->default(true);
                $table->boolean('can_edit')->default(true);
                $table->boolean('can_delete')->default(false);
                $table->unique(['role_name', 'system_module_id']);
            });
        }

        // Seed des modules de base
        $modules = [
            ['code' => 'dashboard', 'name' => 'Tableau de bord', 'icon' => '🏠', 'route' => '/dashboard', 'is_base_module' => true, 'display_order' => 1],
            ['code' => 'hr', 'name' => 'Ressources Humaines', 'icon' => '👥', 'route' => '/hr', 'display_order' => 10],
            ['code' => 'payroll', 'name' => 'Paie', 'icon' => '💰', 'route' => '/payroll', 'display_order' => 20],
            ['code' => 'accounting', 'name' => 'Comptabilité', 'icon' => '📒', 'route' => '/accounting', 'display_order' => 30],
            ['code' => 'sales', 'name' => 'Ventes & Facturation', 'icon' => '📊', 'route' => '/sales', 'display_order' => 40],
            ['code' => 'purchasing', 'name' => 'Achats', 'icon' => '🛒', 'route' => '/purchasing', 'display_order' => 50],
            ['code' => 'inventory', 'name' => 'Stock & Inventaire', 'icon' => '📦', 'route' => '/inventory', 'display_order' => 60],
            ['code' => 'treasury', 'name' => 'Trésorerie', 'icon' => '🏦', 'route' => '/treasury', 'display_order' => 70],
            ['code' => 'cash', 'name' => 'Caisse', 'icon' => '💰', 'route' => '/caisse', 'display_order' => 75],
            ['code' => 'tax', 'name' => 'Fiscalité', 'icon' => '📋', 'route' => '/tax', 'display_order' => 80],
            ['code' => 'assets', 'name' => 'Immobilisations', 'icon' => '🏢', 'route' => '/assets', 'display_order' => 90],
            ['code' => 'reports', 'name' => 'Rapports', 'icon' => '📈', 'route' => '/reports', 'display_order' => 100],
            ['code' => 'settings', 'name' => 'Paramétrage', 'icon' => '⚙️', 'route' => '/settings', 'display_order' => 200],
        ];
        foreach ($modules as $m) {
            $exists = DB::table('system_modules')->where('code', $m['code'])->exists();
            if (!$exists) {
                DB::table('system_modules')->insert(array_merge($m, ['created_at' => now(), 'updated_at' => now()]));
            }
        }

        // Templates par rôle
        $templates = [
            'admin-company' => ['dashboard', 'hr', 'payroll', 'accounting', 'sales', 'purchasing', 'inventory', 'treasury', 'cash', 'tax', 'assets', 'reports', 'settings'],
            'accountant' => ['dashboard', 'accounting', 'cash', 'tax', 'reports'],
            'hr-manager' => ['dashboard', 'hr', 'payroll', 'reports'],
            'payroll-manager' => ['dashboard', 'payroll', 'reports'],
            'commercial' => ['dashboard', 'sales', 'reports'],
            'employee' => ['dashboard'],
        ];
        foreach ($templates as $role => $moduleCodes) {
            foreach ($moduleCodes as $code) {
                $mod = DB::table('system_modules')->where('code', $code)->first();
                if ($mod) {
                    DB::table('role_module_templates')->insert([
                        'role_name' => $role,
                        'system_module_id' => $mod->id,
                        'can_view' => true,
                        'can_create' => $role !== 'employee',
                        'can_edit' => $role !== 'employee',
                        'can_delete' => in_array($role, ['admin-company']),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_module_templates');
        Schema::dropIfExists('user_module_permissions');
        Schema::dropIfExists('system_modules');
    }
};
