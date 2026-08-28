<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function login_user(string $username, string $password): bool {
    $stmt = db()->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'nama' => $user['nama'],
        'role' => $user['role'],
        'regu_id' => $user['regu_id'],
    ];
    audit_log('Login', 'User berhasil login');
    return true;
}

function logout_user(): void {
    if (!empty($_SESSION['user'])) {
        audit_log('Logout', 'User logout');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(array $roles = []): void {
    $user = current_user();
    if (!$user) {
        header('Location: /pcc-110/login.php');
        exit;
    }
    if ($roles && !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('403 - Akses ditolak.');
    }
}
