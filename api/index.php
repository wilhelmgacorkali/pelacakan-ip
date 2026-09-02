<?php

/**
 * Vercel Serverless Entry Point — GeoTrack Pro
 * 
 * Di Vercel, filesystem adalah read-only kecuali /tmp.
 * File ini menyiapkan semua yang dibutuhkan Laravel sebelum bootstrap.
 */

// --- 1. Paksa logging ke errorlog (tidak perlu filesystem) ---
putenv('LOG_CHANNEL=errorlog');
$_ENV['LOG_CHANNEL']  = 'errorlog';
$_SERVER['LOG_CHANNEL'] = 'errorlog';

// --- 2. Hapus config cache lama agar selalu fresh ---
foreach ([
    getenv('APP_CONFIG_CACHE')   ?: '/tmp/config.php',
    getenv('APP_ROUTES_CACHE')   ?: '/tmp/routes.php',
    getenv('APP_EVENTS_CACHE')   ?: '/tmp/events.php',
    getenv('APP_PACKAGES_CACHE') ?: '/tmp/packages.php',
    getenv('APP_SERVICES_CACHE') ?: '/tmp/services.php',
] as $cacheFile) {
    if (is_file($cacheFile)) {
        @unlink($cacheFile);
    }
}

// --- 3. Buat semua direktori writable di /tmp ---
$tmpBase = '/tmp';
foreach ([
    $tmpBase . '/storage',
    $tmpBase . '/storage/framework',
    $tmpBase . '/storage/framework/views',
    $tmpBase . '/storage/framework/sessions',
    $tmpBase . '/storage/framework/cache',
    $tmpBase . '/storage/framework/cache/data',
    $tmpBase . '/storage/framework/testing',
    $tmpBase . '/storage/app',
    $tmpBase . '/storage/app/public',
    $tmpBase . '/storage/logs',
] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// --- 4. Auto-inisialisasi SQLite database dengan SEMUA tabel yang dibutuhkan ---
$sqliteDb = $tmpBase . '/database.sqlite';
if (!file_exists($sqliteDb)) {
    @touch($sqliteDb);
}

try {
    $pdo = new PDO("sqlite:{$sqliteDb}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Riwayat pencarian IP / nomor HP
    $pdo->exec("CREATE TABLE IF NOT EXISTS search_histories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT NOT NULL,
        query TEXT NOT NULL,
        title TEXT,
        result_json TEXT,
        client_ip TEXT,
        status TEXT DEFAULT 'success',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Perangkat yang didaftarkan untuk dilacak
    $pdo->exec("CREATE TABLE IF NOT EXISTS devices (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT,
        phone TEXT,
        device_token TEXT UNIQUE NOT NULL,
        platform TEXT,
        user_agent TEXT,
        last_seen_at DATETIME,
        is_active INTEGER DEFAULT 1,
        requester_name TEXT DEFAULT 'Pemilik / Admin',
        requester_photo_url TEXT,
        purpose TEXT DEFAULT 'Berbagi lokasi real-time',
        sharing_enabled INTEGER DEFAULT 1,
        sharing_revoked_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Riwayat lokasi GPS perangkat
    $pdo->exec("CREATE TABLE IF NOT EXISTS device_locations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        device_id INTEGER NOT NULL,
        latitude REAL NOT NULL,
        longitude REAL NOT NULL,
        accuracy REAL,
        altitude REAL,
        speed REAL,
        heading REAL,
        ip_address TEXT,
        user_agent TEXT,
        recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
    )");

    // Tabel migrasi Laravel (agar Schema::hasTable bekerja tanpa error)
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        migration TEXT NOT NULL,
        batch INTEGER NOT NULL
    )");

} catch (\Throwable $e) {
    // Lanjutkan meski DB gagal — Laravel punya fallback tersendiri
}

// --- 5. Jalankan aplikasi Laravel ---
require __DIR__ . '/../public/index.php';
