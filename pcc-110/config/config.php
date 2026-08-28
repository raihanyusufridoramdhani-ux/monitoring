<?php
declare(strict_types=1);

const APP_NAME = 'PCC-110';
const APP_TITLE = 'Sistem Monitoring Response Time Layanan 110';
const BASE_PATH = __DIR__ . '/..';

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'pcc-110';
const DB_USER = 'root';
const DB_PASS = '';

const UPLOAD_DIR = BASE_PATH . '/storage/uploads';
const MAX_UPLOADS = 5;
const MAX_FILE_SIZE = 5 * 1024 * 1024;

date_default_timezone_set('Asia/Jakarta');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('pcc110_session');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0775, true);
}
