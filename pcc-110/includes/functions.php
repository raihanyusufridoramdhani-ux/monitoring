<?php
require_once __DIR__ . '/db.php';

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Token CSRF tidak valid.');
    }
}

function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array {
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function audit_log(string $activity, string $detail = ''): void {
    try {
        $userId = $_SESSION['user']['id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $stmt = db()->prepare("INSERT INTO audit_log (user_id, aktivitas, keterangan, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $activity, $detail, $ip]);
    } catch (Throwable $e) {
        // Logging must never break the main transaction.
    }
}

function generate_ticket(): array {
    $date = date('Ymd');
    $pdo = db();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM laporan WHERE DATE(created_at)=CURDATE()");
    $stmt->execute();
    $seq = (int)$stmt->fetchColumn() + 1;
    $seq = max(1, $seq);
    return [
        'tiket' => sprintf('TIKET-%s-%04d', $date, $seq),
        'nomor_laporan' => sprintf('LP-%s-%04d', $date, $seq),
        'token' => bin2hex(random_bytes(16)),
    ];
}

function fmt_dt(?string $dt): string {
    if (!$dt) return '—';
    return date('d/m/Y H:i:s', strtotime($dt));
}

function seconds_between(?string $a, ?string $b): ?int {
    if (!$a || !$b) return null;
    $x = strtotime($a); $y = strtotime($b);
    return $y >= $x ? $y - $x : null;
}

function duration_human(?int $seconds): string {
    if ($seconds === null) return '—';
    $h = intdiv($seconds, 3600);
    $m = intdiv($seconds % 3600, 60);
    $s = $seconds % 60;
    if ($h > 0) return sprintf('%dj %02dm', $h, $m);
    return sprintf('%02dm %02ds', $m, $s);
}

function response_seconds(array $row): ?int {
    return seconds_between($row['waktu_laporan'] ?? null, $row['waktu_tiba'] ?? null);
}

function sla_class(?int $seconds): string {
    if ($seconds === null) return 'muted';
    if ($seconds < 300) return 'ok';
    if ($seconds <= 600) return 'warn';
    return 'alert';
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
