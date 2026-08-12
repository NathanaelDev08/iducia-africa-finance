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

    public function test_company_admin_cannot_create_system_admin_account(): void
    {
        $company = Company::create([
            'name' => 'Acme',
            'slug' => 'acme',
            'short_name' => 'ACME',
            'currency' => 'XOF',
            'timezone' => 'Africa/Abidjan',
            'is_active' => true,
        ]);

        $companyAdmin = User::factory()->create(['email' => 'company-admin@example.com']);
        $companyAdmin->companies()->attach($company->id, ['role' => 'admin', 'is_active' => true]);
        $companyAdmin->assignRole('admin-company');

        SystemModule::updateOrCreate(
            ['code' => 'accounting'],
            [
                'name' => 'Comptabilité',
                'route' => 'accounting.index',
                'description' => 'Test',
                'is_base_module' => false,
                'display_order' => 1,
                'is_active' => true,
            ]
        );

        $moduleId = SystemModule::where('code', 'accounting')->value('id');

        $response = $this->actingAs($companyAdmin)->post('/super-admin/users', [
            'name' => 'New admin',
            'email' => 'new-admin@example.com',
            'company_id' => $company->id,
            'role' => 'admin',
            'modules' => [$moduleId ?? 1],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'new-admin@example.com']);
    }

    public function test_company_admin_can_create_only_company_user_for_own_company(): void
    {
        $company = Company::create([
            'name' => 'Acme',
            'slug' => 'acme-2',
            'short_name' => 'ACME2',
            'currency' => 'XOF',
            'timezone' => 'Africa/Abidjan',
            'is_active' => true,
        ]);

        $companyAdmin = User::factory()->create(['email' => 'company-admin2@example.com']);
        $companyAdmin->companies()->attach($company->id, ['role' => 'admin', 'is_active' => true]);
        $companyAdmin->assignRole('admin-company');

        SystemModule::updateOrCreate(
            ['code' => 'hr'],
            [
                'name' => 'RH',
                'route' => 'hr.index',
                'description' => 'Test',
                'is_base_module' => false,
                'display_order' => 2,
                'is_active' => true,
            ]
        );

        $moduleId = SystemModule::where('code', 'hr')->value('id');

        $response = $this->actingAs($companyAdmin)->post('/super-admin/users', [
            'name' => 'New employee',
            'email' => 'new-employee@example.com',
            'company_id' => $company->id,
            'role' => 'employee',
            'modules' => [$moduleId ?? 1],
        ]);

        $response->assertRedirect();
        $user = User::where('email', 'new-employee@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->hasRole('super-admin'));
        $this->assertTrue($user->hasModule('hr'));
    }

    public function test_module_permissions_are_persisted_for_created_user(): void
    {
        $company = Company::create([
            'name' => 'Acme 3',
            'slug' => 'acme-3',
            'short_name' => 'ACME3',
            'currency' => 'XOF',
            'timezone' => 'Africa/Abidjan',
            'is_active' => true,
        ]);

        $companyAdmin = User::factory()->create(['email' => 'company-admin3@example.com']);
        $companyAdmin->companies()->attach($company->id, ['role' => 'admin', 'is_active' => true]);
        $companyAdmin->assignRole('admin-company');

        SystemModule::updateOrCreate(
            ['code' => 'settings'],
            [
                'name' => 'Paramétrage',
                'route' => 'settings.index',
                'description' => 'Test',
                'is_base_module' => false,
                'display_order' => 3,
                'is_active' => true,
            ]
        );

        $moduleId = SystemModule::where('code', 'settings')->value('id');

        $response = $this->actingAs($companyAdmin)->post('/super-admin/users', [
            'name' => 'New settings user',
            'email' => 'settings-user@example.com',
            'company_id' => $company->id,
            'role' => 'employee',
            'modules' => [$moduleId ?? 1],
            'module_permissions' => [[
                'module_id' => $moduleId ?? 1,
                'can_view' => true,
                'can_create' => true,
                'can_edit' => false,
                'can_delete' => false,
            ]],
        ]);

        $response->assertRedirect();
        $user = User::where('email', 'settings-user@example.com')->first();
        $this->assertNotNull($user);

        $pivot = $user->modules()->where('system_modules.id', $moduleId ?? 1)->first()?->pivot;
        $this->assertNotNull($pivot);
        $this->assertTrue((bool) $pivot->can_view);
        $this->assertTrue((bool) $pivot->can_create);
        $this->assertFalse((bool) $pivot->can_edit);
        $this->assertFalse((bool) $pivot->can_delete);
    }
}
