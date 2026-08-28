<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['operator']);
require_once __DIR__ . '/../includes/whatsapp.php';

$errors=[]; $created=null;
$templates=[
 'Kecelakaan Lalu Lintas'=>'Laporan kecelakaan lalu lintas. Jelaskan kendaraan, korban, dan kondisi arus.',
 'Pencurian'=>'Laporan dugaan pencurian. Jelaskan objek, lokasi, waktu kejadian, dan informasi awal.',
 'Perkelahian'=>'Laporan perkelahian/keributan. Jelaskan pihak terlibat dan situasi terkini.',
 'Kebakaran'=>'Laporan kebakaran. Jelaskan objek yang terbakar, kondisi api, dan risiko sekitar.',
 'Gangguan Ketertiban'=>'Laporan gangguan ketertiban. Jelaskan situasi dan kebutuhan penanganan.'
];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $lokasi=trim($_POST['lokasi']??''); $jenis=trim($_POST['jenis_kejadian']??''); $prioritas=$_POST['prioritas']??'';
  $deskripsi=trim($_POST['deskripsi']??''); $waktu_laporan=$_POST['waktu_laporan']??'';
  if (!$lokasi || !$jenis || !in_array($prioritas,['tinggi','sedang','rendah'],true)) $errors[]='Lokasi, jenis kejadian, dan prioritas wajib diisi.';
  $ts=strtotime($waktu_laporan);
  if (!$ts) $errors[]='Waktu laporan tidak valid.';
  if (!$errors) {
    $ids=generate_ticket();
    $dt=date('Y-m-d H:i:s',$ts);
    $stmt=db()->prepare("INSERT INTO laporan (tiket,nomor_laporan,token_akses,lokasi,jenis_kejadian,prioritas,deskripsi,waktu_laporan,waktu_input,status) VALUES (?,?,?,?,?,?,?,?,NOW(),'baru')");
    $stmt->execute([$ids['tiket'],$ids['nomor_laporan'],$ids['token'],$lokasi,$jenis,$prioritas,$deskripsi,$dt]);
    audit_log('Insert', 'Membuat laporan ' . $ids['tiket']);
    $created=db()->prepare("SELECT * FROM laporan WHERE id=?"); $created->execute([db()->lastInsertId()]); $created=$created->fetch();
    flash('success','Laporan berhasil dibuat: '.$created['tiket']);
  }
}
$page_title='Input Laporan 110';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><div><h1>Input Laporan</h1><p>Masukan data laporan 110.</p></div></div>
<?php foreach($errors as $er): ?><div class="flash error"><?= e($er) ?></div><?php endforeach; ?>

<?php if($created): ?>
<div class="card" style="border-left:4px solid var(--gold);margin-bottom:16px">
  <div class="eyebrow2">LAPORAN BERHASIL DIBUAT</div>
  <h2 style="font-family:'Barlow Condensed';font-size:26px;margin:4px 0"><?= e($created['tiket']) ?></h2>
  <p class="muted">Nomor laporan: <span class="mono"><?= e($created['nomor_laporan']) ?></span></p>
  <div class="actions" style="margin-top:12px">
    <a class="btn btn-gold" href="<?= e(whatsapp_url($created)) ?>" target="_blank" rel="noopener">Kirim WhatsApp</a>
    <button class="btn btn-outline" data-copy="<?= e(whatsapp_message($created)) ?>">Salin Pesan</button>
    <a class="btn btn-outline" href="/pcc-110/pamapta/detail.php?token=<?= e($created['token_akses']) ?>" target="_blank">Tes Link Pamapta</a>
  </div>
  <p class="small muted" style="margin-top:10px">W3 hanya tercatat saat tombol Kirim WhatsApp diklik melalui endpoint aksi.</p>
</div>
<?php endif; ?>

<div class="grid-main">
  <div class="card">
    <h2>FORM LAPORAN BARU</h2>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field"><label>Template Cepat</label><select data-template data-target="#deskripsi"><option value="">Pilih template...</option><?php foreach($templates as $name=>$desc): ?><option data-description="<?= e($desc) ?>"><?= e($name) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Lokasi Kejadian</label><input name="lokasi" list="lokasi-list" placeholder="Masukkan alamat/lokasi" required><datalist id="lokasi-list"><option value="Jl. Pahlawan"><option value="Simpang Lima"><option value="Jl. Pandanaran"><option value="Jl. MT Haryono"></datalist></div>
      <div class="field"><label>Jenis Kejadian</label><input name="jenis_kejadian" list="jenis-list" required><datalist id="jenis-list"><?php foreach($templates as $name=>$desc): ?><option value="<?= e($name) ?>"><?php endforeach; ?></datalist></div>
      <div class="field"><label>Prioritas</label><select name="prioritas" required><option value="">Pilih prioritas</option><option value="tinggi">Tinggi</option><option value="sedang">Sedang</option><option value="rendah">Rendah</option></select></div>
      <div class="field"><label>Waktu Laporan Diterima (W1)</label><input type="datetime-local" name="waktu_laporan" value="<?= date('Y-m-d\TH:i') ?>" required></div>
      <div class="field"><label>Deskripsi Awal</label><textarea id="deskripsi" name="deskripsi" placeholder="Keterangan singkat dari laporan 110"></textarea></div>
      <button class="btn btn-primary btn-block">Generate Tiket & Simpan</button>
    </form>
  </div>
  <div class="card">
    <h2>PARAMETER WAKTU</h2>
    <div class="timeline">
      <?php foreach([
        'W1'=>'Telepon 110 diterima',
        'W2'=>'Operator selesai input',
        'W3'=>'Link dikirim via WhatsApp',
        'W4'=>'Link pertama kali dibuka',
        'W5'=>'Pamapta berangkat',
        'W6'=>'Pamapta tiba di TKP',
        'W7'=>'Penanganan selesai'
      ] as $k=>$v): ?>
      <div class="timeline-row"><div class="timeline-key"><?= $k ?></div><div><?= e($v) ?></div></div>
      <?php endforeach; ?>
    </div>
    <div class="flash info" style="margin-top:15px">Semua timestamp proses dicatat server-side untuk mengurangi human error.</div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
