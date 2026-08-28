<?php

// Tampilkan error secara eksplisit jika terjadi kegagalan bootstrap di serverless
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Pastikan direktori /tmp untuk Vercel Serverless terbuat
$tmpDirs = [
    '/tmp/views',
    '/tmp/sessions',
    '/tmp/cache',
    '/tmp/logs',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/cache'
];

foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Inisialisasi SQLite di /tmp
$sqliteDb = '/tmp/database.sqlite';
if (!file_exists($sqliteDb)) {
    @touch($sqliteDb);
    try {
        $pdo = new PDO("sqlite:$sqliteDb");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE TABLE IF NOT EXISTS search_histories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT NOT NULL,
            query TEXT NOT NULL,
            title TEXT,
            result_json TEXT,
            client_ip TEXT,
            status TEXT DEFAULT 'success',
            created_at DATETIME,
            updated_at DATETIME
        );");
    } catch (\Throwable $e) {
        // Abaikan jika sudah ada
    }
}

// Forward request to Laravel entrypoint
if (file_exists(__DIR__ . '/../public/index.php')) {
    require __DIR__ . '/../public/index.php';
} else {
    echo "<h1>Entrypoint Laravel tidak ditemukan di /public/index.php</h1>";
}
