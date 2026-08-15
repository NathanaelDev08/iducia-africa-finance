<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminCompanySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@fiducia-africa.local')->first();

        if (! $user) {
            $user = User::create([
                'name' => 'Administrateur FIDUCIA AFRICA',
                'email' => 'admin@fiducia-africa.local',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'must_change_password' => true,
            ]);
        }

        $company = Company::withTrashed()
            ->where('slug', 'fiducia-africa')
            ->first();

        if (! $company) {
            $company = Company::create([
                'name' => 'FIDUCIA AFRICA Conseil & Finance',
                'slug' => 'fiducia-africa',
                'short_name' => 'FIDUCIA AFRICA',
                'email' => 'contact@fiducia-africa.local',
                'currency' => 'XOF',
                'timezone' => 'Africa/Abidjan',
                'is_active' => true,
            ]);
        }

        if (! $user->companies()->where('companies.id', $company->id)->exists()) {
            $user->companies()->attach($company->id, [
                'role' => 'super-admin',
                'is_active' => true,
            ]);
        }

        $user->syncRoles(['super-admin']);
    }
}
