<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Insights Pro — FIDUCIA ERP</title>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f1f5f9;color:#0f172a}
  .layout{display:flex;min-height:100vh}
  .sidebar{width:240px;background:#fff;border-right:1px solid #e2e8f0;position:fixed;top:0;left:0;bottom:0;display:flex;flex-direction:column;z-index:50}
  .brand{padding:20px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:10px}
  .brand img{height:38px;width:auto}
  .brand .name{font-size:15px;font-weight:800;color:#1a3a6a}
  .brand .name span{color:#b8860b}
  .brand .sub{font-size:10px;color:#64748b;letter-spacing:1px;text-transform:uppercase}
  .nav{flex:1;padding:14px 10px;overflow-y:auto}
  .nav-item{display:flex;align-items:center;gap:12px;padding:11px 14px;margin-bottom:2px;border-radius:10px;color:#475569;text-decoration:none;font-size:14px;font-weight:600;transition:all .15s}
  .nav-item .icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;background:#f1f5f9;flex-shrink:0}
  .nav-item:hover{background:#f8fafc;color:#1a3a6a}
  .nav-item.active{background:#eef4ff;color:#1a3a6a}
  .nav-sep{height:1px;background:#e2e8f0;margin:12px 8px}
  .sidebar-footer{padding:16px;border-top:1px solid #e2e8f0;font-size:11px;color:#64748b}
  .sidebar-footer .badge{display:inline-block;background:#eef4ff;color:#1a3a6a;padding:3px 10px;border-radius:20px;font-weight:700;margin-bottom:6px}
  main{flex:1;margin-left:240px;padding:28px 32px}
  .page-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:24px}
  .page-header h1{font-size:22px;font-weight:800}
  .page-header .sub{font-size:13px;color:#64748b;margin-top:3px}
  .header-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
  .pill{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #e2e8f0;border-radius:30px;padding:8px 16px;font-size:13px;font-weight:600;color:#334155}
  .pill .dot{width:8px;height:8px;border-radius:50%;background:#10b981;animation:pulse 1.5s infinite}
  @keyframes pulse{0%,100%{opacity:1}50%{opacity:.35}}
  .btn{padding:9px 18px;border-radius:9px;border:1px solid #e2e8f0;background:#fff;color:#334155;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .15s}
  .btn:hover{border-color:#cbd5e1;background:#f8fafc}
  .btn.active{background:#1a3a6a;border-color:#1a3a6a;color:#fff}
  .kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px}
  .kpi{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
  .kpi .icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:21px;flex-shrink:0}
  .kpi .num{font-size:24px;font-weight:800;line-height:1.1}
  .kpi .lbl{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.6px;font-weight:600;margin-top:3px}
  .kpi .trend{font-size:11px;margin-top:4px;font-weight:600}
  .trend.up{color:#059669}.trend.mut{color:#94a3b8}
  .ic-blue{background:#eef4ff}.ic-green{background:#ecfdf5}.ic-gold{background:#fffbeb}.ic-red{background:#fef2f2}.ic-purple{background:#f5f3ff}.ic-cyan{background:#ecfeff}
  section{margin-bottom:32px;scroll-margin-top:20px}
  .section-title{font-size:15px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:9px}
  .section-title .icon{width:30px;height:30px;border-radius:8px;background:#fff;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:14px}
  .chart-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
  .chart-box{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
  .chart-title{font-size:13px;font-weight:700;color:#334155;margin-bottom:14px}
  table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden}
  th,td{padding:12px 16px;text-align:left;font-size:13px;border-bottom:1px solid #f1f5f9;color:#334155}
  th{background:#f8fafc;color:#64748b;text-transform:uppercase;font-size:11px;letter-spacing:.6px;font-weight:700}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:#f8fafc}
  .rank{display:inline-flex;width:26px;height:26px;border-radius:8px;background:#eef4ff;color:#1a3a6a;font-weight:800;font-size:12px;align-items:center;justify-content:center}
  @media(max-width:1000px){.chart-grid{grid-template-columns:1fr}.sidebar{width:70px}.sidebar .label,.brand .texts,.sidebar-footer{display:none}.nav-item{justify-content:center;padding:11px 0}main{margin-left:70px;padding:20px}}
</style>
</head>
<body>
@php $s = $live['stats']; @endphp
<div class="layout">

  <aside class="sidebar">
    <div class="brand">
      <img src="/images/logo.png" alt="Logo" onerror="this.style.display='none'">
      <div class="texts">
        <div class="name">FIDUCIA <span>Insights</span></div>
        <div class="sub">Analytics Pro</div>
      </div>
    </div>
    <nav class="nav">
      <a href="#overview" class="nav-item active"><span class="icon">📊</span><span class="label">Vue d'ensemble</span></a>
      <a href="#activity" class="nav-item"><span class="icon">⚡</span><span class="label">Activité</span></a>
      <a href="#audience" class="nav-item"><span class="icon">📱</span><span class="label">Audience</span></a>
      <a href="#companies" class="nav-item"><span class="icon">🏢</span><span class="label">Entreprises</span></a>
      <a href="#top" class="nav-item"><span class="icon">🏆</span><span class="label">Top utilisateurs</span></a>
      <a href="#history" class="nav-item"><span class="icon">🕐</span><span class="label">Historique</span></a>
      <div class="nav-sep"></div>
      <a href="{{ route('telemetry.export', $token) }}?days={{ $days }}" class="nav-item"><span class="icon">📥</span><span class="label">Exporter JSON</span></a>
    </nav>
    <div class="sidebar-footer">
      <span class="badge">v{{ $live['version'] }}</span><br>
      {{ substr($live['install_id'], 0, 10) }}… · {{ $live['app_url'] }}
    </div>
  </aside>

  <main>
    <div class="page-header">
      <div>
        <h1>📊 Insights d'utilisation</h1>
        <div class="sub">Données confidentielles — réservées au propriétaire</div>
      </div>
      <div class="header-actions">
        <span class="pill"><span class="dot"></span><span id="realtime-count">0</span>&nbsp;actifs maintenant</span>
        @foreach([7, 30, 90, 365] as $d)
          <a href="?days={{ $d }}" class="btn {{ $days == $d ? 'active' : '' }}">{{ $d }} j</a>
        @endforeach
      </div>
    </div>

    <section id="overview">
      <div class="section-title"><span class="icon">📊</span> Vue d'ensemble</div>
      <div class="kpi-grid">
        <div class="kpi"><div class="icon ic-blue">👥</div><div><div class="num">{{ $s['users_total'] }}</div><div class="lbl">Utilisateurs</div><div class="trend up">↑ {{ $s['users_active_7d'] }} actifs / 7 j</div></div></div>
        <div class="kpi"><div class="icon ic-green">🏢</div><div><div class="num">{{ $s['companies_total'] }}</div><div class="lbl">Entreprises</div><div class="trend mut">Multi-tenant</div></div></div>
        <div class="kpi"><div class="icon ic-cyan">⚡</div><div><div class="num">{{ $s['sessions_today'] }}</div><div class="lbl">Sessions / jour</div><div class="trend mut">Temps réel</div></div></div>
        <div class="kpi"><div class="icon ic-purple">🎯</div><div><div class="num">{{ $s['events_today'] }}</div><div class="lbl">Événements / jour</div><div class="trend mut">Actions tracées</div></div></div>
        <div class="kpi"><div class="icon ic-gold">🧾</div><div><div class="num">{{ $s['invoices_total'] }}</div><div class="lbl">Factures</div><div class="trend mut">Ventes + Achats</div></div></div>
        <div class="kpi"><div class="icon ic-green">💰</div><div><div class="num">{{ $s['payslips_total'] }}</div><div class="lbl">Bulletins</div><div class="trend mut">Module Paie</div></div></div>
        <div class="kpi"><div class="icon ic-blue">🧑‍</div><div><div class="num">{{ $s['employees_total'] }}</div><div class="lbl">Employés</div><div class="trend mut">Module RH</div></div></div>
        <div class="kpi"><div class="icon ic-red">📒</div><div><div class="num">{{ $s['journal_entries'] }}</div><div class="lbl">Écritures</div><div class="trend mut">SYSCOHADA</div></div></div>
      </div>
    </section>

    <section id="activity">
      <div class="section-title"><span class="icon">⚡</span> Activité ({{ $days }} jours)</div>
      <div class="chart-grid">
        <div class="chart-box"><div class="chart-title">📈 Utilisateurs actifs quotidiens</div><div id="chart-daily"></div></div>
        <div class="chart-box"><div class="chart-title">🕐 Activité par heure</div><div id="chart-hourly"></div></div>
      </div>
    </section>

    <section id="audience">
      <div class="section-title"><span class="icon">📱</span> Audience</div>
      <div class="chart-grid">
        <div class="chart-box"><div class="chart-title">💻 Répartition par appareil</div><div id="chart-devices"></div></div>
        <div class="chart-box"><div class="chart-title">🌍 Top pays</div><div id="chart-countries"></div></div>
      </div>
    </section>

    <!-- ═══════════ ENTREPRISES : FILTRE + FICHE + BLOCAGE ═══════════ -->
    <section id="companies">
      <div class="section-title"><span class="icon">🏢</span> Entreprises & contrôle d'accès</div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px">
        <input id="co-search" type="text" placeholder="🔍 Rechercher (nom, email, contact, téléphone…)" style="padding:9px 14px;border:1px solid #e2e8f0;border-radius:9px;font-size:13px;min-width:280px;background:#fff;color:#0f172a">
        <select id="co-status" style="padding:9px 14px;border:1px solid #e2e8f0;border-radius:9px;font-size:13px;background:#fff;color:#334155">
          <option value="all">Tous les statuts</option>
          <option value="active">✅ Actives</option>
          <option value="blocked">🚫 Bloquées</option>
        </select>
        <span id="co-count" style="font-size:12px;color:#64748b;font-weight:700"></span>
      </div>
      <div style="overflow-x:auto">
      <table>
        <thead><tr>
          <th>Entreprise</th><th>Contact</th><th>Email / Téléphone</th>
          <th>Utilis.</th><th>Employés</th><th>Factures</th><th>Statut</th><th>Actions</th>
        </tr></thead>
        <tbody>
        @foreach($companies as $c)
          <tr class="co-row" data-status="{{ $c['is_blocked'] ? 'blocked' : 'active' }}" data-search="{{ strtolower($c['name'] . ' ' . ($c['email'] ?? '') . ' ' . ($c['phone'] ?? '') . ' ' . ($c['contact_name'] ?? '')) }}">
            <td>
              <div style="font-weight:800">{{ $c['name'] }}</div>
              <div style="font-size:11px;color:#94a3b8">{{ $c['tax_number'] ?? '—' }}</div>
            </td>
            <td>
              <div style="font-weight:600">{{ $c['contact_name'] ?? (isset($c['users'][0]) ? $c['users'][0]->name : '—') }}</div>
              @if(count($c['users']) > 0)
              <details style="margin-top:4px">
                <summary style="cursor:pointer;font-size:11px;color:#1a3a6a;font-weight:700">👥 {{ count($c['users']) }} utilisateur(s)</summary>
                <ul style="margin:6px 0 0 16px;font-size:12px;color:#475569">
                  @foreach($c['users'] as $u)
                    <li><strong>{{ $u->name }}</strong> — {{ $u->email }}</li>
                  @endforeach
                </ul>
              </details>
              @endif
            </td>
            <td>
              <div>{{ $c['email'] ?? '—' }}</div>
              <div style="font-size:11px;color:#64748b">{{ $c['phone'] ?? '' }}</div>
            </td>
            <td style="text-align:center;font-weight:700">{{ count($c['users']) }}</td>
            <td>{{ $c['employees'] }}</td>
            <td>{{ $c['invoices'] }}</td>
            <td>
              @if($c['is_blocked'])
                <span style="background:#fef2f2;color:#dc2626;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:800">🚫 BLOQUÉE</span>
              @else
                <span style="background:#ecfdf5;color:#059669;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:800">✅ ACTIVE</span>
              @endif
            </td>
            <td style="white-space:nowrap">
              <button type="button" class="btn co-fiche" data-id="{{ $c['id'] }}" style="margin-right:6px" title="Fiche complète">📋 Fiche</button>
              @if($c['is_blocked'])
                <form method="POST" action="{{ route('telemetry.unblock', [$token, $c['id']]) }}" style="display:inline">
                  @csrf
                  <button class="btn" type="submit">🔓 Débloquer</button>
                </form>
              @else
                <form method="POST" action="{{ route('telemetry.block', [$token, $c['id']]) }}" style="display:inline" onsubmit="return confirm('Bloquer l\'accès de « {{ $c['name'] }} » au système ?')">
                  @csrf
                  <button class="btn" style="color:#dc2626;border-color:#fecaca" type="submit">🚫 Bloquer</button>
                </form>
              @endif
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
      </div>
    </section>

    <section id="top">
      <div class="section-title"><span class="icon">🏆</span> Top 10 utilisateurs actifs</div>
      <table>
        <thead><tr><th>#</th><th>Nom</th><th>Email</th><th>Événements</th></tr></thead>
        <tbody>
          @forelse($topUsers as $i => $user)
          <tr>
            <td><span class="rank">{{ $i + 1 }}</span></td>
            <td style="font-weight:700">{{ $user->name }}</td>
            <td style="color:#64748b">{{ $user->email }}</td>
            <td style="font-weight:800;color:#1a3a6a">{{ number_format($user->event_count) }}</td>
          </tr>
          @empty
          <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:28px">Aucune donnée d'activité pour l'instant</td></tr>
          @endforelse
        </tbody>
      </table>
    </section>

    <section id="history">
      <div class="section-title"><span class="icon">🕐</span> Historique des snapshots</div>
      <table>
        <thead><tr><th>Date</th><th>Utilisateurs</th><th>Entreprises</th><th>Factures</th><th>Bulletins</th><th>Sessions</th></tr></thead>
        <tbody>
          @forelse($snapshots as $snap)
          @php $p = $snap['payload']['stats'] ?? []; @endphp
          <tr>
            <td style="font-weight:600">{{ $snap->recorded_at->format('d/m/Y H:i') }}</td>
            <td>{{ $p['users_total'] ?? '—' }}</td>
            <td>{{ $p['companies_total'] ?? '—' }}</td>
            <td>{{ $p['invoices_total'] ?? '—' }}</td>
            <td>{{ $p['payslips_total'] ?? '—' }}</td>
            <td>{{ $p['sessions_today'] ?? '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:28px">Aucun snapshot enregistré</td></tr>
          @endforelse
        </tbody>
      </table>
    </section>
  </main>
</div>

<!-- ═══════════ MODAL FICHE ENTREPRISE ═══════════ -->
<div id="co-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
  <div style="background:#fff;border-radius:16px;max-width:660px;width:100%;max-height:88vh;overflow-y:auto;padding:26px;box-shadow:0 20px 60px rgba(15,23,42,.25)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
      <div style="font-size:16px;font-weight:800">🏢 Fiche entreprise</div>
      <button type="button" onclick="document.getElementById('co-modal').style.display='none'" style="border:none;background:#f1f5f9;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:14px">✕</button>
    </div>
    <div id="co-modal-body"></div>
  </div>
</div>

<script>
const COMPANIES = @json($companies);
const LIGHT = { foreColor:'#64748b', grid:'#e2e8f0' };
const COLORS = ['#1a3a6a','#b8860b','#10b981','#ef4444','#38bdf8','#8b5cf6'];

// ── Filtrage des entreprises ──
const coSearch = document.getElementById('co-search');
const coStatus = document.getElementById('co-status');
const coCount  = document.getElementById('co-count');
function applyCoFilter() {
  const q = (coSearch ? coSearch.value : '').toLowerCase();
  const st = coStatus ? coStatus.value : 'all';
  let visible = 0;
  document.querySelectorAll('.co-row').forEach(row => {
    const okQ = !q || (row.dataset.search || '').includes(q);
    const okS = st === 'all' || row.dataset.status === st;
    const show = okQ && okS;
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  if (coCount) coCount.textContent = visible + ' entreprise(s) affichée(s)';
}
if (coSearch) coSearch.addEventListener('input', applyCoFilter);
if (coStatus) coStatus.addEventListener('change', applyCoFilter);
applyCoFilter();

// ── Fiche complète en popup ──
document.querySelectorAll('.co-fiche').forEach(btn => {
  btn.addEventListener('click', () => {
    const co = COMPANIES.find(x => x.id == btn.dataset.id);
    if (!co) return;
    const s = co.stats || {};
    const row = (l, v) => '<div style="display:flex;justify-content:space-between;border-bottom:1px dotted #e2e8f0;padding:6px 0;font-size:13px"><span style="font-weight:600;color:#64748b">' + l + '</span><span style="color:#0f172a;text-align:right">' + (v || '—') + '</span></div>';
    const stat = (i, n, l) => '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px;text-align:center"><div style="font-size:16px">' + i + '</div><div style="font-weight:800">' + n + '</div><div style="font-size:10px;color:#64748b;text-transform:uppercase">' + l + '</div></div>';
    const usersHtml = (co.users || []).map(u => '<li style="display:flex;justify-content:space-between;background:#f8fafc;border-radius:8px;padding:6px 10px;font-size:12px"><strong>' + u.name + '</strong><span style="color:#64748b">' + u.email + '</span></li>').join('');
    document.getElementById('co-modal-body').innerHTML =
      '<div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">' +
      '<div style="width:52px;height:52px;border-radius:12px;background:#1a3a6a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px">' + co.name.substring(0,2).toUpperCase() + '</div>' +
      '<div><div style="font-weight:800">' + co.name + '</div>' +
      (co.is_blocked ? '<span style="font-size:11px;font-weight:800;color:#dc2626">🚫 Accès bloqué</span>' : '<span style="font-size:11px;font-weight:800;color:#059669">✅ Accès actif</span>') + '</div></div>' +
      row('N° fiscal', co.tax_number) + row('Adresse', co.address) + row('Téléphone', co.phone) + row('Email', co.email) +
      row('Devise', co.currency) + row('Début exercice', 'Mois ' + co.fiscal_year_start_month) + row('Créée le', co.created_at) +
      '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:14px 0">' +
      stat('👥', s.users || 0, 'Utilisateurs') + stat('🧑‍💼', s.employees || 0, 'Employés') + stat('🤝', s.clients || 0, 'Clients') + stat('🏭', s.suppliers || 0, 'Fournisseurs') +
      stat('🧾', s.invoices || 0, 'Factures') + stat('💰', s.payslips || 0, 'Bulletins') + stat('📒', s.journal_entries || 0, 'Écritures') + '</div>' +
      '<div style="font-weight:700;font-size:13px;color:#334155;margin-bottom:8px">Utilisateurs de cette entreprise</div>' +
      '<ul style="list-style:none;display:flex;flex-direction:column;gap:6px;max-height:160px;overflow-y:auto">' + (usersHtml || '<li style="font-size:12px;color:#94a3b8">Aucun utilisateur</li>') + '</ul>';
    document.getElementById('co-modal').style.display = 'flex';
  });
});
document.getElementById('co-modal').addEventListener('click', e => { if (e.target.id === 'co-modal') e.target.style.display = 'none'; });

// ── Graphiques ──
const dailyData = @json($dailyActive);
if (dailyData.length > 0) new ApexCharts(document.querySelector("#chart-daily"), { series:[{name:'Utilisateurs',data:dailyData.map(d=>({x:d.date,y:d.users}))}], chart:{type:'area',height:280,foreColor:LIGHT.foreColor,toolbar:{show:false}}, stroke:{curve:'smooth',width:3}, colors:['#1a3a6a'], fill:{type:'gradient',gradient:{opacityFrom:.25,opacityTo:0}}, xaxis:{type:'datetime'}, grid:{borderColor:LIGHT.grid} }).render();
const hourlyData = @json($activityByHour);
if (Object.keys(hourlyData).length > 0) new ApexCharts(document.querySelector("#chart-hourly"), { series:[{name:'Événements',data:Array.from({length:24},(_,i)=>({x:i+'h',y:hourlyData[i]||0}))}], chart:{type:'bar',height:280,foreColor:LIGHT.foreColor,toolbar:{show:false}}, colors:['#b8860b'], plotOptions:{bar:{columnWidth:'65%',borderRadius:4}}, grid:{borderColor:LIGHT.grid} }).render();
const deviceData = @json($deviceBreakdown);
if (Object.keys(deviceData).length > 0) new ApexCharts(document.querySelector("#chart-devices"), { series:Object.values(deviceData), chart:{type:'donut',height:280,foreColor:LIGHT.foreColor}, labels:Object.keys(deviceData), colors:COLORS, legend:{position:'bottom'}, stroke:{colors:['#fff'],width:2} }).render();
const countryData = @json($countryBreakdown);
if (Object.keys(countryData).length > 0) new ApexCharts(document.querySelector("#chart-countries"), { series:[{name:'Sessions',data:Object.values(countryData)}], chart:{type:'bar',height:280,foreColor:LIGHT.foreColor,toolbar:{show:false}}, plotOptions:{bar:{horizontal:true,barHeight:'55%',borderRadius:4}}, xaxis:{categories:Object.keys(countryData)}, colors:['#10b981'], grid:{borderColor:LIGHT.grid} }).render();

// ── Temps réel ──
async function refreshRealtime(){ try{ const r=await fetch('{{ route("telemetry.realtime", $token) }}'); const d=await r.json(); document.getElementById('realtime-count').textContent=d.active_now; }catch(e){} }
refreshRealtime(); setInterval(refreshRealtime, 5000);

// ── Nav active ──
document.querySelectorAll('.nav-item[href^="#"]').forEach(l => l.addEventListener('click', () => { document.querySelectorAll('.nav-item').forEach(x=>x.classList.remove('active')); l.classList.add('active'); }));
</script>
</body>
</html>
