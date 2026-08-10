<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Bulletin {{ $payslip->slip_number ?? $payslip->id }}</title>
<style>
@page { size: A4 portrait; margin: 10mm; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 7.5px; color: #111; line-height: 1.3; }
table { border-collapse: collapse; width: 100%; page-break-inside: avoid; }
td, th { vertical-align: top; word-wrap: break-word; }
.r { text-align: right; } .c { text-align: center; } .l { text-align: left; }
.b { font-weight: 700; } .bb { font-weight: 900; }
.blue { color: #2E5090; }
.cell { border: 1px solid #d0d7e2; padding: 1.8mm 2mm; font-size: 7px; }
.h { background: #2E5090; color: #fff; border: 1px solid #2E5090; padding: 2mm; font-weight: 700; font-size: 7px; }
.sec { background: #2E5090; color: #fff; border: 1px solid #2E5090; padding: 1.6mm 2mm; font-weight: 700; font-size: 7px; }
.soft { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 1.8mm 2mm; font-weight: 700; font-size: 7px; }
.tsm { background: #3d5ea8; color: #fff; border: 1px solid #3d5ea8; padding: 1.6mm 2mm; font-weight: 600; font-size: 6.8px; }
.band { background: #2E5090; color: #fff; }
.band-dk { background: #1e3a6a; color: #fff; }
.mb { margin-bottom: 3mm; }
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
    $gross = (float) $payslip->gross_salary;
    $totSalDed = (float) $payslip->total_employee_contributions;
    $totPat = (float) $payslip->total_employer_contributions;
    $ir = (float) $payslip->income_tax;
    $taxable = (float) $payslip->taxable_income ?: ($gross - $totSalDed);
    $net = (float) $payslip->net_salary;
    $netAvant = $gross - $totSalDed;
    $coutGlobal = $gross + $totPat;
    $methods = ['bank' => 'Virement bancaire', 'virement' => 'Virement bancaire', 'mobile_money' => 'Mobile Money', 'cash' => 'Especes', 'cheque' => 'Cheque'];
    $methodLabel = $methods[$employee->payment_method ?? ''] ?? 'Virement bancaire';
    $logo = null;
    $sp = public_path('images/logo.png');
    if (file_exists($sp)) $logo = 'data:image/png;base64,' . base64_encode(file_get_contents($sp));

    // ═══ AUTO-RECONSTITUTION des lignes si absentes ═══
    $items = collect($payslip->items ?? []);
    if ($items->isEmpty() && $gross > 0) {
        $base = (float) ($payslip->base_salary ?? 0);
        if ($base <= 0) $base = max(0, ($gross - 60000) / 1.05);
        $syn = [
            ['name' => 'Salaire de base', 'base_amount' => $base, 'rate' => 100, 'amount' => $base, 'type' => 'earning', 'display_order' => 1],
        ];
        $rest = $gross - $base;
        if ($rest > 0) {
            $syn[] = ['name' => 'Primes et indemnites', 'base_amount' => $base, 'rate' => $base > 0 ? round($rest/$base*100, 2) : 0, 'amount' => $rest, 'type' => 'earning', 'display_order' => 2];
        }
        if ($totSalDed > 0) {
            $syn[] = ['name' => 'CNPS Retraite (part salariale)', 'base_amount' => min($gross, 1200000), 'rate' => 4.25, 'amount' => $totSalDed, 'type' => 'employee_contribution', 'display_order' => 10];
        }
        if ($totPat > 0) {
            $a = round($totPat * 0.50); $b = round($totPat * 0.13);
            $syn[] = ['name' => 'CNPS Retraite (part patronale)', 'base_amount' => min($gross, 1200000), 'rate' => 7.70, 'amount' => $a, 'type' => 'employer_contribution', 'display_order' => 20];
            $syn[] = ['name' => 'Accidents du travail', 'base_amount' => min($gross, 1200000), 'rate' => 2.00, 'amount' => $b, 'type' => 'employer_contribution', 'display_order' => 21];
            $syn[] = ['name' => 'Prestations familiales', 'base_amount' => $gross, 'rate' => 5.75, 'amount' => $totPat - $a - $b, 'type' => 'employer_contribution', 'display_order' => 22];
        }
        $items = collect($syn)->map(fn($x) => (object) $x);
    }
    $items = $items->sortBy('display_order');
    $earnings = $items->filter(fn($i) => $i->type === 'earning' || ($i->is_earning ?? false));
    $salDed = $items->filter(fn($i) => in_array($i->type, ['employee_contribution', 'deduction']));
    $patItems = $items->filter(fn($i) => $i->type === 'employer_contribution');
    $stem = fn($n) => trim(str_replace(['(part salariale)', '(part patronale)'], '', strtolower($n ?? '')));
    $sections = ['SANTE' => [], 'ACCIDENTS DE TRAVAIL' => [], 'RETRAITE' => [], 'FAMILLE' => [], 'CHOMAGE' => [], 'CSG / CRDS' => [], 'AUTRES' => []];
    foreach ($salDed as $it) {
        $n = strtoupper($it->name ?? '');
        if (str_contains($n, 'MALAD') || str_contains($n, 'SANT') || str_contains($n, 'PREVOY')) $sections['SANTE'][] = $it;
        elseif (str_contains($n, 'ACCID')) $sections['ACCIDENTS DE TRAVAIL'][] = $it;
        elseif (str_contains($n, 'RETR') || str_contains($n, 'CNPS')) $sections['RETRAITE'][] = $it;
        elseif (str_contains($n, 'FAMI')) $sections['FAMILLE'][] = $it;
        elseif (str_contains($n, 'CHOM')) $sections['CHOMAGE'][] = $it;
        elseif (str_contains($n, 'CSG') || str_contains($n, 'CRDS')) $sections['CSG / CRDS'][] = $it;
        else $sections['AUTRES'][] = $it;
    }
    $orphans = $patItems->filter(fn($p) => !$salDed->contains(fn($s) => $stem($s->name) === $stem($p->name)));
    $cumuls = null;
    try {
        if ($payRun && $payRun->period_start) {
            $cumuls = \Illuminate\Support\Facades\DB::table('payslips')
                ->where('employee_id', $payslip->employee_id)
                ->whereBetween('period_start', [\Carbon\Carbon::parse($payRun->period_start)->startOfYear()->toDateString(), $payRun->period_end ? \Carbon\Carbon::parse($payRun->period_end)->toDateString() : now()->toDateString()])
                ->selectRaw('SUM(gross_salary) g, SUM(taxable_income) t, SUM(total_employee_contributions) e, SUM(total_employer_contributions) p, COUNT(*) c')
                ->first();
        }
    } catch (\Throwable $e) {}
@endphp

<!-- 1. EN-TÊTE -->
<table class="mb">
    <tr>
        <td style="width: 42%; border: 1px solid #d0d7e2; padding: 2.5mm;">
            @if($logo)<img src="{{ $logo }}" style="height: 20px; margin-bottom: 1.5mm;" /><br>@endif
            <div class="b blue" style="font-size: 9px;">{{ $company->name ?? 'ENTREPRISE' }}</div>
            <div style="font-size: 6.5px; color: #555; margin-top: 1mm;">
                {{ $company->address ?? '' }}@if($company->tax_number) · SIRET : {{ $company->tax_number }}@endif
            </div>
        </td>
        <td style="width: 16%;"></td>
        <td style="width: 42%; text-align: right; border: 1px solid #d0d7e2; padding: 2.5mm;">
            <div class="b blue" style="font-size: 13px;">BULLETIN DE PAIE</div>
            <div style="font-size: 6.8px; color: #2E5090; margin-top: 1.5mm;">
                Periode du {{ $payRun && $payRun->period_start ? \Carbon\Carbon::parse($payRun->period_start)->format('d-m-Y') : '—' }}
                au {{ $payRun && $payRun->period_end ? \Carbon\Carbon::parse($payRun->period_end)->format('d-m-Y') : '—' }}<br>
                Paiement, le {{ $payRun && $payRun->payment_date ? \Carbon\Carbon::parse($payRun->payment_date)->format('d-m-Y') : '—' }} par {{ $methodLabel }}
            </div>
            <div style="font-size: 6px; color: #888; margin-top: 1mm;">Bulletin N° {{ $payslip->slip_number ?? $payslip->id }} · Montants en {{ $currency }}</div>
        </td>
    </tr>
</table>

<!-- 2. IDENTITÉ -->
<table class="mb">
    <tr>
        <td style="width: 50%; border: 1px solid #d0d7e2; padding: 2mm;">
            <table style="width: 100%;">
                <tr><td style="width: 48%; font-size: 6.5px;" class="b blue">Matricule</td><td style="font-size: 6.5px;">{{ $employee->matricule ?? '—' }}</td></tr>
                <tr><td style="font-size: 6.5px;" class="b blue">N° Securite soc.</td><td style="font-size: 6.5px;">{{ $employee->cnps_number ?? '—' }}</td></tr>
                <tr><td style="font-size: 6.5px;" class="b blue">Poste</td><td style="font-size: 6.5px;">{{ $employee->position?->name ?? '—' }}</td></tr>
                <tr><td style="font-size: 6.5px;" class="b blue">Echelon</td><td style="font-size: 6.5px;">—</td></tr>
                <tr><td style="font-size: 6.5px;" class="b blue">Statut</td><td style="font-size: 6.5px;">{{ $employee->professional_category ?? 'Employe' }}</td></tr>
                <tr><td style="font-size: 6.5px;" class="b blue">Anciennete</td><td style="font-size: 6.5px;">{{ $fdate($employee->hire_date) }}</td></tr>
            </table>
        </td>
        <td style="width: 50%; border: 1px solid #d0d7e2; border-left: none; padding: 2mm; background: #f8fafc;">
            <div class="b blue" style="font-size: 10px;">{{ strtoupper(trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''))) }}</div>
            <div style="font-size: 6.5px; margin-top: 1mm;">{{ $employee->address ?? '—' }}</div>
            <div style="font-size: 6.2px; margin-top: 1.5mm; padding-top: 1mm; border-top: 1px dashed #cbd5e1;">
                <span class="b blue">Convention collective :</span> {{ $employee->collective_agreement ?? 'Convention generale du travail' }}
            </div>
        </td>
    </tr>
</table>

<!-- 3. TABLEAU PRINCIPAL (largeurs sur TH = pas de duplication) -->
<table class="mb">
    <thead>
        <tr>
            <th class="h l" style="width: 36%;">Libelle</th>
            <th class="h r" style="width: 12%;">Base</th>
            <th class="h r" style="width: 9%;">Taux sal.</th>
            <th class="h r" style="width: 13%;">Montant sal.</th>
            <th class="h r" style="width: 9%;">Taux pat.</th>
            <th class="h r" style="width: 21%;">Montant pat.</th>
        </tr>
    </thead>
    <tbody>
        @foreach($earnings as $it)
        <tr>
            <td class="cell">{{ $it->name }}</td>
            <td class="cell r">{{ $fmt($it->base_amount) }}</td>
            <td class="cell r">{{ $fmt($it->rate) }}</td>
            <td class="cell r">{{ $fmt($it->amount) }}</td>
            <td class="cell r">—</td>
            <td class="cell r">—</td>
        </tr>
        @endforeach
        <tr>
            <td class="soft" style="padding-left: 5mm;">Total Brut</td>
            <td class="soft r">—</td><td class="soft r">—</td>
            <td class="soft r">{{ $fmt($gross) }}</td>
            <td class="soft r">—</td><td class="soft r">—</td>
        </tr>
        @foreach($sections as $sn => $si)
            @if(count($si) > 0)
            <tr>
                <td class="sec">{{ $sn }}</td>
                <td class="sec">&nbsp;</td><td class="sec">&nbsp;</td>
                <td class="sec">&nbsp;</td><td class="sec">&nbsp;</td>
                <td class="sec">&nbsp;</td>
            </tr>
            @foreach($si as $it)
                @php $pm = $patItems->first(fn($p) => $stem($p->name) === $stem($it->name)); @endphp
                <tr>
                    <td class="cell">{{ $it->name }}</td>
                    <td class="cell r">{{ $fmt($it->base_amount) }}</td>
                    <td class="cell r">{{ $fmt($it->rate) }}</td>
                    <td class="cell r">{{ $fmt($it->amount) }}</td>
                    <td class="cell r">{{ $pm ? $fmt($pm->rate) : '—' }}</td>
                    <td class="cell r">{{ $pm ? $fmt($pm->amount) : '—' }}</td>
                </tr>
            @endforeach
            @endif
        @endforeach
        @if($orphans->count() > 0)
        <tr><td class="sec">AUTRES CONTRIBUTIONS EMPLOYEUR</td><td class="sec">&nbsp;</td><td class="sec">&nbsp;</td><td class="sec">&nbsp;</td><td class="sec">&nbsp;</td><td class="sec">&nbsp;</td></tr>
        @foreach($orphans as $p)
        <tr>
            <td class="cell">{{ $p->name }}</td>
            <td class="cell r">{{ $fmt($p->base_amount) }}</td>
            <td class="cell r">—</td><td class="cell r">—</td>
            <td class="cell r">{{ $fmt($p->rate) }}</td>
            <td class="cell r">{{ $fmt($p->amount) }}</td>
        </tr>
        @endforeach
        @endif
        <tr>
            <td class="soft" style="padding-left: 5mm;">Total Retenues</td>
            <td class="soft r">—</td><td class="soft r">—</td>
            <td class="soft r">{{ $fmt($totSalDed) }}</td>
            <td class="soft r">—</td>
            <td class="soft r">{{ $fmt($totPat) }}</td>
        </tr>
    </tbody>
</table>

<!-- 4. NET AVANT IMPÔT -->
<table class="band mb">
    <tr>
        <td class="b" style="width: 70%; padding: 2mm 3mm; font-size: 8px;">Net a payer avant impot sur le revenu</td>
        <td class="r b" style="width: 30%; padding: 2mm 3mm; font-size: 9px;">{{ $fmt($netAvant) }}</td>
    </tr>
</table>

<!-- 5. IMPÔT -->
<table class="mb">
    <tr>
        <td class="tsm" style="width: 38%;">Impot sur le revenu</td>
        <td class="tsm r" style="width: 20%;">Base</td>
        <td class="tsm r" style="width: 22%;">Taux</td>
        <td class="tsm r" style="width: 20%;">Montant</td>
    </tr>
    <tr>
        <td class="cell">Prelevement a la source</td>
        <td class="cell r">{{ $fmt($taxable) }}</td>
        <td class="cell r">{{ $taxable > 0 && $ir > 0 ? $fmt($ir/$taxable*100) : '0,00' }} %</td>
        <td class="cell r">{{ $fmt($ir) }}</td>
    </tr>
</table>

<!-- 6. CONGÉS + NET À PAYER -->
<table class="mb">
    <tr>
        <td style="width: 55%; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <td class="tsm" style="width: 40%;">Conges payes</td>
                    <td class="tsm c" style="width: 20%;">Acquis</td>
                    <td class="tsm c" style="width: 20%;">Pris</td>
                    <td class="tsm c" style="width: 20%;">Reste</td>
                </tr>
                <tr>
                    <td class="cell">CP annee en cours</td>
                    <td class="cell c">2,50</td>
                    <td class="cell c">0,00</td>
                    <td class="cell c">2,50</td>
                </tr>
            </table>
        </td>
        <td style="width: 3%;"></td>
        <td style="width: 42%;">
            <table class="band-dk" style="width: 100%;">
                <tr>
                    <td class="c" style="padding: 3mm 2mm;">
                        <div class="b" style="font-size: 7.5px;">NET A PAYER ({{ $currency }})</div>
                        <div class="b" style="font-size: 11px; margin-top: 1.5mm;">{{ $fmt($net) }}</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- 7. CUMULS + TOTAL EMPLOYEUR -->
<table class="mb">
    <tr>
        <td style="width: 68%; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <td class="tsm" style="width: 16%;">Cumuls annuels</td>
                    <td class="tsm r" style="width: 17%;">Brut</td>
                    <td class="tsm r" style="width: 17%;">Cout global</td>
                    <td class="tsm r" style="width: 17%;">Net imposable</td>
                    <td class="tsm r" style="width: 15%;">Heures</td>
                    <td class="tsm r" style="width: 18%;">Cot. pat.</td>
                </tr>
                <tr>
                    <td class="cell b">Mois</td>
                    <td class="cell r">{{ $fmt($gross) }}</td>
                    <td class="cell r">{{ $fmt($coutGlobal) }}</td>
                    <td class="cell r">{{ $fmt($taxable) }}</td>
                    <td class="cell r">173,33</td>
                    <td class="cell r">{{ $fmt($totPat) }}</td>
                </tr>
                <tr>
                    <td class="cell b">Annee</td>
                    <td class="cell r">{{ $cumuls ? $fmt($cumuls->g) : $fmt($gross) }}</td>
                    <td class="cell r">{{ $cumuls ? $fmt($cumuls->g + $cumuls->p) : $fmt($coutGlobal) }}</td>
                    <td class="cell r">{{ $cumuls ? $fmt($cumuls->t) : $fmt($taxable) }}</td>
                    <td class="cell r">{{ $cumuls ? $fmt($cumuls->c * 173.33) : '173,33' }}</td>
                    <td class="cell r">{{ $cumuls ? $fmt($cumuls->p) : $fmt($totPat) }}</td>
                </tr>
            </table>
        </td>
        <td style="width: 3%;"></td>
        <td style="width: 29%;">
            <table style="width: 100%;">
                <tr><td class="tsm c">Total verse par employeur</td></tr>
                <tr><td class="r b" style="border: 1px solid #d0d7e2; border-top: none; padding: 2.5mm; font-size: 9.5px;">{{ $fmt($coutGlobal) }}</td></tr>
                <tr><td class="tsm c">Allegement des cotisations</td></tr>
                <tr><td class="r" style="border: 1px solid #d0d7e2; border-top: none; padding: 2mm;">0,00</td></tr>
            </table>
        </td>
    </tr>
</table>

<!-- 8. SIGNATURES + PIED : position:fixed = toujours en bas de page -->
<div style="position: fixed; bottom: 0; left: 0; right: 0;">
    <table class="mb" style="margin-bottom: 2mm;">
        <tr>
            <td class="cell" style="width: 48%; height: 15mm;"><span class="b" style="font-size: 7px;">Signature du salarie</span></td>
            <td style="width: 4%;"></td>
            <td class="cell" style="width: 48%; height: 15mm;"><span class="b" style="font-size: 7px;">Signature et cachet employeur</span></td>
        </tr>
    </table>
    <div style="border-top: 1px solid #cbd5e1; padding-top: 1.5mm;">
        <div style="font-size: 6px; color: #2E5090; font-style: italic;">
            Pour vous aider a faire valoir vos droits, conservez ce bulletin de paie sans limitation de duree.
            Pour la definition des termes employes, se reporter au site internet service-public.fr.
        </div>
        <div style="text-align: right; font-size: 6px; color: #888; margin-top: 1mm;">
            Bulletin genere par <span class="b blue">FIDUCIA ERP</span> le {{ now()->format('d/m/Y a H:i') }}
        </div>
    </div>
</div>

</body>
</html>
