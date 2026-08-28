<?php
require_once __DIR__ . '/functions.php';

function report_public_url(string $token): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/pcc-110/pamapta/detail.php?token=' . rawurlencode($token);
}

function whatsapp_message(array $report): string {
    $url = report_public_url($report['token_akses']);
    return "LAPORAN LAYANAN 110\n"
        . "Tiket: {$report['tiket']}\n"
        . "Nomor Laporan: {$report['nomor_laporan']}\n"
        . "Prioritas: " . strtoupper($report['prioritas']) . "\n"
        . "Jenis: {$report['jenis_kejadian']}\n"
        . "Lokasi: {$report['lokasi']}\n\n"
        . "Buka laporan: {$url}";
}

function whatsapp_url(array $report): string {
    return 'https://wa.me/?text=' . rawurlencode(whatsapp_message($report));
}
