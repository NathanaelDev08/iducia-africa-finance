<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Reçu {{ $doc['number'] }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    @page { size: A4; margin: 12mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
    table { border-collapse: collapse; width: 100%; }
</style></head>
<body>
@php
    $fmt = fn($v) => number_format((float)$v, 2, ',', ' ');
    $fdate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
    $currency = $company->currency ?? 'FCFA';
@endphp

<!-- EN-TÊTE -->
<table style="margin-bottom:10mm;">
    <tr>
        <td style="width:30%; vertical-align:top; border:none;">
            @if($logo)<img src="{{ $logo }}" style="height:45px; width:auto;" />@endif
        </td>
        <td style="width:40%; text-align:center; vertical-align:top; border:none;">
            <div style="font-size:16px; font-weight:bold; letter-spacing:2px; color:#2d6a4f;">REÇU DE PAIEMENT</div>
            <div style="font-size:10px; margin-top:2px;">N° <strong>{{ $doc['number'] }}</strong></div>
        </td>
        <td style="width:30%; text-align:right; vertical-align:top; border:none;">
            @if($companyLogo)<img src="{{ $companyLogo }}" style="height:38px; width:auto;" /><br>@endif
            <div style="font-size:9px; font-weight:bold; color:#1a3a6a;">{{ $company->name }}</div>
        </td>
    </tr>
</table>

<!-- CORPS DU REÇU -->
<table style="border:2px solid #2d6a4f; border-radius:4px;">
    <tr>
        <td style="padding:4mm; border-bottom:1px solid #ccc; width:40%; font-weight:bold; color:#2d6a4f;">Date du paiement</td>
        <td style="padding:4mm;">{{ $fdate($doc['date']) }}</td>
    </tr>
    <tr>
        <td style="padding:4mm; border-bottom:1px solid #ccc; font-weight:bold; color:#2d6a4f;">{{ $doc['source'] === 'client' ? 'Reçu de' : 'Versé à' }}</td>
        <td style="padding:4mm; font-weight:bold;">{{ $doc['party_name'] }}</td>
    </tr>
    @if($doc['invoice_ref'])
    <tr>
        <td style="padding:4mm; border-bottom:1px solid #ccc; font-weight:bold; color:#2d6a4f;">Au titre de la facture</td>
        <td style="padding:4mm;">{{ $doc['invoice_ref'] }}</td>
    </tr>
    @endif
    <tr>
        <td style="padding:4mm; border-bottom:1px solid #ccc; font-weight:bold; color:#2d6a4f;">Mode de paiement</td>
        <td style="padding:4mm;">{{ $doc['method'] }}</td>
    </tr>
    <tr>
        <td style="padding:5mm; background:#eaf6ee; font-weight:bold; color:#2d6a4f; font-size:11px;">MONTANT ENCAISSÉ</td>
        <td style="padding:5mm; background:#eaf6ee; font-weight:bold; font-size:13px; color:#2d6a4f;">{{ $fmt($doc['amount']) }} {{ $currency }}</td>
    </tr>
    @if($doc['restant'] !== null)
    <tr>
        <td style="padding:4mm; font-weight:bold; color:#b8860b;">Restant dû sur la facture</td>
        <td style="padding:4mm; font-weight:bold;">{{ $fmt(max(0, $doc['restant'])) }} {{ $currency }}</td>
    </tr>
    @endif
</table>

<!-- SIGNATURES -->
<table style="margin-top:15mm;">
    <tr>
        <td style="width:48%; border:1px solid #999; height:30mm; vertical-align:top; padding:3mm; font-size:9px;">
            <strong>Signature du {{ $doc['source'] === 'client' ? 'client' : 'fournisseur' }}</strong>
        </td>
        <td style="width:4%; border:none;"></td>
        <td style="width:48%; border:1px solid #999; height:30mm; vertical-align:top; padding:3mm; font-size:9px;">
            <strong>Pour {{ $company->name }} — Signature et cachet</strong>
        </td>
    </tr>
</table>

<div style="margin-top:10mm; font-size:7.5px; color:#666; text-align:center;">
    Ce reçu libère le débiteur à concurrence du montant indiqué. — Document généré par FIDUCIA ERP le {{ now()->format('d/m/Y à H:i') }}
</div>
</body></html>
