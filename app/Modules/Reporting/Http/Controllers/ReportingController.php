<?php

namespace App\Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Reporting\Services\AccountingReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportingController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:reports');
    }

    protected function company(Request $request): Company
    {
        return $request->attributes->get('company') ?? Company::first();
    }

    public function index(Request $request)
    {
        $service = app(AccountingReportService::class);
        $company = $this->company($request);

        return Inertia::render('Reports/Index', [
            'trialBalance' => $service->getTrialBalance($company),
            'profitAndLoss' => $service->getProfitAndLoss($company),
            'balanceSheet' => $service->getBalanceSheet($company),
            'charts' => $service->getCharts($company),
            'initialTab' => $request->query('tab', 'balance'),
        ]);
    }

    public function trialBalance() { return redirect()->route('reporting.index', ['tab' => 'balance']); }
    public function profitAndLoss() { return redirect()->route('reporting.index', ['tab' => 'resultat']); }
    public function balanceSheet() { return redirect()->route('reporting.index', ['tab' => 'bilan']); }

    // ============ EXPORTS CSV ============

    public function exportTrialBalance(Request $request)
    {
        $rows = app(AccountingReportService::class)->getTrialBalance($this->company($request));

        $data = $rows->map(fn ($r) => [
            $r->number, $r->name, $r->class_number,
            number_format((float) $r->total_debit, 2, '.', ''),
            number_format((float) $r->total_credit, 2, '.', ''),
            number_format((float) $r->total_debit - (float) $r->total_credit, 2, '.', ''),
        ])->all();

        return $this->csvResponse('balance_generale_' . now()->format('Ymd') . '.csv',
            ['N° Compte', 'Libellé', 'Classe', 'Débit', 'Crédit', 'Solde'], $data);
    }

    public function exportProfitAndLoss(Request $request)
    {
        $pnl = app(AccountingReportService::class)->getProfitAndLoss($this->company($request));

        $data = [];
        $data[] = ['CHARGES', '', ''];
        foreach ($pnl['expenses'] as $row) {
            $data[] = [$row->number, $row->name, number_format((float) $row->total_debit - (float) $row->total_credit, 2, '.', '')];
        }
        $data[] = ['TOTAL CHARGES', '', number_format($pnl['total_expenses'], 2, '.', '')];
        $data[] = ['PRODUITS', '', ''];
        foreach ($pnl['revenues'] as $row) {
            $data[] = [$row->number, $row->name, number_format((float) $row->total_credit - (float) $row->total_debit, 2, '.', '')];
        }
        $data[] = ['TOTAL PRODUITS', '', number_format($pnl['total_revenues'], 2, '.', '')];
        $data[] = ['RÉSULTAT NET', '', number_format($pnl['net_income'], 2, '.', '')];

        return $this->csvResponse('compte_resultat_' . now()->format('Ymd') . '.csv',
            ['Compte', 'Libellé', 'Montant'], $data);
    }

    public function exportBalanceSheet(Request $request)
    {
        $bs = app(AccountingReportService::class)->getBalanceSheet($this->company($request));

        $data = [];
        $data[] = ['ACTIF', '', ''];
        foreach ($bs['assets'] as $row) {
            $data[] = [$row->number, $row->name, number_format((float) $row->total_debit - (float) $row->total_credit, 2, '.', '')];
        }
        $data[] = ['TOTAL ACTIF', '', number_format($bs['total_assets'], 2, '.', '')];
        $data[] = ['PASSIF', '', ''];
        foreach ($bs['liabilities'] as $row) {
            $data[] = [$row->number, $row->name, number_format((float) $row->total_credit - (float) $row->total_debit, 2, '.', '')];
        }
        $data[] = ['Résultat net', '', number_format($bs['net_income'], 2, '.', '')];
        $data[] = ['TOTAL PASSIF', '', number_format($bs['total_liabilities'], 2, '.', '')];

        return $this->csvResponse('bilan_' . now()->format('Ymd') . '.csv',
            ['Compte', 'Libellé', 'Montant'], $data);
    }

    protected function csvResponse(string $filename, array $header, array $rows)
    {
        $callback = function () use ($header, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $header, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
