<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['operator']);
require_once __DIR__ . '/../includes/whatsapp.php';

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$date = $_GET['date'] ?? '';

$sql = "SELECT l.* FROM laporan l WHERE 1=1";
$params = [];
if ($q !== '') {
    $sql .= " AND (l.tiket LIKE ? OR l.nomor_laporan LIKE ? OR l.lokasi LIKE ? OR l.jenis_kejadian LIKE ?)";
    $like = "%$q%"; array_push($params,$like,$like,$like,$like);
}
if (in_array($status,['baru','proses','selesai'],true)) { $sql .= " AND l.status=?"; $params[]=$status; }
if ($date !== '') { $sql .= " AND DATE(l.created_at)=?"; $params[]=$date; }
$sql .= " ORDER BY l.created_at DESC LIMIT 100";
$stmt = db()->prepare($sql); $stmt->execute($params); $reports = $stmt->fetchAll();

$stats = db()->query("SELECT
 SUM(status='baru') baru, SUM(status='proses') proses, SUM(status='selesai') selesai,
 AVG(CASE WHEN waktu_laporan IS NOT NULL AND waktu_tiba IS NOT NULL THEN TIMESTAMPDIFF(SECOND,waktu_laporan,waktu_tiba) END) avg_response
 FROM laporan")->fetch();
$page_title = 'Antrean Laporan Layanan 110';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
  <div><h1>Antrean Laporan</h1><p>Input, distribusi, dan monitoring laporan 110.</p></div>
  <a class="btn btn-gold" href="/pcc-110/operator/laporan-baru.php">+ Input Laporan Baru</a>
</div>

<div class="grid grid-4">
  <div class="card stat alert"><span class="stat-label">Laporan Baru</span><span class="stat-value"><?= (int)$stats['baru'] ?></span><span class="stat-note">Menunggu distribusi</span></div>
  <div class="card stat warn"><span class="stat-label">Sedang Diproses</span><span class="stat-value"><?= (int)$stats['proses'] ?></span><span class="stat-note">Dalam penanganan</span></div>
  <div class="card stat ok"><span class="stat-label">Selesai</span><span class="stat-value"><?= (int)$stats['selesai'] ?></span><span class="stat-note">Tertutup</span></div>
  <div class="card stat"><span class="stat-label">Avg Response</span><span class="stat-value"><?= $stats['avg_response']!==null ? e(duration_human((int)$stats['avg_response'])) : '—' ?></span><span class="stat-note">W1 → W6</span></div>
</div>

<div class="card" style="margin-top:16px">
  <form class="filters" method="get">
    <input name="q" placeholder="Cari tiket, nomor, lokasi..." value="<?= e($q) ?>">
    <select name="status"><option value="">Semua status</option><?php foreach(['baru','proses','selesai'] as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= strtoupper($s) ?></option><?php endforeach; ?></select>
    <input type="date" name="date" value="<?= e($date) ?>">
    <button class="btn btn-outline">Filter</button>
    <a class="btn btn-outline" href="/pcc-110/operator/dashboard.php">Reset</a>
  </form>
</div>

<div class="card" style="margin-top:16px">
  <div class="page-head" style="margin-bottom:12px"><div><h2>Riwayat Laporan</h2><p class="small muted"><?= count($reports) ?> data ditampilkan</p></div></div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Tiket</th><th>Jenis</th><th>Lokasi</th><th>Prioritas</th><th>Status</th><th>Response</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if (!$reports): ?><tr><td colspan="7" class="empty">Belum ada laporan.</td></tr><?php endif; ?>
      <?php foreach ($reports as $r): $rs=response_seconds($r); ?>
        <tr>
          <td class="mono"><?= e($r['tiket']) ?><br><span class="muted"><?= e($r['nomor_laporan']) ?></span></td>
          <td><?= e($r['jenis_kejadian']) ?></td>
          <td><?= e($r['lokasi']) ?></td>
          <td class="priority-<?= $r['prioritas']==='tinggi'?'high':($r['prioritas']==='sedang'?'mid':'low') ?>"><?= strtoupper(e($r['prioritas'])) ?></td>
          <td><span class="badge <?= $r['status']==='selesai'?'ok':($r['status']==='proses'?'warn':'alert') ?>"><?= strtoupper(e($r['status'])) ?></span></td>
          <td><span class="badge <?= sla_class($rs) ?>"><?= duration_human($rs) ?></span></td>
          <td>
  <div class="actions">
    <?php if(!$r['waktu_kirim']): ?>
    <form method="post" action="/pcc-110/operator/kirim-wa.php" target="_blank">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
      <button class="btn btn-gold">Kirim WA</button>
    </form>
    <?php else: ?><span class="badge ok">WA TERKIRIM</span><?php endif; ?>
    <a class="btn btn-outline" href="/pcc-110/pamapta/detail.php?token=<?= e($r['token_akses']) ?>" target="_blank">Buka</a>
  </div>
</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
