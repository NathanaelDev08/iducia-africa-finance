<!DOCTYPE html>
<html>
<body style="font-family:Arial,sans-serif;background:#f3f4f6;padding:20px;">
<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
<div style="background:#2E5090;color:#fff;padding:25px;text-align:center;"><h1 style="margin:0;">Bienvenue sur FIDUCIA ERP</h1></div>
<div style="padding:25px;color:#333;">
<p>Bonjour <strong>{{ $user->name }}</strong>,</p>
<p><strong>{{ $invitedBy }}</strong> vous a cree un compte.</p>
<div style="background:#f8fafc;border-left:4px solid #2E5090;padding:15px;margin:20px 0;">
<p style="margin:5px 0;">Email : <code>{{ $user->email }}</code></p>
<p style="margin:5px 0;">Mot de passe temporaire : <code style="font-weight:bold;">{{ $tempPassword }}</code></p>
</div>
<p style="text-align:center;"><a href="{{ config('app.url') }}/login" style="display:inline-block;background:#2E5090;color:#fff;padding:12px 30px;text-decoration:none;border-radius:6px;">Acceder a mon compte</a></p>
<p>Vos modules autorises :</p>
<ul>@foreach($user->modules as $m)<li>{{ $m->icon }} {{ $m->name }}</li>@endforeach</ul>
</div>
</div>
</body>
</html>
