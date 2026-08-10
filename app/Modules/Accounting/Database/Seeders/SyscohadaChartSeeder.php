<?php

namespace App\Modules\Accounting\Database\Seeders;

use App\Models\Company;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\ChartAccount;
use App\Modules\Accounting\Models\FiscalYear;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\Period;
use App\Modules\Accounting\Models\Tax;
use App\Modules\Accounting\Models\TaxRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SyscohadaChartSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->get()->each(function (Company $company) {
            $this->seedForCompany($company);
        });
    }

    private function seedForCompany(Company $company): void
    {
        $chart = ChartAccount::firstOrCreate([
            'company_id' => $company->id,
            'slug' => 'syscohada',
        ], [
            'name' => 'Plan SYSCOHADA révisé',
            'standard' => 'SYSCOHADA',
            'version' => '2024',
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->seedAccounts($company, $chart);
        $this->seedJournals($company);
        $this->seedTaxes($company, $chart);
        $this->seedFiscalYear($company);
    }

    private function seedAccounts(Company $company, ChartAccount $chart): void
    {
        $accounts = [
            // Classe 1 - Ressources durables
            ['10', 'Capital', 1, 'equity'],
            ['101', 'Capital social', 1, 'equity'],
            ['101100', 'Capital souscrit, non appelé', 1, 'equity'],
            ['101200', 'Capital souscrit, appelé non versé', 1, 'equity'],
            ['101300', 'Capital souscrit, appelé, versé', 1, 'equity'],
            ['12', 'Report à nouveau', 1, 'equity'],
            ['121', 'Report à nouveau créditeur', 1, 'equity'],
            ['129', 'Report à nouveau débiteur', 1, 'equity'],
            ['13', 'Résultat net de l\'exercice', 1, 'equity'],
            ['131', 'Résultat net : Bénéfice', 1, 'equity'],
            ['139', 'Résultat net : Perte', 1, 'equity'],
            ['16', 'Emprunts et dettes assimilées', 1, 'liability'],
            ['165', 'Emprunts auprès des établissements de crédit', 1, 'liability'],

            // Classe 2 - Actif immobilisé
            ['20', 'Charges immobilisées', 2, 'asset'],
            ['21', 'Immobilisations incorporelles', 2, 'asset'],
            ['22', 'Terrains', 2, 'asset'],
            ['23', 'Bâtiments', 2, 'asset'],
            ['231100', 'Bâtiments administratifs et commerciaux', 2, 'asset'],
            ['24', 'Matériels', 2, 'asset'],
            ['241100', 'Matériel de bureau', 2, 'asset'],
            ['241200', 'Matériel informatique', 2, 'asset'],
            ['242100', 'Matériel de transport', 2, 'asset'],
            ['243100', 'Mobilier de bureau', 2, 'asset'],
            ['28', 'Amortissements', 2, 'asset'],

            // Classe 3 - Stocks
            ['31', 'Marchandises', 3, 'asset'],
            ['32', 'Matières premières', 3, 'asset'],
            ['33', 'Autres approvisionnements', 3, 'asset'],
            ['36', 'Produits finis', 3, 'asset'],

            // Classe 4 - Tiers
            ['40', 'Fournisseurs et comptes rattachés', 4, 'liability'],
            ['401', 'Fournisseurs - Dettes en compte', 4, 'liability', true, true],
            ['401100', 'Fournisseurs nationaux', 4, 'liability', true, true],
            ['41', 'Clients et comptes rattachés', 4, 'asset'],
            ['411', 'Clients', 4, 'asset', true, true],
            ['411100', 'Clients - Ventes de biens et services', 4, 'asset', true, true],
            ['411101', 'Clients douteux', 4, 'asset', true, true],
            ['42', 'Personnel', 4, 'liability'],
            ['421', 'Personnel - Rémunérations dues', 4, 'liability'],
            ['422', 'Personnel - Avances et acomptes', 4, 'asset'],
            ['43', 'Organismes sociaux', 4, 'liability'],
            ['431', 'CNPS', 4, 'liability'],
            ['44', 'État et collectivités', 4, 'liability'],
            ['441', 'État - Impôts sur bénéfices', 4, 'liability'],
            ['443', 'État - TVA récupérable', 4, 'asset'],
            ['443100', 'TVA déductible sur achats', 4, 'asset'],
            ['445', 'État - TVA facturée', 4, 'liability'],
            ['445200', 'TVA collectée', 4, 'liability'],
            ['445700', 'TVA à décaisser', 4, 'liability'],
            ['445800', 'TVA à régulariser', 4, 'liability'],
            ['46', 'Débiteurs et créditeurs divers', 4, 'liability'],
            ['47', 'Comptes transitoires', 4, 'liability'],

            // Classe 5 - Trésorerie
            ['52', 'Banques', 5, 'bank'],
            ['521', 'Banques locales', 5, 'bank', false, false, true],
            ['521100', 'Banque SGBCI', 5, 'bank', false, false, true],
            ['521200', 'Banque BICICI', 5, 'bank', false, false, true],
            ['56', 'Banques - crédits de trésorerie', 5, 'liability'],
            ['57', 'Caisse', 5, 'cash'],
            ['571', 'Caisse siège social', 5, 'cash', false, false, false, true],

            // Classe 6 - Charges
            ['60', 'Achats et variations de stocks', 6, 'expense'],
            ['601', 'Achats de marchandises', 6, 'expense'],
            ['602', 'Achats de matières premières', 6, 'expense'],
            ['604', 'Achats stockés - Matières consommables', 6, 'expense'],
            ['605', 'Autres achats', 6, 'expense'],
            ['605100', 'Fournitures non stockables (eau, électricité)', 6, 'expense'],
            ['605200', 'Fournitures de bureau', 6, 'expense'],
            ['607', 'Travaux, services vendus', 6, 'expense'],
            ['61', 'Transports', 6, 'expense'],
            ['62', 'Services tiers', 6, 'expense'],
            ['622100', 'Locations', 6, 'expense'],
            ['624100', 'Entretien et réparations', 6, 'expense'],
            ['625100', 'Primes d\'assurance', 6, 'expense'],
            ['626100', 'Frais de téléphone', 6, 'expense'],
            ['627100', 'Frais postaux', 6, 'expense'],
            ['628100', 'Honoraires comptables', 6, 'expense'],
            ['628200', 'Honoraires juridiques', 6, 'expense'],
            ['63', 'Impôts et taxes', 6, 'expense'],
            ['631', 'Impôts directs', 6, 'expense'],
            ['635', 'Impôts et taxes directs', 6, 'expense'],
            ['64', 'Charges de personnel', 6, 'expense'],
            ['641', 'Rémunérations du personnel', 6, 'expense'],
            ['641100', 'Salaires de base', 6, 'expense'],
            ['641200', 'Heures supplémentaires', 6, 'expense'],
            ['641300', 'Primes et gratifications', 6, 'expense'],
            ['641400', 'Congés payés', 6, 'expense'],
            ['643', 'Charges sociales', 6, 'expense'],
            ['643100', 'Cotisations CNPS', 6, 'expense'],
            ['643200', 'Charges sociales diverses', 6, 'expense'],
            ['66', 'Charges financières', 6, 'expense'],
            ['661', 'Intérêts des emprunts', 6, 'expense'],
            ['67', 'Dotations aux amortissements', 6, 'expense'],
            ['68', 'Dotations aux provisions', 6, 'expense'],
            ['69', 'Impôts sur bénéfices', 6, 'expense'],

            // Classe 7 - Produits
            ['70', 'Ventes', 7, 'revenue'],
            ['701', 'Ventes de marchandises', 7, 'revenue'],
            ['701100', 'Ventes locales', 7, 'revenue'],
            ['702', 'Ventes de produits finis', 7, 'revenue'],
            ['706', 'Services vendus', 7, 'revenue'],
            ['706100', 'Prestations de services', 7, 'revenue'],
            ['75', 'Autres produits', 7, 'revenue'],
            ['77', 'Revenus financiers', 7, 'revenue'],
            ['78', 'Transferts de charges', 7, 'revenue'],
        ];

        $sort = 0;

        foreach ($accounts as $data) {
            [$number, $name, $classNumber, $type] = $data;

            $isAuxiliary = $data[4] ?? false;
            $isReconcilable = $data[5] ?? $isAuxiliary;
            $isBank = $data[6] ?? false;
            $isCash = $data[7] ?? false;

            Account::firstOrCreate([
                'company_id' => $company->id,
                'chart_account_id' => $chart->id,
                'number' => $number,
            ], [
                'parent_id' => $this->findParentAccountId($company->id, $chart->id, $number),
                'name' => $name,
                'class_number' => $classNumber,
                'type' => $type,
                'is_active' => true,
                'is_auxiliary' => $isAuxiliary,
                'is_reconcilable' => $isReconcilable,
                'is_bank_account' => $isBank,
                'is_cash_account' => $isCash,
                'sort_order' => ++$sort,
            ]);
        }
    }

    private function findParentAccountId(int $companyId, int $chartAccountId, string $number): ?int
    {
        $parentNumber = $number;

        while (strlen($parentNumber) > 2) {
            $parentNumber = substr($parentNumber, 0, -1);

            $parentId = Account::where('company_id', $companyId)
                ->where('chart_account_id', $chartAccountId)
                ->where('number', $parentNumber)
                ->value('id');

            if ($parentId) {
                return $parentId;
            }
        }

        return null;
    }

    private function seedJournals(Company $company): void
    {
        $journals = [
            ['AC', 'Achats', 'purchase', true],
            ['VE', 'Ventes', 'sale', true],
            ['BQ', 'Banque', 'bank', true],
            ['CA', 'Caisse', 'cash', true],
            ['OD', 'Opérations Diverses', 'misc', false],
            ['PA', 'Paie', 'payroll', false],
            ['TV', 'TVA', 'vat', false],
            ['AN', 'À Nouveau', 'opening', false],
        ];

        foreach ($journals as [$code, $name, $type, $requiresAttachment]) {
            Journal::firstOrCreate([
                'company_id' => $company->id,
                'code' => $code,
            ], [
                'name' => $name,
                'type' => $type,
                'next_number_pattern' => $code . '-{YYYY}-{SEQ:6}',
                'next_number' => 1,
                'is_active' => true,
                'requires_attachment' => $requiresAttachment,
            ]);
        }
    }

    private function seedTaxes(Company $company, ChartAccount $chart): void
    {
        $salesVatAccount = Account::where('company_id', $company->id)
            ->where('chart_account_id', $chart->id)
            ->where('number', '445200')
            ->first();

        $purchaseVatAccount = Account::where('company_id', $company->id)
            ->where('chart_account_id', $chart->id)
            ->where('number', '443100')
            ->first();

        $vat18 = Tax::updateOrCreate([
            'company_id' => $company->id,
            'code' => 'TVA_18',
        ], [
            'name' => 'TVA 18%',
            'type' => 'vat',
            'scope' => 'both',
            'sales_account_id' => $salesVatAccount?->id,
            'purchase_account_id' => $purchaseVatAccount?->id,
            'is_active' => true,
        ]);

        $vat9 = Tax::updateOrCreate([
            'company_id' => $company->id,
            'code' => 'TVA_9',
        ], [
            'name' => 'TVA 9% (réduite)',
            'type' => 'vat',
            'scope' => 'both',
            'sales_account_id' => $salesVatAccount?->id,
            'purchase_account_id' => $purchaseVatAccount?->id,
            'is_active' => true,
        ]);

        TaxRate::firstOrCreate([
            'tax_id' => $vat18->id,
            'effective_from' => '2000-01-01',
        ], [
            'rate' => 18.0,
            'is_active' => true,
        ]);

        TaxRate::firstOrCreate([
            'tax_id' => $vat9->id,
            'effective_from' => '2000-01-01',
        ], [
            'rate' => 9.0,
            'is_active' => true,
        ]);
    }

    private function seedFiscalYear(Company $company): void
    {
        $year = now()->year;

        $start = Carbon::create($year, 1, 1);
        $end = $start->copy()->endOfYear();

        $fiscalYear = FiscalYear::firstOrCreate([
            'company_id' => $company->id,
            'start_date' => $start->toDateString(),
        ], [
            'name' => 'Exercice ' . $year,
            'end_date' => $end->toDateString(),
            'status' => 'open',
            'is_locked' => false,
        ]);

        for ($month = 1; $month <= 12; $month++) {
            $periodStart = $start->copy()->setMonth($month)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();

            Period::firstOrCreate([
                'company_id' => $company->id,
                'fiscal_year_id' => $fiscalYear->id,
                'number' => $month,
            ], [
                'name' => $periodStart->format('m/Y'),
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
                'status' => 'open',
                'is_locked' => false,
            ]);
        }
    }
}
