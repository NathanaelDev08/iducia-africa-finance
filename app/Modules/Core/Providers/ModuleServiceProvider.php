<?php

namespace App\Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

abstract class ModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName = '';

    public function boot(): void
    {
        $modulePath = app_path("Modules/{$this->moduleName}");

        // Chargement automatique des migrations du module
        $migrationsPath = "{$modulePath}/Database/Migrations";

        if (File::isDirectory($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }

        // Chargement automatique des routes du module
        $routesPath = "{$modulePath}/routes";

        if (File::isDirectory($routesPath)) {
            foreach (File::allFiles($routesPath) as $file) {
                if ($file->getExtension() === 'php') {
                    $this->loadRoutesFrom($file->getPathname());
                }
            }
        }

        // Chargement des vues du module
        $viewsPath = "{$modulePath}/Resources/views";

        if (File::isDirectory($viewsPath)) {
            $this->loadViewsFrom($viewsPath, strtolower($this->moduleName));
        }
    }

    public function register(): void
    {
        //
    }
}
