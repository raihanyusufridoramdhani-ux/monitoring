<?php
require_once __DIR__ . '/config/config.php';
if (!empty($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'operator') header('Location: /pcc-110/operator/dashboard.php');
    elseif ($role === 'pimpinan') header('Location: /pcc-110/pimpinan/dashboard.php');
    else header('Location: /pcc-110/login.php');
} else {
    header('Location: /pcc-110/login.php');
}
exit;
