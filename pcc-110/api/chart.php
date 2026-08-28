<?php
require_once __DIR__ . '/../includes/auth.php';
require_login(['pimpinan']);
header('Content-Type: application/json; charset=utf-8');

$range=$_GET['range']??'7d';
$days=$range==='30d'?30:($range==='90d'?90:7);
$stmt=db()->prepare("SELECT DATE(created_at) label, COUNT(*) total,
 AVG(CASE WHEN waktu_laporan IS NOT NULL AND waktu_tiba IS NOT NULL THEN TIMESTAMPDIFF(SECOND,waktu_laporan,waktu_tiba) END) response
 FROM laporan WHERE created_at>=CURDATE()-INTERVAL ? DAY GROUP BY DATE(created_at) ORDER BY label");
$stmt->execute([$days-1]);
echo json_encode(['ok'=>true,'data'=>$stmt->fetchAll()],JSON_UNESCAPED_UNICODE);
