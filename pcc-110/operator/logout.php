<?php
require_once __DIR__ . '/../includes/auth.php';
logout_user();
header('Location: /pcc-110/login.php');
exit;
