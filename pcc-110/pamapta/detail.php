<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$token=trim($_GET['token']??'');
if(!$token){ http_response_code(400); exit('Token laporan tidak tersedia.'); }
$stmt=db()->prepare("SELECT l.*, r.nama_regu FROM laporan l LEFT JOIN regu r ON r.id=l.regu_id WHERE l.token_akses=? LIMIT 1");
$stmt->execute([$token]); $report=$stmt->fetch();
if(!$report){ http_response_code(404); exit('Laporan tidak ditemukan atau token tidak valid.'); }

if(!$report['waktu_buka']) {
  db()->prepare("UPDATE laporan SET waktu_buka=NOW() WHERE id=? AND waktu_buka IS NULL")->execute([$report['id']]);
  $report['waktu_buka']=date('Y-m-d H:i:s');
  audit_log('Open Token','W4 '.$report['tiket']);
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf();
  $action=$_POST['action']??'';
  try {
    if($action==='berangkat' && !$report['waktu_berangkat']){
      db()->prepare("UPDATE laporan SET waktu_berangkat=NOW(),status='proses' WHERE id=?")->execute([$report['id']]);
      audit_log('Berangkat',$report['tiket']);
      flash('success','Waktu berangkat berhasil dicatat.');
    } elseif($action==='tiba' && !$report['waktu_tiba']){
      db()->prepare("UPDATE laporan SET waktu_tiba=NOW(),status='proses' WHERE id=?")->execute([$report['id']]);
      audit_log('Tiba TKP',$report['tiket']);
      flash('success','Waktu tiba di TKP berhasil dicatat.');
    } elseif($action==='selesai'){
      $hasil=trim($_POST['hasil']??'');
      if(!$hasil) throw new RuntimeException('Hasil penanganan wajib diisi.');
      $files=$_FILES['foto']??null;
      if(!$files || !isset($files['name']) || !is_array($files['name'])) throw new RuntimeException('Foto dokumentasi wajib dilampirkan.');
      $valid=[];
      foreach($files['name'] as $i=>$name){
        if($name==='' || ($files['error'][$i]??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) continue;
        $valid[]=$i;
      }
      if(count($valid)<1 || count($valid)>MAX_UPLOADS) throw new RuntimeException('Foto harus 1 sampai 5 file.');
      $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
      $saved=[];
      foreach($valid as $i){
        if(($files['size'][$i]??0)>MAX_FILE_SIZE) throw new RuntimeException('Ukuran foto maksimal 5 MB per file.');
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($files['tmp_name'][$i]);
        if(!isset($allowed[$mime])) throw new RuntimeException('Tipe file tidak diperbolehkan.');
        $name=bin2hex(random_bytes(12)).'.'.$allowed[$mime];
        $dest=UPLOAD_DIR.'/'.$name;
        if(!move_uploaded_file($files['tmp_name'][$i],$dest)) throw new RuntimeException('Upload foto gagal.');
        $saved[]='/pcc-110/storage/uploads/'.$name;
      }

      $signature=null;
      $sig=trim($_POST['signature']??'');
      if($sig && str_starts_with($sig,'data:image/png;base64,')){
        $raw=base64_decode(substr($sig,22),true);
        if($raw!==false && strlen($raw)<2*1024*1024){
          $sigName=bin2hex(random_bytes(12)).'.png';
          file_put_contents(UPLOAD_DIR.'/'.$sigName,$raw);
          $signature='/pcc-110/storage/uploads/'.$sigName;
        }
      }
      $pdo=db(); $pdo->beginTransaction();
      $pdo->prepare("UPDATE laporan SET hasil=?,tanda_tangan=?,waktu_selesai=NOW(),status='selesai' WHERE id=?")->execute([$hasil,$signature,$report['id']]);
      $ins=$pdo->prepare("INSERT INTO foto(laporan_id,file_path) VALUES(?,?)");
      foreach($saved as $path) $ins->execute([$report['id'],$path]);
      $pdo->commit();
      audit_log('Selesai',$report['tiket']);
      flash('success','Laporan berhasil diselesaikan.');
    }
  } catch(Throwable $e) {
    if(isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    flash('error',$e->getMessage());
  }
  redirect('/pcc-110/pamapta/detail.php?token='.rawurlencode($token));
}
$stmt->execute([$token]); $report=$stmt->fetch();
$photos=db()->prepare("SELECT * FROM foto WHERE laporan_id=? ORDER BY id"); $photos->execute([$report['id']]); $photos=$photos->fetchAll();
$next=$report['waktu_berangkat'] ? ($report['waktu_tiba'] ? ($report['waktu_selesai'] ? 'done':'selesai') : 'tiba') : 'berangkat';
$page_title='Detail Laporan — Pamapta';
require __DIR__ . '/../includes/header.php';
?>
<div class="mobile-card">
  <div class="mobile-head">
    <div class="eyebrow" style="color:#aab6c2">PAMAPTA / TOKEN ACCESS</div>
    <div style="font-family:'Barlow Condensed';font-size:29px;font-weight:800"><?= e($report['tiket']) ?></div>
    <div class="small" style="color:#c8d0d8"><?= e($report['nomor_laporan']) ?></div>
  </div>
  <div class="mobile-body">
    <?php foreach(flashes() as $f): ?><div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endforeach; ?>
    <div class="grid grid-2">
      <div><div class="eyebrow2">JENIS KEJADIAN</div><strong><?= e($report['jenis_kejadian']) ?></strong></div>
      <div><div class="eyebrow2">PRIORITAS</div><strong class="priority-<?= $report['prioritas']==='tinggi'?'high':($report['prioritas']==='sedang'?'mid':'low') ?>"><?= strtoupper(e($report['prioritas'])) ?></strong></div>
      <div style="grid-column:1/-1"><div class="eyebrow2">LOKASI</div><strong><?= e($report['lokasi']) ?></strong></div>
      <div style="grid-column:1/-1"><div class="eyebrow2">DESKRIPSI</div><div class="muted"><?= e($report['deskripsi']) ?></div></div>
    </div>

    <div class="card" style="margin-top:15px;padding:13px;background:#fbfcfd">
      <h2>PROGRESS WAKTU</h2>
      <?php $steps=[['W1','Laporan diterima',$report['waktu_laporan']],['W2','Operator input',$report['waktu_input']],['W3','Link dikirim',$report['waktu_kirim']],['W4','Link dibuka',$report['waktu_buka']],['W5','Berangkat',$report['waktu_berangkat']],['W6','Tiba di TKP',$report['waktu_tiba']],['W7','Selesai',$report['waktu_selesai']]]; ?>
      <?php foreach($steps as $st): ?><div class="step <?= $st[2]?'done':($next==='selesai'&&$st[0]==='W6'?'current':'') ?>"><div class="step-dot"></div><span><strong><?= $st[0] ?></strong> — <?= e($st[1]) ?> · <?= fmt_dt($st[2]) ?></span></div><?php endforeach; ?>
    </div>

    <?php if($report['status']!=='selesai'): ?>
    <form method="post" style="margin-top:15px">
      <?= csrf_field() ?>
      <div class="mobile-actions">
        <button class="btn btn-primary" name="action" value="berangkat" <?= $report['waktu_berangkat']?'disabled':'' ?>>Berangkat</button>
        <button class="btn btn-gold" name="action" value="tiba" <?= (!$report['waktu_berangkat']||$report['waktu_tiba'])?'disabled':'' ?>>Tiba di TKP</button>
        <button class="btn btn-ok" type="button" onclick="document.getElementById('finish').scrollIntoView({behavior:'smooth'})" <?= !$report['waktu_tiba']?'disabled':'' ?>>Selesai</button>
      </div>
    </form>

    <form id="finish" method="post" enctype="multipart/form-data" style="margin-top:18px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="selesai">
      <div class="field"><label>Hasil Penanganan *</label><textarea name="hasil" required placeholder="Ringkasan tindakan di lapangan..."><?= e($report['hasil']) ?></textarea></div>
      <div class="field"><label>Foto Dokumentasi * (1–5 file, JPG/PNG/WebP, max 5 MB/file)</label><input type="file" name="foto[]" accept="image/jpeg,image/png,image/webp" multiple required></div>
      <div class="field"><label>Tanda Tangan Digital (opsional)</label><div class="signature-wrap"><canvas id="signature"></canvas></div><button type="button" class="btn btn-outline" id="clear-signature" style="margin-top:7px">Bersihkan Tanda Tangan</button><input type="hidden" name="signature" id="signature-data"></div>
      <button class="btn btn-ok btn-block" <?= !$report['waktu_tiba']?'disabled':'' ?>>Konfirmasi Laporan Selesai</button>
    </form>
    <?php else: ?>
      <div class="flash success" style="margin-top:16px">Laporan sudah selesai. Timestamp dan dokumentasi telah dikunci.</div>
      <?php if($report['hasil']): ?><div class="card" style="margin-top:12px"><h2>HASIL PENANGANAN</h2><p><?= nl2br(e($report['hasil'])) ?></p></div><?php endif; ?>
    <?php endif; ?>
    <?php if($photos): ?><div style="margin-top:16px"><div class="eyebrow2" style="margin-bottom:8px">DOKUMENTASI</div><div class="photos"><?php foreach($photos as $p): ?><div class="photo"><img src="<?= e($p['file_path']) ?>" alt="Dokumentasi"></div><?php endforeach; ?></div></div><?php endif; ?>
  </div>
</div>
<script>
const canvas=document.getElementById('signature');
if(canvas){
 const ctx=canvas.getContext('2d'); let drawing=false; const resize=()=>{const r=canvas.getBoundingClientRect(),d=devicePixelRatio||1;canvas.width=r.width*d;canvas.height=180*d;ctx.scale(d,d);ctx.lineWidth=2;ctx.lineCap='round';};
 resize(); window.addEventListener('resize',resize);
 const pos=e=>{const r=canvas.getBoundingClientRect(),p=e.touches?e.touches[0]:e;return{x:p.clientX-r.left,y:p.clientY-r.top}};
 const start=e=>{drawing=true;const p=pos(e);ctx.beginPath();ctx.moveTo(p.x,p.y);e.preventDefault()};
 const move=e=>{if(!drawing)return;const p=pos(e);ctx.lineTo(p.x,p.y);ctx.stroke();e.preventDefault()};
 const end=()=>{drawing=false;document.getElementById('signature-data').value=canvas.toDataURL('image/png')};
 ['mousedown','touchstart'].forEach(x=>canvas.addEventListener(x,start,{passive:false}));
 ['mousemove','touchmove'].forEach(x=>canvas.addEventListener(x,move,{passive:false}));
 ['mouseup','mouseleave','touchend'].forEach(x=>canvas.addEventListener(x,end));
 document.getElementById('clear-signature').onclick=()=>{ctx.clearRect(0,0,canvas.width,canvas.height);document.getElementById('signature-data').value=''};
}
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
