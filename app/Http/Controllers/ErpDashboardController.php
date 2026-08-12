<?php

namespace App\Http\Controllers;

use App\Modules\Reporting\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ErpDashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $service): Response
    {
        // Récupération sécurisée de l'entreprise active
        // On vérifie si elle a bien été bindée par le middleware SetActiveCompany
        $company = app()->bound('currentCompany') ? app('currentCompany') : null;

        $data = null;
        if ($company) {
            $data = $service->getOverview($company);
        }

        return Inertia::render('Dashboard', [
            'company' => $data['company'] ?? [
                'id' => $company?->id,
                'name' => $company?->name ?? '—',
                'currency' => $company?->currency ?? 'FCFA',
            ],
            'metrics' => $data['metrics'] ?? [],
            'alerts' => $data['alerts'] ?? [],
            'generated_at' => $data['generated_at'] ?? now()->toISOString(),
            'erpData' => $data,
        ]);
    }
}
