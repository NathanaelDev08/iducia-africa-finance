<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $doc['title'] }} {{ $doc['number'] }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        @page { size: A4; margin: 8mm; }
        html, body { background: #f4f6f8; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1d1d1d; padding: 0; }
        .page { background: #fff; min-height: 277mm; padding: 10mm; }
        table { border-collapse: collapse; width: 100%; }
        .header-table td { vertical-align: top; }
        .company-name { font-size: 12px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .company-info { font-size: 8.8px; color: #475569; line-height: 1.4; }
        .title { font-size: 18px; font-weight: 800; color: #166534; letter-spacing: 0.6px; margin-bottom: 4px; }
        .subtitle { font-size: 9px; color: #475569; line-height: 1.4; }
        .meta { text-align: right; font-size: 9px; color: #475569; }
        .meta .number { font-size: 12px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .status-pill { display: inline-block; margin-top: 8px; padding: 4px 8px; border: 1px solid #166534; border-radius: 4px; color: #166534; font-size: 8.5px; font-weight: 700; }
        .section-title { font-size: 9.2px; font-weight: 700; color: #166534; margin-bottom: 6px; }
        .card { border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden; }
        .card th, .card td { padding: 7px 8px; border: 1px solid #e5e7eb; }
        .card th { background: #f8fafc; font-weight: 700; }
        .card td.label { width: 32%; font-weight: 700; color: #166534; }
        .card td.value { color: #111827; }
        .highlight { background: #ecfdf5; font-weight: 700; color: #166534; }
        .summary-table td { padding: 7px 8px; border: 1px solid #e5e7eb; }
        .summary-table .label { font-weight: 700; }
        .summary-table .total { background: #166534; color: #fff; font-weight: 700; }
        .signatures { display: flex; justify-content: space-between; gap: 10px; margin-top: 10px; }
        .sign-box { width: 49%; min-height: 40mm; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; font-size: 9px; color: #475569; }
        .sign-box strong { display: block; margin-bottom: 6px; color: #111827; }
        .footer { margin-top: 12px; font-size: 8px; color: #64748b; text-align: center; line-height: 1.4; }
    </style>
</head>
<body>
@php
    $fmt = fn($v) => number_format((float)$v, 0, ',', ' ');
    $fdate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
    $currency = $company->currency ?? 'FCFA';
    $statusLabels = ['paid' => 'PAYÉE', 'pending' => 'EN ATTENTE', 'overdue' => 'EN RETARD', 'draft' => 'BROUILLON'];
@endphp

<div class="page">
    <table class="header-table" style="margin-bottom:8px;">
        <tr>
            <td style="width:50%;">
                @if($logo)
                    <img src="{{ $logo }}" alt="Logo" style="max-height:40px; width:auto; display:block; margin-bottom:6px;">
                @endif
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-info">
                    @if($company->address){{ $company->address }}<br>@endif
                    @if($company->phone)Tél : {{ $company->phone }}<br>@endif
                    @if($company->email){{ $company->email }}<br>@endif
                    @if($company->tax_number)N° Fiscal : {{ $company->tax_number }}@endif
                </div>
            </td>
            <td class="meta" style="width:50%;">
                <div class="number">Réf. {{ $doc['number'] }}</div>
                <div>Date : {{ $fdate($doc['date']) }}</div>
                @if($doc['due_date'])<div>Échéance : {{ $fdate($doc['due_date']) }}</div>@endif
                <div class="status-pill">{{ $statusLabels[$doc['status']] ?? strtoupper($doc['status']) }}</div>
            </td>
        </tr>
    </table>

    <div style="margin-bottom:10px;">
        <div class="title">{{ $doc['title'] }}</div>
        <div class="subtitle">Document officiel de facturation</div>
    </div>

    <div class="section" style="margin-bottom:10px;">
        <div class="section-title">{{ $doc['kind'] === 'sale' ? 'Facturé à' : 'Fournisseur' }}</div>
        <table class="card">
            <tbody>
                <tr>
                    <td class="label">Nom</td>
                    <td class="value">{{ $doc['party']['name'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Adresse</td>
                    <td class="value">{{ $doc['party']['address'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Contact</td>
                    <td class="value">{{ $doc['party']['phone'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td class="value">{{ $doc['party']['email'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">N° Fiscal</td>
                    <td class="value">{{ $doc['party']['tax_number'] ?? '—' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section" style="margin-bottom:10px;">
        <div class="section-title">Détails de la facture</div>
        <table class="card">
            <thead>
                <tr>
                    <th style="width:6%;">#</th>
                    <th style="width:48%;">Désignation</th>
                    <th style="width:10%; text-align:center;">Qté</th>
                    <th style="width:14%; text-align:right;">P.U. HT</th>
                    <th style="width:8%; text-align:center;">TVA</th>
                    <th style="width:14%; text-align:right;">Total HT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($doc['items'] as $i => $item)
                <tr>
                    <td style="text-align:center;">{{ $i + 1 }}</td>
                    <td>{{ $item['label'] }}</td>
                    <td style="text-align:center;">{{ $item['qty'] }}</td>
                    <td style="text-align:right;">{{ $fmt($item['pu']) }}</td>
                    <td style="text-align:center;">{{ number_format((float)$item['tax'], 0) }}%</td>
                    <td style="text-align:right;">{{ $fmt($item['total']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section" style="width:50%; margin-left:auto;">
        <table class="summary-table">
            <tr>
                <td class="label">Total HT</td>
                <td class="value" style="text-align:right;">{{ $fmt($doc['total_ht']) }} {{ $currency }}</td>
            </tr>
            <tr>
                <td class="label">TVA</td>
                <td class="value" style="text-align:right;">{{ $fmt($doc['total_tax']) }} {{ $currency }}</td>
            </tr>
            <tr>
                <td class="total">TOTAL TTC</td>
                <td class="total" style="text-align:right;">{{ $fmt($doc['total_ttc']) }} {{ $currency }}</td>
            </tr>
            @if($doc['amount_paid'] > 0)
            <tr>
                <td class="label" style="color:#166534;">Déjà payé</td>
                <td class="value" style="text-align:right; color:#166534;">- {{ $fmt($doc['amount_paid']) }} {{ $currency }}</td>
            </tr>
            <tr>
                <td class="label">Restant dû</td>
                <td class="value" style="text-align:right;">{{ $fmt($doc['total_ttc'] - $doc['amount_paid']) }} {{ $currency }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="signatures">
        <div class="sign-box">
            <strong>Signature du client</strong>
            Nom :<br><br>
            Date :<br><br>
            Signature :
        </div>
        <div class="sign-box">
            <strong>Signature émetteur</strong>
            Nom :<br><br>
            Date :<br><br>
            Cachet / Signature :
        </div>
    </div>

    <div class="footer">
        Document généré par FIDUCIA ERP le {{ now()->format('d/m/Y à H:i') }}.
    </div>
</div>
</body>
</html>
