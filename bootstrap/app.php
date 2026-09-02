<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        // Pengecualian CSRF untuk endpoint API dan device tracking
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'devices/*',
            'device-agent/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

// Pastikan storage path ke /tmp/storage pada Vercel / Linux serverless
// AppServiceProvider juga melakukan ini, tapi diperlukan lebih awal di sini
// agar config/view.php bisa dibaca sebelum ServiceProvider boot
if (DIRECTORY_SEPARATOR === '/' && is_dir('/tmp')) {
    $storagePath = env('APP_STORAGE', '/tmp/storage');
    $app->useStoragePath($storagePath);
}

return $app;
