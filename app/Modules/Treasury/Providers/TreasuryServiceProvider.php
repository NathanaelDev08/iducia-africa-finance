<?php

namespace App\Modules\Treasury\Providers;

use App\Modules\Core\Providers\ModuleServiceProvider;

class TreasuryServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Treasury';

    public function register(): void
    {
        // Add module-specific container bindings here if needed.
    }
}
