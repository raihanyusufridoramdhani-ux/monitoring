<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['pimpinan']);
$page_title='Dashboard Monitoring';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
 <div><h1>Dashboard Monitoring</h1><p>statistik response time, SLA, dan performa penanganan.</p></div>
 <div class="actions"><a class="btn btn-outline" href="/pcc-110/exports/export_csv.php">Export CSV</a><a class="btn btn-gold" href="/pcc-110/exports/export_pdf.php" target="_blank">Export PDF</a></div>
</div>

<div id="dashboard-stats" class="grid grid-4">
 <div class="card stat"><span class="stat-label">Laporan Hari Ini</span><span class="stat-value" id="total">—</span><span class="stat-note">real-time polling</span></div>
 <div class="card stat ok"><span class="stat-label">Selesai</span><span class="stat-value" id="selesai">—</span><span class="stat-note">status selesai</span></div>
 <div class="card stat warn"><span class="stat-label">Diproses</span><span class="stat-value" id="proses">—</span><span class="stat-note">status proses</span></div>
 <div class="card stat"><span class="stat-label">Avg Response</span><span class="stat-value" id="avg">—</span><span class="stat-note">W1 → W6</span></div>
</div>

<div class="grid grid-3" style="margin-top:16px">
 <div class="card"><div class="stat-label">Lama Perjalanan</div><div class="stat-value" id="travel">—</div><div class="stat-note">W5 → W6</div></div>
 <div class="card"><div class="stat-label">Lama Penyelesaian</div><div class="stat-value" id="handling">—</div><div class="stat-note">W6 → W7</div></div>
 <div class="card"><div class="stat-label">SLA Target</div><div class="stat-value">&lt; 05m</div><div class="stat-note">indikator sesuai proposal</div></div>
</div>

<div class="grid grid-2" style="margin-top:16px">
 <div class="card"><h2>RESPONSE TIME 7 HARI</h2><div class="chart-box"><canvas id="responseChart"></canvas></div></div>
 <div class="card"><h2>VOLUME LAPORAN</h2><div class="chart-box"><canvas id="volumeChart"></canvas></div></div>
</div>

<div class="grid grid-2" style="margin-top:16px">
 <div class="card"><h2>PERINGKAT REGU PAMAPTA</h2><div class="table-wrap"><table class="table"><thead><tr><th>Regu</th><th>Total</th><th>Selesai</th><th>Avg Response</th></tr></thead><tbody id="leader"></tbody></table></div></div>
 <div class="card"><h2>SEBARAN JENIS KEJADIAN</h2><div class="table-wrap"><table class="table"><thead><tr><th>Jenis</th><th>Jumlah</th></tr></thead><tbody id="types"></tbody></table></div></div>
</div>

<div class="card" style="margin-top:16px">
 <div class="page-head" style="margin-bottom:10px"><div><h2>SLA MONITORING</h2><p>Hijau &lt;5 menit · Kuning 5–10 menit · Merah &gt;10 menit.</p></div><span class="status-live"><span></span> AUTO REFRESH 15s</span></div>
 <div id="sla-summary" class="kpi-inline"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let responseChart,volumeChart;
const fmt=s=>{if(s==null)return '—';s=Math.round(Number(s));const m=Math.floor(s/60),sec=s%60;return `${String(m).padStart(2,'0')}m ${String(sec).padStart(2,'0')}s`};
const esc=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
async function load(){
 const r=await fetch('/pcc-110/api/dashboard.php',{cache:'no-store'}); const d=await r.json(); if(!d.ok)return;
 const t=d.today; document.getElementById('total').textContent=t.total??0; document.getElementById('selesai').textContent=t.selesai??0; document.getElementById('proses').textContent=t.proses??0;
 document.getElementById('avg').textContent=fmt(t.avg_response);document.getElementById('travel').textContent=fmt(t.avg_travel);document.getElementById('handling').textContent=fmt(t.avg_handling);
 document.getElementById('leader').innerHTML=d.leaderboard.length?d.leaderboard.map(x=>`<tr><td>${esc(x.regu)}</td><td>${x.total}</td><td>${x.selesai}</td><td>${fmt(x.avg_response)}</td></tr>`).join(''):`<tr><td colspan="4" class="empty">Belum ada data regu.</td></tr>`;
 document.getElementById('types').innerHTML=d.types.length?d.types.map(x=>`<tr><td>${esc(x.jenis)}</td><td>${x.total}</td></tr>`).join(''):`<tr><td colspan="2" class="empty">Belum ada data.</td></tr>`;
 const labels=d.weekly.map(x=>x.hari), responses=d.weekly.map(x=>x.response?Math.round(x.response/60):null), volumes=d.weekly.map(x=>x.total);
 if(responseChart)responseChart.destroy(); if(volumeChart)volumeChart.destroy();
 responseChart=new Chart(document.getElementById('responseChart'),{type:'line',data:{labels,datasets:[{label:'Menit',data:responses,borderWidth:2,tension:.25}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
 volumeChart=new Chart(document.getElementById('volumeChart'),{type:'bar',data:{labels,datasets:[{label:'Laporan',data:volumes,borderWidth:1}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
}
load(); setInterval(load,15000);
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
