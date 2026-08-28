<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['pimpinan']);

if (!class_exists('\Dompdf\Dompdf')) {
    http_response_code(503);
    exit('Dompdf belum terpasang. Jalankan: composer install dari C:\\laragon\\www\\pcc-110');
}
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
$rows=db()->query("SELECT * FROM laporan ORDER BY created_at DESC LIMIT 500")->fetchAll();
$html='<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans;font-size:9px}h1{font-size:18px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #bbb;padding:4px}th{background:#eee}</style></head><body>';
$html.='<h1>Rekap Sistem Monitoring Response Time Layanan 110</h1><p>Dicetak '.date('d/m/Y H:i:s').' WIB</p><table><tr><th>Tiket</th><th>Jenis</th><th>Lokasi</th><th>Status</th><th>Response</th><th>Travel</th><th>Handling</th></tr>';
foreach($rows as $r){$html.='<tr><td>'.e($r['tiket']).'</td><td>'.e($r['jenis_kejadian']).'</td><td>'.e($r['lokasi']).'</td><td>'.e($r['status']).'</td><td>'.e(duration_human(response_seconds($r))).'</td><td>'.e(duration_human(seconds_between($r['waktu_berangkat'],$r['waktu_tiba']))).'</td><td>'.e(duration_human(seconds_between($r['waktu_tiba'],$r['waktu_selesai']))).'</td></tr>';}
$html.='</table></body></html>';
$d=new Dompdf();$d->loadHtml($html);$d->setPaper('A4','landscape');$d->render();$d->stream('pcc-110-rekap-'.date('Ymd-His').'.pdf',['Attachment'=>false]);
