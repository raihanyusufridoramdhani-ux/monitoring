<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['operator']);
require_once __DIR__ . '/../includes/whatsapp.php';
verify_csrf();
$id=(int)($_POST['id']??0);
$stmt=db()->prepare("SELECT * FROM laporan WHERE id=? LIMIT 1"); $stmt->execute([$id]); $r=$stmt->fetch();
if(!$r) { http_response_code(404); exit('Laporan tidak ditemukan.'); }
if(!$r['waktu_kirim']) {
  db()->prepare("UPDATE laporan SET waktu_kirim=NOW(), status='proses' WHERE id=?")->execute([$id]);
  audit_log('Kirim WA','W3 tercatat untuk '.$r['tiket']);
}
header('Location: '.whatsapp_url($r));
exit;
