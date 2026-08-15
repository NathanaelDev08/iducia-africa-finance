<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SystemModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanyUserManagementAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['cache']->forget('spatie.permission.cache');

        foreach (['super-admin', 'admin-company', 'employee', 'manager', 'accountant', 'hr-manager'] as $name) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    protected function makeCompany(string $slug): Company
    {
        return Company::create([
            'name' => 'Acme ' . $slug,
            'slug' => $slug,
            'short_name' => 'ACME',
            'currency' => 'XOF',
            'timezone' => 'Africa/Abidjan',
            'is_active' => true,
        ]);
    }

    protected function makeModule(string $code): SystemModule
    {
        return SystemModule::updateOrCreate(
            ['code' => $code],
            [
                'name' => ucfirst($code),
                'route' => "$code.index",
                'description' => 'Test',
                'is_base_module' => false,
                'display_order' => 1,
                'is_active' => true,
            ]
        );
    }

    public function test_company_admin_cannot_manage_users_at_all(): void
    {
        $company = $this->makeCompany('acme-1');
        $companyAdmin = User::factory()->create(['email' => 'company-admin@example.com']);
        $companyAdmin->companies()->attach($company->id, ['role' => 'admin', 'is_active' => true]);
        $companyAdmin->assignRole('admin-company');

        $module = $this->makeModule('hr');

        // Seul le super-admin peut créer des comptes : un admin d'entreprise
        // n'a même pas accès aux routes de gestion des utilisateurs.
        $response = $this->actingAs($companyAdmin)->post('/super-admin/users', [
            'name' => 'New employee',
            'email' => 'new-employee@example.com',
            'company_id' => $company->id,
            'role' => 'employee',
            'modules' => [$module->id],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'new-employee@example.com']);
    }

    public function test_super_admin_can_create_user_for_any_company(): void
    {
        $company = $this->makeCompany('acme-2');
        $superAdmin = User::factory()->create(['email' => 'super-admin@fiducia-africa.local']);
        $superAdmin->assignRole('super-admin');

        $module = $this->makeModule('hr');

        $response = $this->actingAs($superAdmin)->post('/super-admin/users', [
            'name' => 'New employee',
            'email' => 'new-employee@example.com',
            'company_id' => $company->id,
            'role' => 'employee',
            'modules' => [$module->id],
        ]);

        $response->assertRedirect();
        $user = User::where('email', 'new-employee@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue((bool) $user->must_change_password);
        $this->assertFalse($user->hasRole('super-admin'));
        $this->assertTrue($user->hasModule('hr'));
    }

    public function test_module_permissions_are_persisted_for_created_user(): void
    {
        $company = $this->makeCompany('acme-3');
        $superAdmin = User::factory()->create(['email' => 'super-admin2@fiducia-africa.local']);
        $superAdmin->assignRole('super-admin');

        $module = $this->makeModule('settings');

        $response = $this->actingAs($superAdmin)->post('/super-admin/users', [
            'name' => 'New settings user',
            'email' => 'settings-user@example.com',
            'company_id' => $company->id,
            'role' => 'employee',
            'modules' => [$module->id],
            'module_permissions' => [[
                'module_id' => $module->id,
                'can_view' => true,
                'can_create' => true,
                'can_edit' => false,
                'can_delete' => false,
            ]],
        ]);

        $response->assertRedirect();
        $user = User::where('email', 'settings-user@example.com')->first();
        $this->assertNotNull($user);

        $pivot = $user->modules()->where('system_modules.id', $module->id)->first()?->pivot;
        $this->assertNotNull($pivot);
        $this->assertTrue((bool) $pivot->can_view);
        $this->assertTrue((bool) $pivot->can_create);
        $this->assertFalse((bool) $pivot->can_edit);
        $this->assertFalse((bool) $pivot->can_delete);
    }
}
