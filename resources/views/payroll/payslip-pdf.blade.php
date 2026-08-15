<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bulletin de paie</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 9mm 10mm 9mm;
        }

        * {
            box-sizing: border-box;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #1a1a1a;
            font-family: Helvetica, Arial, sans-serif;
            line-height: 1.3;
            font-size: 9px;
        }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td, th { vertical-align: top; padding: 0; }
        .col-num { text-align: center; }

        .muted { color: #555; }
        .bold { font-weight: bold; }
        .right { text-align: right; }
        .center { text-align: center; }

        /* ===== En-tête ===== */
        .top-header td { vertical-align: top; }
        .brand-name { font-size: 20px; font-weight: bold; color: #0f6d5c; }
        .brand-name .accent { color: #1a1a1a; }
        .brand-lines { font-size: 7.5px; color: #444; margin-top: 3px; line-height: 1.5; }
        .doc-title-block { text-align: right; }
        .doc-title { font-size: 18px; font-weight: bold; color: #0f6d5c; letter-spacing: 0.5px; }
        .doc-meta { font-size: 8px; color: #333; margin-top: 4px; line-height: 1.6; }
        .doc-meta strong { color: #000; }

        .separator {
            border-top: 2px solid #0f6d5c;
            margin: 6px 0 8px 0;
        }

        /* ===== Boîtes d'information (3 colonnes) ===== */
        .info-grid td {
            border: 1px solid #cfd8d6;
            border-radius: 3px;
            padding: 6px 7px;
            width: 33.33%;
        }
        .info-grid td + td { border-left: 1px solid #cfd8d6; }
        .info-title { font-size: 7px; font-weight: bold; color: #0f6d5c; letter-spacing: 0.5px; margin-bottom: 3px; }
        .info-name { font-size: 10px; font-weight: bold; margin-bottom: 1px; }
        .info-line { font-size: 8px; color: #333; margin-top: 2px; }

        .grid-spacer { height: 6px; }

        /* ===== Ligne de compteurs (congés / heures) ===== */
        .stats-row td {
            border: 1px solid #cfd8d6;
            border-top: none;
            padding: 5px 4px;
            text-align: center;
            width: 20%;
        }
        .stats-row td + td { border-left: 1px solid #cfd8d6; }
        .stat-value { font-size: 11px; font-weight: bold; color: #0f6d5c; }
        .stat-label { font-size: 6.5px; color: #555; margin-top: 1px; }

        /* ===== Tableau des rubriques ===== */
        .rubriques { margin-top: 8px; }
        .rubriques th {
            background: #0f6d5c;
            color: #fff;
            font-size: 6.8px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 4px;
            border: 1px solid #0f6d5c;
        }
        .rubriques td {
            font-size: 8px;
            padding: 3px 4px;
            border-bottom: 1px solid #e3e8e7;
            border-left: 1px solid #e3e8e7;
            border-right: 1px solid #e3e8e7;
        }
        .rubriques .col-num { width: 6%; }
        .rubriques .col-label { width: 26%; }
        .rubriques .col-base { width: 15%; }
        .rubriques .col-taux { width: 10%; }
        .rubriques .col-amt { width: 11%; }

        .row-total-brut td {
            background: #eaf3f1;
            font-weight: bold;
            border-top: 1.5px solid #0f6d5c;
            border-bottom: 1.5px solid #0f6d5c;
        }

        /* ===== Totaux / Net à payer ===== */
        .totals-block { margin-top: 6px; }
        .totals-block td { padding: 3px 4px; font-size: 8.5px; }
        .totals-block .label { width: 80%; }
        .totals-block .amt { width: 20%; text-align: right; font-weight: bold; }
        .net-row td {
            background: #0f6d5c;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 6px 5px;
        }

        .arrete {
            font-size: 8px;
            font-style: italic;
            margin: 6px 0 8px 0;
            color: #222;
        }

        /* ===== Cumulés ===== */
        .cumules th {
            background: #0f6d5c;
            color: #fff;
            font-size: 6.8px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px;
            border: 1px solid #0f6d5c;
            text-align: right;
        }
        .cumules th:first-child { text-align: left; }
        .cumules td {
            font-size: 8px;
            padding: 4px;
            border-bottom: 1px solid #e3e8e7;
            text-align: right;
        }
        .cumules td:first-child { text-align: left; font-weight: bold; }

        /* ===== Signatures ===== */
        .signatures { margin-top: 12px; }
        .sig-box {
            border: 1px dashed #999;
            border-radius: 3px;
            height: 60px;
            width: 48%;
            padding: 6px;
            font-size: 8px;
            color: #555;
            text-align: center;
            vertical-align: middle;
        }
        .sig-spacer { width: 4%; }

        /* ===== Pied de page ===== */
        .footer-note {
            font-size: 6.5px;
            color: #666;
            text-align: center;
            margin-top: 10px;
            border-top: 1px solid #cfd8d6;
            padding: 4px 10px 0 10px;
        }
    </style>
</head>
<body>
@php
    $fmt = fn ($v) => number_format((float) $v, 0, ',', ' ');
    $fmtRate = fn ($v) => $v === null ? '' : number_format((float) $v, 2, ',', ' ');
    $fdate = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/y') : '—';
    $currency = $company->currency ?? 'XOF';
    $currencyLabel = $currency === 'XOF' ? 'FCFA' : $currency;

    $civility = [
        'single' => 'Célibataire', 'married' => 'Marié(e)', 'divorced' => 'Divorcé(e)', 'widowed' => 'Veuf(ve)',
    ][$employee->marital_status] ?? null;

    $seniorityLabel = $seniority ? "{$seniority['years']} an(s) et {$seniority['months']} mois" : '—';
    $ccn = $employee->collective_agreement ?: 'CCN Côte d\'Ivoire du 20/07/1977';
@endphp

<table class="top-header">
    <tr>
        <td style="width:55%;">
            <div class="brand-name">{{ mb_strtoupper($company->short_name ?: $company->name) }}</div>
            <div class="brand-lines">
                {{ $company->name }}<br>
                {{ $company->address ?: '—' }}<br>
                RCCM : {{ $company->rccm ?: '—' }} &middot; N° CC : {{ $company->ncc ?: '—' }}<br>
                N° CNPS employeur : {{ $company->social_id ?: '—' }}
            </div>
        </td>
        <td class="doc-title-block" style="width:45%;">
            <div class="doc-title">BULLETIN DE PAIE</div>
            <div class="doc-meta">
                <strong>Période :</strong> du {{ $fdate($payRun?->period_start) }} au {{ $fdate($payRun?->period_end) }}<br>
                <strong>Paiement le</strong> {{ $fdate($payRun?->payment_date) }} par {{ $employee->payment_method === 'bank' ? 'virement' : ($employee->payment_method ?: 'virement') }}<br>
                République de Côte d'Ivoire
            </div>
        </td>
    </tr>
</table>

<div class="separator"></div>

<table class="info-grid">
    <tr>
        <td>
            <div class="info-title">SALARIÉ(E)</div>
            <div class="info-name">{{ mb_strtoupper(trim($employee->first_name . ' ' . $employee->last_name)) ?: '—' }}</div>
            <div class="info-line">{{ $employee->address ?: '—' }}</div>
            <div class="info-line">Matricule : {{ $employee->matricule ?? '—' }} &middot; N° SS : {{ $employee->cnps_number ?? '—' }}</div>
            <div class="info-line">
                @if($employee->birth_date) Né(e) le {{ $employee->birth_date->format('d F Y') }} &middot; @endif
                {{ $civility ?? '—' }}{{ $employee->dependents_count ? ', ' . $employee->dependents_count . ' enfant(s)' : '' }}
            </div>
        </td>
        <td>
            <div class="info-title">EMPLOI &amp; CLASSIFICATION</div>
            <div class="info-name">{{ mb_strtoupper($employee->position?->name ?? '—') }}</div>
            <div class="info-line">Département : {{ mb_strtoupper($employee->department?->name ?? '—') }}</div>
            <div class="info-line">Catégorie : {{ $employee->professional_category ?: '—' }} &middot; Qualification : {{ $employee->position?->name ?: '—' }}</div>
            <div class="info-line">Niveau — &middot; Coefficient — &middot; Indice —</div>
        </td>
        <td>
            <div class="info-title">CONTRAT &amp; TEMPS</div>
            <div class="info-name">{{ $contract?->contractType?->name ?? '—' }} &mdash; {{ $ccn }}</div>
            <div class="info-line">Embauche : {{ $employee->hire_date ? $employee->hire_date->format('d M. Y') : '—' }}</div>
            <div class="info-line">Ancienneté : {{ $seniorityLabel }}</div>
            <div class="info-line">Horaire mensuel : <strong>{{ number_format($monthlyHours, 3, ',', ' ') }} h</strong> &middot; Banque : {{ $employee->bank_name ?: '—' }}</div>
        </td>
    </tr>
</table>

<table class="stats-row">
    <tr>
        <td>
            <div class="stat-value">{{ number_format($leaveAccrualRate, 3, ',', ' ') }}</div>
            <div class="stat-label">Congés acquis (mois)</div>
        </td>
        <td>
            <div class="stat-value">{{ number_format($leaveRemaining, 3, ',', ' ') }}</div>
            <div class="stat-label">Reste à prendre</div>
        </td>
        <td>
            <div class="stat-value">{{ number_format($leaveTakenThisYear, 3, ',', ' ') }}</div>
            <div class="stat-label">Pris (année)</div>
        </td>
        <td>
            <div class="stat-value">{{ number_format($monthlyHours, 0, ',', ' ') }}</div>
            <div class="stat-label">Heures travaillées</div>
        </td>
        <td>
            <div class="stat-value">{{ number_format($overtimeHours, 0, ',', ' ') }}</div>
            <div class="stat-label">Heures sup.</div>
        </td>
    </tr>
</table>

@php
    $earnings = $payslip->items->where('type', 'earning')->sortBy('display_order');
    $employeeLines = $payslip->items->whereIn('type', ['employee_contribution', 'tax', 'deduction'])->sortBy('display_order');
    $employerLines = $payslip->items->where('type', 'employer_contribution')->sortBy('display_order');

    $rows = [];
    $n = 100;

    // Le salaire de base n'est pas une PayslipItem (colonne dédiée sur le bulletin) :
    // on l'ajoute en première ligne pour reproduire l'ordre habituel d'un bulletin.
    if ((float) $payslip->base_salary > 0) {
        $rows[] = [
            'n' => $n, 'label' => 'Salaire de base', 'rate' => null, 'base' => null,
            'gain' => $payslip->base_salary, 'retenue' => null, 'tauxPat' => null, 'retenuePat' => null,
        ];
        $n += 10;
    }

    foreach ($earnings as $item) {
        $rows[] = [
            'n' => $n, 'label' => $item->name, 'rate' => $item->rate, 'base' => $item->base_amount,
            'gain' => $item->amount, 'retenue' => null, 'tauxPat' => null, 'retenuePat' => null,
        ];
        $n += 10;
    }
    $n = 600;
    foreach ($employeeLines as $item) {
        $rows[] = [
            'n' => $n, 'label' => $item->name, 'rate' => $item->rate, 'base' => $item->base_amount,
            'gain' => null, 'retenue' => $item->amount, 'tauxPat' => null, 'retenuePat' => null,
        ];
        $n++;
    }
    $n = 670;
    foreach ($employerLines as $item) {
        $rows[] = [
            'n' => $n, 'label' => $item->name, 'rate' => $item->rate, 'base' => $item->base_amount,
            'gain' => null, 'retenue' => null, 'tauxPat' => $item->rate, 'retenuePat' => $item->amount,
        ];
        $n++;
    }
@endphp

<table class="rubriques">
    <thead>
        <tr>
            <th class="col-num">N°</th>
            <th class="col-label">Rubrique</th>
            <th class="col-base right">Base</th>
            <th class="col-taux right">Taux</th>
            <th class="col-amt right">Gain</th>
            <th class="col-amt right">Retenue</th>
            <th class="col-amt right">Taux pat.</th>
            <th class="col-amt right">Retenue pat.</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                <td class="col-num">{{ $row['n'] }}</td>
                <td class="col-label">{{ $row['label'] }}</td>
                <td class="col-base right">{{ $row['rate'] !== null ? $fmt($row['base']) : '' }}</td>
                <td class="col-taux right">{{ $row['rate'] !== null ? $fmtRate($row['rate']) : '' }}</td>
                <td class="col-amt right">{{ $row['gain'] !== null ? $fmt($row['gain']) : '' }}</td>
                <td class="col-amt right">{{ $row['retenue'] !== null ? $fmt($row['retenue']) : '' }}</td>
                <td class="col-amt right">{{ $row['tauxPat'] !== null ? $fmtRate($row['tauxPat']) : '' }}</td>
                <td class="col-amt right">{{ $row['retenuePat'] !== null ? $fmt($row['retenuePat']) : '' }}</td>
            </tr>
        @endforeach
        <tr class="row-total-brut">
            <td colspan="4">TOTAL BRUT</td>
            <td class="right" colspan="4">{{ $fmt($payslip->gross_salary) }}</td>
        </tr>
    </tbody>
</table>

<table class="totals-block">
    <tr>
        <td class="label">Total cotisations salariales</td>
        <td class="amt">{{ $fmt($payslip->total_deductions) }}</td>
    </tr>
    <tr>
        <td class="label">Total charges patronales</td>
        <td class="amt">{{ $fmt($payslip->employer_contributions) }}</td>
    </tr>
    <tr class="net-row">
        <td class="label">NET À PAYER</td>
        <td class="amt">{{ $fmt($payslip->net_salary) }} {{ $currencyLabel }}</td>
    </tr>
</table>

<p class="arrete">
    Arrêté le présent bulletin à la somme de :
    <em>{{ ucfirst(\App\Support\FrenchNumberFormatter::toWords((int) round($payslip->net_salary))) }} francs CFA.</em>
</p>

<table class="cumules">
    <thead>
        <tr>
            <th>Cumulés</th>
            <th>Salaire brut</th>
            <th>Net imposable</th>
            <th>Charges sal.</th>
            <th>Charges pat.</th>
            <th>Heures</th>
            <th>HS</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Période</td>
            <td>{{ $fmt($cumulPeriode['brut']) }}</td>
            <td>{{ $fmt($cumulPeriode['net_imposable']) }}</td>
            <td>{{ $fmt($cumulPeriode['charges_sal']) }}</td>
            <td>{{ $fmt($cumulPeriode['charges_pat']) }}</td>
            <td>{{ number_format($cumulPeriode['heures'], 0, ',', ' ') }}</td>
            <td>{{ number_format($cumulPeriode['hs'], 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>Année</td>
            <td>{{ $fmt($cumulAnnee['brut']) }}</td>
            <td>{{ $fmt($cumulAnnee['net_imposable']) }}</td>
            <td>{{ $fmt($cumulAnnee['charges_sal']) }}</td>
            <td>{{ $fmt($cumulAnnee['charges_pat']) }}</td>
            <td>{{ number_format($cumulAnnee['heures'], 0, ',', ' ') }}</td>
            <td>{{ number_format($cumulAnnee['hs'], 0, ',', ' ') }}</td>
        </tr>
    </tbody>
</table>

<table class="signatures">
    <tr>
        <td class="sig-box">L'employeur (cachet)</td>
        <td class="sig-spacer"></td>
        <td class="sig-box">Le/La salarié(e)</td>
    </tr>
</table>

<div class="footer-note">
    Dans votre intérêt et pour vous aider à faire valoir vos droits, conservez ce bulletin sans limitation de durée.
    &mdash; {{ $company->name }} &middot; RCCM {{ $company->rccm ?: '—' }} &middot; N° CNPS {{ $company->social_id ?: '—' }}
</div>
</body>
</html>
