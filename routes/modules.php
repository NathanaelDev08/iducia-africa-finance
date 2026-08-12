<?php

use Illuminate\Support\Facades\Route;

/**
 * Routes par module avec protection d'accès
 * Chaque module peut définir ses routes ici
 */

$moduleRoutes = [
    'payroll' => function () {
        // Routes Paie - déjà définies ailleurs, on ajoute juste le middleware
    },
    'hr' => function () {
        // Routes RH
    },
    'accounting' => function () {
        // Routes Comptabilité
    },
    'sales' => function () {
        // Routes Ventes
    },
    'purchasing' => function () {
        // Routes Achats
    },
    'inventory' => function () {
        // Routes Stock
    },
    'treasury' => function () {
        // Routes Trésorerie
    },
    'tax' => function () {
        // Routes Fiscalité
    },
    'assets' => function () {
        // Routes Immobilisations
    },
    'reports' => function () {
        // Routes Rapports
    },
    'settings' => function () {
        // Routes Paramétrage
    },
];

// Application du middleware à chaque groupe de routes
foreach ($moduleRoutes as $moduleCode => $callback) {
    Route::middleware(['auth', "module:$moduleCode"])
        ->prefix($moduleCode)
        ->name("$moduleCode.")
        ->group($callback);
}
