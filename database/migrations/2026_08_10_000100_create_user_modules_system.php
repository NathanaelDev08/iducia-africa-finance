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
                $table->string('icon', 20)->nullable();
                $table->string('route')->nullable();
                $table->boolean('is_base_module')->default(false);
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
                $table->unique(['user_id', 'system_module_id']);
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function (Blueprint $t) {
                $t->boolean('must_change_password')->default(false);
                $t->boolean('is_active')->default(true);
                $t->timestamp('password_changed_at')->nullable();
            });
        }

        if (DB::table('system_modules')->count() === 0) {
            $mods = [
                ['code' => 'dashboard', 'name' => 'Tableau de bord', 'icon' => '🏠', 'route' => '/dashboard', 'is_base_module' => true, 'display_order' => 1],
                ['code' => 'accounting', 'name' => 'Comptabilité', 'icon' => '📒', 'route' => '/accounting', 'display_order' => 10],
                ['code' => 'hr', 'name' => 'Ressources Humaines', 'icon' => '👥', 'route' => '/hr/employees', 'display_order' => 20],
                ['code' => 'payroll', 'name' => 'Paie', 'icon' => '💰', 'route' => '/payroll', 'display_order' => 30],
                ['code' => 'tax', 'name' => 'Fiscalité', 'icon' => '📋', 'route' => '/tax', 'display_order' => 40],
                ['code' => 'reporting', 'name' => 'Rapports', 'icon' => '📈', 'route' => '/reporting', 'display_order' => 50],
                ['code' => 'purchasing', 'name' => 'Achats', 'icon' => '🛒', 'route' => '/purchasing', 'display_order' => 60],
                ['code' => 'sales', 'name' => 'Ventes', 'icon' => '🛍️', 'route' => '/sales', 'display_order' => 70],
                ['code' => 'assets', 'name' => 'Immobilisations', 'icon' => '📦', 'route' => '/assets', 'display_order' => 80],
                ['code' => 'treasury', 'name' => 'Trésorerie', 'icon' => '🏦', 'route' => '/treasury', 'display_order' => 90],
                ['code' => 'cash', 'name' => 'Caisse', 'icon' => '💰', 'route' => '/caisse', 'display_order' => 95],
                ['code' => 'settings', 'name' => 'Paramétrage', 'icon' => '⚙️', 'route' => '/parametrage', 'display_order' => 100],
            ];
            foreach ($mods as $m) {
                DB::table('system_modules')->insert(array_merge($m, ['created_at' => now(), 'updated_at' => now()]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_module_permissions');
        Schema::dropIfExists('system_modules');
    }
};
