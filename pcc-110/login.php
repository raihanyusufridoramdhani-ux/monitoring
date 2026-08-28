<?php
require_once __DIR__ . '/includes/auth.php';
if (current_user()) redirect('/pcc-110/');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (login_user(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) {
        $role = current_user()['role'];
        redirect($role === 'operator' ? '/pcc-110/operator/dashboard.php' : ($role === 'pimpinan' ? '/pcc-110/pimpinan/dashboard.php' : '/pcc-110/pamapta/detail.php'));
    }
    $error = 'Username atau password tidak valid.';
}
$page_title = 'Login — PCC-110';
require __DIR__ . '/includes/header.php';
?>
<div class="login-card">
PCC-110 | LOGO INSTANSI
<!-- <div class="pcc-login-logo-row">

    <img
        src="assets/images/logo-tribrata-polri.png"
        alt="Tribrata Polri"
        class="pcc-logo-tribrata"
    >

    <img
        src="assets/images/logo-polda-jawatengah.png"
        alt="Polda Jawa Tengah"
        class="pcc-logo-polda"
    >

    <img
        src="assets/images/logo-libas.jpg"
        alt="LIBAS"
        class="pcc-logo-libas"
    >

</div> -->
  <div class="login-logo">110</div>
  <div class="eyebrow">POLRESTABES SEMARANG</div>
  <h1>PCC-110</h1>
  <p>Sistem Monitoring Response Time Layanan 110. Pilih akun sesuai role untuk pengujian lokal.</p>
  <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="field"><label>Username</label><input name="username" autocomplete="username" required></div>
    <div class="field"><label>Password</label><input type="password" name="password" autocomplete="current-password" required></div>
    <button class="btn btn-primary btn-block">Masuk ke Sistem</button>
  </form>
  <div class="login-meta"><strong>DATA DUMMY:</strong> operator / password · pamapta / password · pimpinan / password</div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
