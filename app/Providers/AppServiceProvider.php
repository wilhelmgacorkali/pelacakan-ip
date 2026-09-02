<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Override storage paths untuk Vercel/serverless (DIRECTORY_SEPARATOR === '/' && /tmp tersedia)
        // Ini menangani Vercel, Railway, dan platform Linux lainnya dengan read-only filesystem
        if (DIRECTORY_SEPARATOR === '/' && is_dir('/tmp')) {
            $tmpBase = '/tmp';

            // Pastikan semua direktori yang dibutuhkan Laravel tersedia di /tmp
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

            // Override storage path ke /tmp/storage
            $this->app->useStoragePath($tmpBase . '/storage');
        }
    }

    public function boot(): void
    {
        if (DIRECTORY_SEPARATOR === '/' && is_dir('/tmp')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
