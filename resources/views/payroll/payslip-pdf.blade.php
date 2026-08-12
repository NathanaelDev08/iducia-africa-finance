<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bulletin de paie</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 7mm 8mm 7mm;
        }

        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.25;
            font-size: 9px;
        }

        .page {
            width: 100%;
            min-height: 277mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td, th {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: top;
        }

        .header-title {
            background: #000;
            color: #fff;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
            padding: 6px 0;
        }

        .label {
            font-weight: bold;
            font-size: 7px;
        }

        .muted {
            color: #333;
        }

        .section-title {
            background: #f3f3f3;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }

        .salary-row {
            background: #f7f7f7;
            font-size: 10px;
            font-weight: bold;
        }

        .amount-right {
            text-align: right;
            font-weight: bold;
        }

        .net-row {
            background: #f7f7f7;
            font-size: 10px;
            font-weight: bold;
            text-align: right;
        }

        .signature-cell {
            height: 46px;
            font-size: 7px;
        }

        .small {
            font-size: 6px;
        }
    </style>
</head>
<body>
@php
    $company = $payslip->company;
    $employee = $payslip->employee;
    $payRun = $payslip->payRun;
    $fmt = fn($v) => number_format((float)$v, 2, ',', ' ');
    $fdate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
    $currency = $company->currency ?? 'FCFA';
    $gross = (float)($payslip->gross_salary ?? 0);
    $totalDeductions = (float)($payslip->total_deductions ?? 0);
    $net = (float)($payslip->net_salary ?? 0);
    $employerContrib = (float)($payslip->employer_contributions ?? 0);
    $incomeTax = (float)($payslip->income_tax ?? 0);
    $totalRetenues = $totalDeductions + $incomeTax;
@endphp

<div class="page">
    <table>
        <tr>
            <td colspan="2" class="header-title">BULLETIN DE PAIE</td>
        </tr>

        <tr>
            <td colspan="2" class="section-title">Employeur</td>
        </tr>
        <tr>
            <td style="width:50%;"><span class="label">Nom :</span> {{ strtoupper($company->name ?? 'FIDUCIA AFRICA') }}</td>
            <td style="width:50%;"><span class="label">Adresse :</span> {{ $company->address ?? '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">Téléphone :</span> {{ $company->phone ?? '—' }}</td>
            <td><span class="label">Email :</span> {{ $company->email ?? '—' }}</td>
        </tr>

        <tr>
            <td colspan="2" class="section-title">Salarié</td>
        </tr>
        <tr>
            <td><span class="label">Nom :</span> {{ trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: '—' }}</td>
            <td><span class="label">Matricule :</span> {{ $employee->matricule ?? '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">Poste :</span> {{ $employee->position?->name ?? '—' }}</td>
            <td><span class="label">N° CNPS :</span> {{ $employee->cnps_number ?? '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">Période :</span> {{ $fdate($payRun->period_start) }} au {{ $fdate($payRun->period_end) }}</td>
            <td><span class="label">Date de paiement :</span> {{ $fdate($payRun->payment_date) }}</td>
        </tr>

        <tr>
            <td colspan="2" class="salary-row">
                <table style="border:none; width:100%;">
                    <tr>
                        <td style="border:none; width:60%;">SALAIRE BRUT</td>
                        <td style="border:none; width:40%; text-align:right;">{{ $fmt($gross) }} {{ $currency }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td colspan="2" style="padding:0;">
                <table>
                    <thead>
                        <tr>
                            <th style="width:45%;">Rubrique</th>
                            <th style="width:20%;">Base</th>
                            <th style="width:15%;">Taux</th>
                            <th style="width:20%;">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Salaire de base</td>
                            <td class="amount-right">{{ $fmt($gross) }}</td>
                            <td class="amount-right">100%</td>
                            <td class="amount-right">{{ $fmt($gross) }}</td>
                        </tr>
                        <tr>
                            <td>CNPS salarié</td>
                            <td class="amount-right">{{ $fmt($gross) }}</td>
                            <td class="amount-right">4,8%</td>
                            <td class="amount-right">-{{ $fmt($totalDeductions) }}</td>
                        </tr>
                        <tr>
                            <td>IRPP / impôt</td>
                            <td class="amount-right">{{ $fmt($gross) }}</td>
                            <td class="amount-right">—</td>
                            <td class="amount-right">-{{ $fmt($incomeTax) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total retenues</strong></td>
                            <td></td>
                            <td></td>
                            <td class="amount-right"><strong>{{ $fmt($totalRetenues) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>

        <tr>
            <td colspan="2" class="net-row">
                NET À PAYER : {{ $fmt($net) }} {{ $currency }}
            </td>
        </tr>

        <tr>
            <td colspan="2" class="section-title">Cotisations patronales</td>
        </tr>
        <tr>
            <td>CNPS employeur</td>
            <td class="amount-right">{{ $fmt($employerContrib) }} {{ $currency }}</td>
        </tr>
        <tr>
            <td><strong>Coût total employeur</strong></td>
            <td class="amount-right"><strong>{{ $fmt($gross + $employerContrib) }} {{ $currency }}</strong></td>
        </tr>

        <tr>
            <td colspan="2" class="section-title">Signatures</td>
        </tr>
        <tr class="signature-cell">
            <td>
                <span class="label">Signature salarié</span><br><br>
                Nom : ........................................<br>
                Date : ........................................
            </td>
            <td>
                <span class="label">Signature employeur</span><br><br>
                Nom : ........................................<br>
                Cachet / Signature : ........................
            </td>
        </tr>

        <tr>
            <td colspan="2" class="small" style="text-align:center; padding:4px;">
                N° CNPS : {{ $employee->cnps_number ?? '—' }} | IFU : {{ $company->tax_id ?? '—' }} | Document généré le {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
        <tr>
            <td colspan="2" class="small" style="text-align:center; padding:3px;">
                Ce bulletin de paie est établi conformément aux règles de paie en vigueur. À conserver comme justificatif de paiement.
            </td>
        </tr>
    </table>
</div>
</body>
</html>

