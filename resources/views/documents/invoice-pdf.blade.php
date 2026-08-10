<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>{{ $doc['title'] }} {{ $doc['number'] }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    @page { size: A4; margin: 10mm 10mm 12mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
    table { border-collapse: collapse; width: 100%; }
    .right { text-align: right; } .center { text-align: center; }
</style></head>
<body>
@php
    $fmt = fn($v) => number_format((float)$v, 0, ',', ' ');
    $fdate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
    $currency = $company->currency ?? 'FCFA';
    $statusLabels = ['paid' => 'PAYÉE', 'pending' => 'EN ATTENTE', 'overdue' => 'EN RETARD', 'draft' => 'BROUILLON'];
@endphp

<div style="position:relative; min-height:270mm;">
    <!-- EN-TÊTE : logo système G + entreprise D -->
    <table style="margin-bottom:6mm;">
        <tr>
            <td style="width:30%; vertical-align:top; border:none;">
                @if($logo)<img src="{{ $logo }}" style="height:45px; width:auto;" />@endif
                <div style="font-size:7px; color:#666; margin-top:2px;">FIDUCIA ERP — Paie, Compta & Fiscalité</div>
            </td>
            <td style="width:40%; text-align:center; vertical-align:top; border:none;">
                <div style="font-size:16px; font-weight:bold; letter-spacing:2px; color:#1a3a6a;">{{ $doc['title'] }}</div>
                <div style="font-size:10px; margin-top:2px;">N° <strong>{{ $doc['number'] }}</strong></div>
                <div style="font-size:8px; color:#555; margin-top:1px;">
                    Date : {{ $fdate($doc['date']) }} @if($doc['due_date']) | Échéance : {{ $fdate($doc['due_date']) }} @endif
                </div>
            </td>
            <td style="width:30%; text-align:right; vertical-align:top; border:none;">
                @if($companyLogo)<img src="{{ $companyLogo }}" style="height:38px; width:auto;" /><br>@endif
                <div style="font-size:10px; font-weight:bold; color:#1a3a6a;">{{ $company->name }}</div>
                <div style="font-size:7px; color:#555; line-height:1.5;">
                    @if($company->address){{ $company->address }}<br>@endif
                    @if($company->phone)Tél : {{ $company->phone }}<br>@endif
                    @if($company->email){{ $company->email }}<br>@endif
                    @if($company->tax_number)N° Fiscal : {{ $company->tax_number }}@endif
                </div>
            </td>
        </tr>
    </table>

    <!-- STATUT -->
    <div style="text-align:right; margin-bottom:4mm;">
        <span style="display:inline-block; padding:2px 10px; border:1.5px solid #1a3a6a; color:#1a3a6a; font-weight:bold; font-size:8px; border-radius:3px;">
            {{ $statusLabels[$doc['status']] ?? strtoupper($doc['status']) }}
        </span>
    </div>

    <!-- TIERS -->
    <table style="margin-bottom:6mm;">
        <tr>
            <td style="border:none; width:45%;"></td>
            <td style="background:#f0f4fb; border:1px solid #9aa7d6; border-radius:4px; padding:4mm; width:55%;">
                <div style="font-size:8px; font-weight:bold; color:#1a3a6a; margin-bottom:2mm;">
                    {{ $doc['kind'] === 'sale' ? 'FACTURÉ À' : 'FOURNISSEUR' }}
                </div>
                <div style="font-size:10px; font-weight:bold;">{{ $doc['party']['name'] ?? '—' }}</div>
                <div style="font-size:8px; color:#444; line-height:1.6;">
                    @if($doc['party'] && $doc['party']['address']){{ $doc['party']['address'] }}<br>@endif
                    @if($doc['party'] && $doc['party']['phone'])Tél : {{ $doc['party']['phone'] }}<br>@endif
                    @if($doc['party'] && $doc['party']['email']){{ $doc['party']['email'] }}<br>@endif
                    @if($doc['party'] && $doc['party']['tax_number'])N° Fiscal : {{ $doc['party']['tax_number'] }}@endif
                </div>
            </td>
        </tr>
    </table>

    <!-- LIGNES -->
    <table style="margin-bottom:5mm;">
        <thead>
            <tr>
                <th style="background:#1a3a6a; color:#fff; padding:2.5mm 2mm; border:1px solid #1a3a6a; width:6%; text-align:center;">#</th>
                <th style="background:#1a3a6a; color:#fff; padding:2.5mm 2mm; border:1px solid #1a3a6a; width:48%; text-align:left; padding-left:3mm;">Désignation</th>
                <th style="background:#1a3a6a; color:#fff; padding:2.5mm 2mm; border:1px solid #1a3a6a; width:10%;" class="center">Qté</th>
                <th style="background:#1a3a6a; color:#fff; padding:2.5mm 2mm; border:1px solid #1a3a6a; width:14%;" class="right">P.U. HT</th>
                <th style="background:#1a3a6a; color:#fff; padding:2.5mm 2mm; border:1px solid #1a3a6a; width:8%;" class="center">TVA</th>
                <th style="background:#1a3a6a; color:#fff; padding:2.5mm 2mm; border:1px solid #1a3a6a; width:14%;" class="right">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($doc['items'] as $i => $item)
            <tr>
                <td style="border:1px solid #ccc; padding:2mm; text-align:center;">{{ $i + 1 }}</td>
                <td style="border:1px solid #ccc; padding:2mm 3mm;">{{ $item['label'] }}</td>
                <td style="border:1px solid #ccc; padding:2mm; text-align:center;">{{ $item['qty'] }}</td>
                <td style="border:1px solid #ccc; padding:2mm; text-align:right;">{{ $fmt($item['pu']) }}</td>
                <td style="border:1px solid #ccc; padding:2mm; text-align:center;">{{ number_format((float)$item['tax'], 0) }}%</td>
                <td style="border:1px solid #ccc; padding:2mm; text-align:right;">{{ $fmt($item['total']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TOTAUX -->
    <table style="width:50%; margin-left:50%;">
        <tr><td style="padding:2mm 3mm; border:1px solid #ccc;">Total HT</td><td class="right" style="padding:2mm 3mm; border:1px solid #ccc;">{{ $fmt($doc['total_ht']) }} {{ $currency }}</td></tr>
        <tr><td style="padding:2mm 3mm; border:1px solid #ccc;">TVA (18%)</td><td class="right" style="padding:2mm 3mm; border:1px solid #ccc;">{{ $fmt($doc['total_tax']) }} {{ $currency }}</td></tr>
        <tr><td style="padding:2.5mm 3mm; background:#1a3a6a; color:#fff; font-weight:bold; border:1px solid #1a3a6a;">TOTAL TTC</td><td class="right" style="padding:2.5mm 3mm; background:#1a3a6a; color:#fff; font-weight:bold; border:1px solid #1a3a6a;">{{ $fmt($doc['total_ttc']) }} {{ $currency }}</td></tr>
        @if($doc['amount_paid'] > 0)
        <tr><td style="padding:2mm 3mm; border:1px solid #ccc; color:#2d6a4f;">Déjà payé</td><td class="right" style="padding:2mm 3mm; border:1px solid #ccc; color:#2d6a4f;">- {{ $fmt($doc['amount_paid']) }} {{ $currency }}</td></tr>
        <tr><td style="padding:2mm 3mm; border:1px solid #ccc; font-weight:bold;">Restant dû</td><td class="right" style="padding:2mm 3mm; border:1px solid #ccc; font-weight:bold;">{{ $fmt($doc['total_ttc'] - $doc['amount_paid']) }} {{ $currency }}</td></tr>
        @endif
    </table>

    <!-- PIED DE PAGE -->
    <div style="position:absolute; bottom:0; left:0; right:0;">
        <div style="border-top:1px solid #bbb; padding-top:3mm; font-size:7px; color:#555; text-align:center; line-height:1.6;">
            {{ $company->name }} @if($company->tax_number)— {{ $company->tax_number }} @endif — {{ $company->address ?? '' }}<br>
            Paiement à réception par virement bancaire ou Mobile Money. Pénalités de retard : 1,5% par mois.
            <br><em>Document généré par FIDUCIA ERP le {{ now()->format('d/m/Y à H:i') }}</em>
        </div>
    </div>
</div>
</body></html>
