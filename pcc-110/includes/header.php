<?php
require_once __DIR__ . '/auth.php';
$user = current_user();
$flashes = flashes();
$current = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title ?? APP_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/pcc-110/assets/css/app.css">
</head>
<body>
<div class="app-shell">
<?php if ($user): ?>
<aside class="sidebar">
  <div class="brand">
    <div class="brand-mark">110</div>
    <div><div class="brand-title">PCC-110</div><div class="brand-sub">MONITORING SYSTEM</div></div>
  </div>
  <nav class="nav">
    <?php if ($user['role'] === 'operator'): ?>
      <a class="<?= str_contains($current,'operator') ? 'active':'' ?>" href="/pcc-110/operator/dashboard.php">Antrean Laporan</a>
      <a href="/pcc-110/operator/laporan-baru.php">Input Laporan</a>
    <?php elseif ($user['role'] === 'pimpinan'): ?>
      <a class="<?= str_contains($current,'pimpinan') ? 'active':'' ?>" href="/pcc-110/pimpinan/dashboard.php">Dashboard Monitoring</a>
    <?php endif; ?>
  </nav>
  <div class="sidebar-foot">
    <div class="role-chip"><?= e(strtoupper($user['role'])) ?></div>
    <div class="muted small">Akses: <?= e($user['username']) ?></div>
    <a class="btn btn-ghost btn-block" href="/pcc-110/operator/logout.php">Keluar</a>
  </div>
</aside>
<?php endif; ?>
<main class="<?= $user ? 'main' : 'main public-main' ?>">
<?php if ($user): ?>
<header class="topbar">
  <div>
    <div class="eyebrow"><?= $user['role']==='operator' ? 'OPERATOR COMMAND CENTER' : 'MONITORING PIMPINAN' ?></div>
    <div class="topbar-title"><?= e($page_title ?? APP_TITLE) ?></div>
  </div>
  <div class="status-live"><span></span> SYSTEM ONLINE</div>
</header>
<?php endif; ?>
<?php foreach ($flashes as $f): ?>
<div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
