<!DOCTYPE html>
<html>
<body style="font-family:Arial,sans-serif;background:#f3f4f6;padding:20px;">
<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
<div style="background:#2E5090;color:#fff;padding:25px;text-align:center;"><h1 style="margin:0;">FIDUCIA ERP</h1></div>
<div style="padding:25px;color:#333;">
@if($forHr)
<p>Bonjour,</p>
<p>Le solde de congés acquis de <strong>{{ $employee->full_name }}</strong> (matricule {{ $employee->matricule }}) a atteint <strong>{{ number_format($balance, 1, ',', ' ') }} jours</strong>.</p>
<p>Nous vous invitons à planifier la prise de congés de cet employé.</p>
@else
<p>Bonjour <strong>{{ $employee->first_name }}</strong>,</p>
<p>Votre solde de congés acquis a atteint <strong>{{ number_format($balance, 1, ',', ' ') }} jours</strong>.</p>
<p>Nous vous invitons à planifier la prise de vos congés auprès de votre responsable RH.</p>
@endif
<div style="background:#f8fafc;border-left:4px solid #2E5090;padding:15px;margin:20px 0;">
<p style="margin:5px 0;">Employé : <code>{{ $employee->full_name }}</code></p>
<p style="margin:5px 0;">Matricule : <code>{{ $employee->matricule }}</code></p>
<p style="margin:5px 0;">Solde acquis : <code style="font-weight:bold;">{{ number_format($balance, 1, ',', ' ') }} jours</code></p>
</div>
<p style="text-align:center;"><a href="{{ config('app.url') }}/hr?tab=conges" style="display:inline-block;background:#2E5090;color:#fff;padding:12px 30px;text-decoration:none;border-radius:6px;">Voir les congés</a></p>
</div>
</div>
</body>
</html>
