<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['pimpinan']);
header('Content-Type: application/json; charset=utf-8');

$today = db()->query("SELECT
 COUNT(*) total,
 SUM(status='selesai') selesai,
 SUM(status='proses') proses,
 SUM(status='baru') baru,
 AVG(CASE WHEN waktu_laporan IS NOT NULL AND waktu_tiba IS NOT NULL THEN TIMESTAMPDIFF(SECOND,waktu_laporan,waktu_tiba) END) avg_response,
 AVG(CASE WHEN waktu_berangkat IS NOT NULL AND waktu_tiba IS NOT NULL THEN TIMESTAMPDIFF(SECOND,waktu_berangkat,waktu_tiba) END) avg_travel,
 AVG(CASE WHEN waktu_tiba IS NOT NULL AND waktu_selesai IS NOT NULL THEN TIMESTAMPDIFF(SECOND,waktu_tiba,waktu_selesai) END) avg_handling
 FROM laporan WHERE DATE(created_at)=CURDATE()")->fetch();

$weekly = db()->query("SELECT DATE(created_at) hari, COUNT(*) total,
 AVG(CASE WHEN waktu_laporan IS NOT NULL AND waktu_tiba IS NOT NULL THEN TIMESTAMPDIFF(SECOND,waktu_laporan,waktu_tiba) END) response
 FROM laporan WHERE created_at >= CURDATE()-INTERVAL 6 DAY GROUP BY DATE(created_at) ORDER BY hari")->fetchAll();

$types = db()->query("SELECT jenis_kejadian jenis, COUNT(*) total FROM laporan GROUP BY jenis_kejadian ORDER BY total DESC LIMIT 8")->fetchAll();

$leader = db()->query("SELECT COALESCE(CAST(regu_id AS CHAR),'Belum ditetapkan') regu,
 COUNT(*) total,
 SUM(status='selesai') selesai,
 AVG(CASE WHEN waktu_laporan IS NOT NULL AND waktu_tiba IS NOT NULL THEN TIMESTAMPDIFF(SECOND,waktu_laporan,waktu_tiba) END) avg_response
 FROM laporan GROUP BY regu_id ORDER BY avg_response IS NULL, avg_response ASC")->fetchAll();

echo json_encode(['ok'=>true,'today'=>$today,'weekly'=>$weekly,'types'=>$types,'leaderboard'=>$leader],JSON_UNESCAPED_UNICODE);
