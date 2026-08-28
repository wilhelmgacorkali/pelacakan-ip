<?php

// Remove old Console/Http Kernel (Laravel 11 doesn't need them)
// Laravel 11 uses the new bootstrap/app.php approach

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Override storage paths for Vercel serverless environment
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
            $tmpBase = '/tmp';

            // Override app storage path to /tmp
            $this->app->useStoragePath($tmpBase . '/storage');

            // Ensure directories exist
            foreach ([
                $tmpBase . '/storage/framework/views',
                $tmpBase . '/storage/framework/sessions',
                $tmpBase . '/storage/framework/cache/data',
                $tmpBase . '/storage/app',
                $tmpBase . '/storage/logs',
            ] as $dir) {
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
            }
        }
    }

    public function boot(): void
    {
        //
    }
}
