<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['pimpinan']);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="pcc-110-rekap-'.date('Ymd-His').'.csv"');
$out=fopen('php://output','w');
fputcsv($out,['Tiket','Nomor Laporan','Lokasi','Jenis','Prioritas','Status','W1','W2','W3','W4','W5','W6','W7','Response Time','Travel Time','Handling Time','Hasil']);
$stmt=db()->query("SELECT * FROM laporan ORDER BY created_at DESC");
while($r=$stmt->fetch()){
  fputcsv($out,[$r['tiket'],$r['nomor_laporan'],$r['lokasi'],$r['jenis_kejadian'],$r['prioritas'],$r['status'],$r['waktu_laporan'],$r['waktu_input'],$r['waktu_kirim'],$r['waktu_buka'],$r['waktu_berangkat'],$r['waktu_tiba'],$r['waktu_selesai'],duration_human(response_seconds($r)),duration_human(seconds_between($r['waktu_berangkat'],$r['waktu_tiba'])),duration_human(seconds_between($r['waktu_tiba'],$r['waktu_selesai'])),$r['hasil']]);
}
fclose($out);
