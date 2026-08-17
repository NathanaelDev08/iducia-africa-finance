<?php

namespace App\Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\ChartAccount;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\FiscalYear;
use App\Modules\Accounting\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AccountingController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:accounting');
    }

    protected function company(Request $request): Company
    {
        return $request->attributes->get('company') ?? Company::first();
    }

    public function index(Request $request)
    {
        $company = $this->company($request);
        $chart = ChartAccount::where('company_id', $company->id)->first();

        $accounts = $chart ? Account::where('chart_account_id', $chart->id)
            ->orderBy('number')->get()
            ->map(fn ($a) => [
                'id' => $a->id, 'number' => $a->number, 'name' => $a->name,
                'class_number' => $a->class_number, 'type' => $a->type,
                'is_active' => (bool) $a->is_active,
            ]) : collect();

        $journals = Journal::where('company_id', $company->id)
            ->orderBy('code')->get()
            ->map(fn ($j) => [
                'id' => $j->id, 'code' => $j->code, 'name' => $j->name,
                'type' => $j->type, 'is_active' => (bool) $j->is_active,
            ]);

        $fiscalYears = FiscalYear::where('company_id', $company->id)
            ->orderByDesc('start_date')->get()
            ->map(fn ($fy) => [
                'id' => $fy->id, 'name' => $fy->name,
                'start_date' => $fy->start_date->toDateString(),
                'end_date' => $fy->end_date->toDateString(),
                'status' => $fy->status,
            ]);

        $periods = AccountingPeriod::where('company_id', $company->id)
            ->with('fiscalYear')->orderByDesc('start_date')->get()
            ->map(fn ($p) => [
                'id' => $p->id, 'name' => $p->name,
                'start_date' => $p->start_date->toDateString(),
                'end_date' => $p->end_date->toDateString(),
                'status' => $p->status,
                'fiscal_year_name' => $p->fiscalYear->name ?? '—',
            ]);

        return Inertia::render('Accounting/Index', [
            'accounts' => $accounts,
            'journals' => $journals,
            'fiscalYears' => $fiscalYears,
            'periods' => $periods,
            'chartAccount' => $chart ? ['id' => $chart->id, 'name' => $chart->name] : null,
            'initialTab' => $request->query('tab', 'accounts'),
        ]);
    }

    // CRUD Comptes
    public function storeAccount(Request $request)
    {
        $company = $this->company($request);
        $chart = ChartAccount::firstOrCreate(['company_id' => $company->id], ['name' => 'Plan SYSCOHADA', 'standard' => 'SYSCOHADA', 'is_active' => true]);
        
        $data = $request->validate(['number' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('accounts')->where(function ($q) use ($request) { $q->where('company_id', $this->company($request)->id); })], 'name' => 'required|string|max:255', 'class_number' => 'required|integer|between:1,9', 'type' => 'required|string']);
        Account::create(array_merge($data, ['chart_account_id' => $chart->id, 'is_active' => true]));
        return back()->with('success', 'Compte créé.');
    }

    public function updateAccount(Request $request, Account $account)
    {
        if ($account->company_id !== $this->company($request)->id) abort(403);
        $data = $request->validate(['number' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('accounts')->where(function ($q) use ($request) { $q->where('company_id', $this->company($request)->id); })], 'name' => 'required|string|max:255', 'is_active' => 'boolean']);
        $account->update($data);
        return back()->with('success', 'Compte mis à jour.');
    }

    public function destroyAccount(Request $request, Account $account)
    {
        if ($account->company_id !== $this->company($request)->id) abort(403);
        $account->delete();
        return back()->with('success', 'Compte supprimé.');
    }

    /**
     * Import du plan comptable depuis un fichier CSV.
     *
     * Colonnes attendues (insensibles à la casse, ordre libre si un en-tête
     * est présent) : Numéro/number/compte, Libellé/label/name,
     * Classe/class/class_number (optionnel), Type/type (optionnel).
     * Si l'en-tête n'est pas reconnu, l'ordre par défaut est utilisé :
     * numéro, libellé, classe, type.
     */
    public function importAccounts(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $company = $this->company($request);
        $chart = ChartAccount::firstOrCreate(
            ['company_id' => $company->id],
            ['name' => 'Plan SYSCOHADA', 'standard' => 'SYSCOHADA', 'is_active' => true]
        );

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if ($handle === false) {
            return back()->with('error', "Impossible de lire le fichier importé.");
        }

        $created = 0;
        $updated = 0;
        $skipped = [];

        DB::beginTransaction();
        try {
            $firstRow = fgetcsv($handle);
            if ($firstRow === false) {
                fclose($handle);
                DB::rollBack();
                return back()->with('error', 'Le fichier CSV est vide.');
            }

            $columns = $this->mapImportColumns($firstRow);
            $rows = [];
            if ($columns === null) {
                // Pas d'en-tête reconnu : la première ligne est une ligne de données.
                $columns = ['number' => 0, 'name' => 1, 'class_number' => 2, 'type' => 3];
                $rows[] = $firstRow;
            }
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);

            $lineNumber = 1;
            foreach ($rows as $row) {
                $lineNumber++;

                if (!is_array($row) || count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue; // ligne vide
                }

                $number = trim((string) ($row[$columns['number']] ?? ''));
                $name = trim((string) ($row[$columns['name']] ?? ''));

                if ($number === '' || $name === '') {
                    $skipped[] = "Ligne {$lineNumber} : numéro ou libellé manquant.";
                    continue;
                }

                $classRaw = $columns['class_number'] !== null ? trim((string) ($row[$columns['class_number']] ?? '')) : '';
                $classNumber = ctype_digit($classRaw) ? (int) $classRaw : null;
                if ($classNumber === null || $classNumber < 1 || $classNumber > 9) {
                    $firstDigit = $number[0] ?? '';
                    if (!ctype_digit($firstDigit)) {
                        $skipped[] = "Ligne {$lineNumber} : numéro de compte « {$number} » invalide (classe indéterminable).";
                        continue;
                    }
                    $classNumber = (int) $firstDigit;
                }

                $typeRaw = $columns['type'] !== null ? trim((string) ($row[$columns['type']] ?? '')) : '';
                $type = $typeRaw !== '' ? strtolower($typeRaw) : $this->deriveAccountType($number, $classNumber);

                $existing = Account::where('company_id', $company->id)
                    ->where('chart_account_id', $chart->id)
                    ->where('number', $number)
                    ->first();

                $payload = [
                    'name' => $name,
                    'class_number' => $classNumber,
                    'type' => $type,
                ];

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    Account::create(array_merge($payload, [
                        'company_id' => $company->id,
                        'chart_account_id' => $chart->id,
                        'number' => $number,
                        'is_active' => true,
                    ]));
                    $created++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            if (is_resource($handle)) {
                fclose($handle);
            }
            return back()->with('error', "Échec de l'import : " . $e->getMessage());
        }

        $summary = "{$created} compte(s) créé(s), {$updated} mis à jour, " . count($skipped) . ' ignoré(s).';
        if (!empty($skipped)) {
            $summary .= ' Détails : ' . implode(' | ', array_slice($skipped, 0, 10));
            if (count($skipped) > 10) {
                $summary .= ' …';
            }
        }

        return back()->with(empty($skipped) ? 'success' : 'info', $summary);
    }

    /**
     * Tente d'associer les colonnes attendues à partir d'une ligne d'en-tête.
     * Retourne null si la ligne ne ressemble pas à un en-tête reconnu.
     */
    private function mapImportColumns(array $headerRow): ?array
    {
        $aliases = [
            'number' => ['numero', 'number', 'compte', 'num', 'no', 'n'],
            'name' => ['libelle', 'label', 'name', 'intitule', 'nom', 'designation'],
            'class_number' => ['classe', 'class', 'classnumber', 'classnum'],
            'type' => ['type', 'nature'],
        ];

        $columns = ['number' => null, 'name' => null, 'class_number' => null, 'type' => null];

        foreach ($headerRow as $index => $cell) {
            $normalized = $this->normalizeHeaderCell((string) $cell);
            foreach ($aliases as $key => $candidates) {
                if ($columns[$key] === null && in_array($normalized, $candidates, true)) {
                    $columns[$key] = $index;
                }
            }
        }

        if ($columns['number'] === null || $columns['name'] === null) {
            return null;
        }

        return $columns;
    }

    private function normalizeHeaderCell(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ô' => 'o', 'î' => 'i',
            'ï' => 'i', 'ç' => 'c', 'ù' => 'u', 'û' => 'u',
        ]);

        return preg_replace('/[^a-z0-9]/', '', $value) ?? '';
    }

    /**
     * Déduit le type d'un compte à partir de son numéro et de sa classe,
     * en suivant la même convention que SyscohadaChartSeeder.
     */
    private function deriveAccountType(string $number, int $classNumber): string
    {
        return match (true) {
            $classNumber === 1 => str_starts_with($number, '16') ? 'liability' : 'equity',
            $classNumber === 2, $classNumber === 3 => 'asset',
            $classNumber === 4 => (str_starts_with($number, '41') || str_starts_with($number, '443') || str_starts_with($number, '422'))
                ? 'asset' : 'liability',
            $classNumber === 5 => match (true) {
                str_starts_with($number, '56') => 'liability',
                str_starts_with($number, '57') => 'cash',
                default => 'bank',
            },
            $classNumber === 6 => 'expense',
            $classNumber === 7 => 'revenue',
            $classNumber === 8 => 'expense',
            default => 'asset',
        };
    }

    // CRUD Journaux
    public function storeJournal(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:10', \Illuminate\Validation\Rule::unique('journals')->where('company_id', $this->company($request)->id)], 'name' => 'required|string|max:255', 'type' => 'required|string']);
        Journal::create(array_merge($data, ['company_id' => $this->company($request)->id, 'is_active' => true]));
        return back()->with('success', 'Journal créé.');
    }

    public function updateJournal(Request $request, Journal $journal)
    {
        if ($journal->company_id !== $this->company($request)->id) abort(403);
        $data = $request->validate(['code' => ['required', 'string', 'max:10', \Illuminate\Validation\Rule::unique('journals')->where('company_id', $this->company($request)->id)], 'name' => 'required|string|max:255', 'is_active' => 'boolean']);
        $journal->update($data);
        return back()->with('success', 'Journal mis à jour.');
    }

    public function destroyJournal(Request $request, Journal $journal)
    {
        if ($journal->company_id !== $this->company($request)->id) abort(403);
        $journal->delete();
        return back()->with('success', 'Journal supprimé.');
    }

    // CRUD Exercices
    public function storeFiscalYear(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'start_date' => 'required|date', 'end_date' => 'required|date|after:start_date']);
        $fy = FiscalYear::create(array_merge($data, ['company_id' => $this->company($request)->id, 'status' => 'open']));
        
        // Créer automatiquement les 12 périodes mensuelles
        $start = \Carbon\Carbon::parse($data['start_date']);
        for ($i = 0; $i < 12; $i++) {
            $periodStart = $start->copy()->addMonths($i)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();
            AccountingPeriod::create([
                'company_id' => $fy->company_id,
                'fiscal_year_id' => $fy->id,
                'name' => $periodStart->format('F Y'),
                'start_date' => $periodStart,
                'end_date' => $periodEnd,
                'status' => 'open',
            ]);
        }
        return back()->with('success', 'Exercice créé avec 12 périodes.');
    }

    public function closeFiscalYear(Request $request, FiscalYear $fiscalYear)
    {
        if ($fiscalYear->company_id !== $this->company($request)->id) abort(403);
        $fiscalYear->update(['status' => 'closed']);
        $fiscalYear->periods()->update(['status' => 'closed']);
        return back()->with('success', 'Exercice clôturé.');
    }

    // CRUD Périodes
    public function closePeriod(Request $request, AccountingPeriod $period)
    {
        if ($period->company_id !== $this->company($request)->id) abort(403);
        $period->update(['status' => 'closed']);
        return back()->with('success', 'Période clôturée.');
    }

    public function reopenPeriod(Request $request, AccountingPeriod $period)
    {
        if ($period->company_id !== $this->company($request)->id) abort(403);
        $period->update(['status' => 'open']);
        return back()->with('success', 'Période rouverte.');
    }
}
