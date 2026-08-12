<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Reçu {{ $doc['number'] }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        @page { size: A4; margin: 8mm; }
        html, body { background: #f4f6f8; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1d1d1d; padding: 0; }
        .page { width:100%; min-height:277mm; padding: 10mm; background:#fff; }
        table { border-collapse: collapse; width: 100%; }
        .header-table td { vertical-align: top; }
        .company-name { font-size: 12px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .company-info { font-size: 8.8px; color: #475569; line-height: 1.4; }
        .title { font-size: 18px; font-weight: 800; color: #166534; letter-spacing: 0.8px; margin-bottom: 4px; }
        .subtitle { font-size: 9px; color: #475569; line-height: 1.4; }
        .meta { text-align: right; font-size: 9px; color: #475569; }
        .meta .ref { font-size: 12px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .section-title { font-size: 9.5px; font-weight: 700; color: #166534; margin-bottom: 6px; }
        .card { border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden; }
        .card td { padding: 7px 8px; }
        .card tr + tr td { border-top: 1px solid #e5e7eb; }
        .label { width: 38%; font-weight: 700; color: #166534; }
        .value { color: #111827; }
        .highlight { background: #ecfdf5; color: #166534; font-weight: 700; }
        .signatures { width: 100%; margin-top: 12px; }
        .sign-table { width: 100%; border-collapse: collapse; }
        .sign-table td { width: 50%; padding: 8px; vertical-align: top; border: 1px solid #d1d5db; border-radius: 6px; }
        .sign-box { font-size: 9px; color: #475569; min-height: 38mm; }
        .sign-box strong { display: block; margin-bottom: 6px; color: #111827; }
        .footer { margin-top: 12px; font-size: 8px; color: #64748b; text-align: center; line-height: 1.4; }
    </style>
</head>
<body>
@php
    $fmt = fn($v) => number_format((float)$v, 2, ',', ' ');
    $fdate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
    $currency = $company->currency ?? 'FCFA';
    $companyAddress = trim(implode(', ', array_filter([
        $company->address ?? null,
        $company->city ?? null,
        $company->country ?? null,
    ])));
    $companyContact = trim(implode(' • ', array_filter([
        $company->phone ?? null,
        $company->email ?? null,
    ])));
@endphp

<div class="page">
    <table class="header-table" style="margin-bottom:8px;">
        <tr>
            <td style="width:50%;">
                @if($logo)
                    <img src="{{ $logo }}" alt="Logo" style="max-height:42px; width:auto; display:block; margin-bottom:6px;">
                @endif
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-info">
                    @if($companyAddress){{ $companyAddress }}<br>@endif
                    @if($companyContact){{ $companyContact }}@endif
                </div>
            </td>
            <td class="meta" style="width:50%;">
                <div class="ref">Réf. {{ $doc['number'] }}</div>
                <div>Date paiement : {{ $fdate($doc['date']) }}</div>
                <div>Émis le : {{ now()->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <div style="margin-bottom:10px;">
        <div class="title">REÇU DE PAIEMENT</div>
        <div class="subtitle">Document officiel de validation du règlement</div>
    </div>

    <div class="section">
        <div class="section-title">Informations de paiement</div>
        <table class="card">
            <tr>
                <td class="label">{{ $doc['source'] === 'client' ? 'Reçu de' : 'Versé à' }}</td>
                <td class="value">{{ $doc['party_name'] }}</td>
            </tr>
            <tr>
                <td class="label">Mode de paiement</td>
                <td class="value">{{ $doc['method'] }}</td>
            </tr>
            @if($doc['invoice_ref'])
            <tr>
                <td class="label">Facture liée</td>
                <td class="value">{{ $doc['invoice_ref'] }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Montant encaissé</td>
                <td class="value highlight">{{ $fmt($doc['amount']) }} {{ $currency }}</td>
            </tr>
            @if($doc['restant'] !== null)
            <tr>
                <td class="label">Solde restant</td>
                <td class="value">{{ $fmt(max(0, $doc['restant'])) }} {{ $currency }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="signatures">
        <table class="sign-table">
            <tr>
                <td>
                    <div class="sign-box">
                        <strong>Signature {{ $doc['source'] === 'client' ? 'client' : 'fournisseur' }}</strong>
                        Nom :<br><br>
                        Date :<br><br>
                        Signature :
                    </div>
                </td>
                <td>
                    <div class="sign-box">
                        <strong>Signature pour {{ $company->name }}</strong>
                        Nom :<br><br>
                        Date :<br><br>
                        Cachet / Signature :
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Ce reçu atteste du paiement indiqué et libère le débiteur à concurrence du montant versé.
        <br>Document généré par FIDUCIA ERP le {{ now()->format('d/m/Y à H:i') }}.
    </div>
</div>
</body>
</html>

