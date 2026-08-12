<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_middleware_alias_is_registered(): void
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);

        $this->assertArrayHasKey('module', $kernel->getMiddlewareAliases());
        $this->assertSame(
            \App\Http\Middleware\CheckModuleAccess::class,
            $kernel->getMiddlewareAliases()['module']
        );
    }
}
