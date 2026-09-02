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
        tracking_code TEXT UNIQUE NOT NULL,
        device_name TEXT,
        model TEXT,
        platform TEXT,
        os_version TEXT,
        browser TEXT,
        screen_resolution TEXT,
        battery_level REAL,
        is_charging INTEGER DEFAULT 0,
        ip_address TEXT,
        country TEXT,
        city TEXT,
        isp TEXT,
        network_type TEXT,
        latitude REAL,
        longitude REAL,
        accuracy REAL,
        status TEXT DEFAULT 'active',
        last_seen_at DATETIME,
        requester_ip TEXT,
        requester_city TEXT,
        requester_country TEXT,
        requester_isp TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");
    $pdo->exec("CREATE TABLE IF NOT EXISTS device_locations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        device_id INTEGER NOT NULL,
        latitude REAL NOT NULL,
        longitude REAL NOT NULL,
        accuracy REAL,
        speed REAL,
        heading REAL,
        altitude REAL,
        recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");
} catch (\Throwable $e) {
    // Ignore
}

require __DIR__ . '/../public/index.php';

