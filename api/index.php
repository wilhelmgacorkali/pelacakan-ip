<?php

putenv('LOG_CHANNEL=stderr');
$_ENV['LOG_CHANNEL'] = 'stderr';
$_SERVER['LOG_CHANNEL'] = 'stderr';

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
    } catch (\Throwable $e) {
        // Ignore
    }
}

require __DIR__ . '/../public/index.php';
