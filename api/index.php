<?php

putenv('LOG_CHANNEL=errorlog');
$_ENV['LOG_CHANNEL'] = 'errorlog';
$_SERVER['LOG_CHANNEL'] = 'errorlog';

$configCache = getenv('APP_CONFIG_CACHE') ?: '/tmp/config.php';
if (is_file($configCache)) {
    @unlink($configCache);
}

// Bootstrap directories untuk Vercel /tmp (satu-satunya direktori writable)
$tmpBase = '/tmp';
foreach ([
    $tmpBase . '/storage/framework/views',
    $tmpBase . '/storage/framework/sessions',
    $tmpBase . '/storage/framework/cache/data',
    $tmpBase . '/storage/app/public',
    $tmpBase . '/storage/logs',
    $tmpBase . '/views',
    $tmpBase . '/cache',
] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Override APP_BASE_PATH ke root project
$_ENV['APP_BASE_PATH'] = dirname(__DIR__);

// Auto-create SQLite database
$sqliteDb = $tmpBase . '/database.sqlite';
if (!file_exists($sqliteDb)) {
    @touch($sqliteDb);
}

try {
    $pdo = new PDO("sqlite:{$sqliteDb}");
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
    );");
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
        requester_name TEXT,
        requester_photo_url TEXT,
        purpose TEXT,
        sharing_enabled INTEGER DEFAULT 1,
        sharing_revoked_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");
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
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");
} catch (\Throwable $e) {
    // Ignore
}

require __DIR__ . '/../public/index.php';

