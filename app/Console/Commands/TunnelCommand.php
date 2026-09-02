<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class TunnelCommand extends Command
{
    /**
     * php artisan tracker:tunnel
     *
     * Menjalankan `php artisan serve` lokal + tunnel ngrok sekaligus,
     * lalu menampilkan URL publik ngrok supaya link Device Agent bisa
     * langsung dibagikan ke perangkat lain di luar jaringan lokal.
     */
    protected $signature = 'tracker:tunnel
        {--port=8000 : Port lokal untuk php artisan serve}
        {--sync-env : Otomatis tulis ulang APP_URL di .env dengan URL ngrok}';

    protected $description = 'Jalankan server lokal + ngrok tunnel untuk GeoTrack Pro';

    public function handle(): int
    {
        $port = (int) $this->option('port');
        $ngrokBin = config('ngrok.binary', 'ngrok');
        $authtoken = config('ngrok.authtoken');
        $domain = config('ngrok.domain');

        if (!$this->binaryExists($ngrokBin)) {
            $this->error("Binary ngrok tidak ditemukan ('{$ngrokBin}'). Install dari https://ngrok.com/download lalu pastikan ada di PATH, atau set NGROK_BINARY di .env ke path lengkapnya.");
            return self::FAILURE;
        }

        if ($authtoken) {
            $this->info('Mengatur ngrok authtoken...');
            (new Process([$ngrokBin, 'config', 'add-authtoken', $authtoken]))->run();
        }

        $this->info("Menjalankan php artisan serve di port {$port}...");
        $serve = new Process(['php', 'artisan', 'serve', '--host=127.0.0.1', "--port={$port}"], base_path());
        $serve->start();

        $ngrokArgs = [$ngrokBin, 'http', (string) $port, '--log=stdout'];
        if ($domain) {
            $ngrokArgs[] = "--domain={$domain}";
        }

        $this->info('Menjalankan ngrok tunnel...');
        $ngrok = new Process($ngrokArgs, base_path());
        $ngrok->start();

        $publicUrl = $this->waitForNgrokUrl();

        if (!$publicUrl) {
            $this->error('Gagal mendapatkan URL publik dari ngrok. Cek apakah ngrok berhasil terhubung (authtoken valid?).');
            $serve->stop();
            $ngrok->stop();
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('======================================================');
        $this->info(" URL publik ngrok : {$publicUrl}");
        $this->info(" Dashboard devices: {$publicUrl}/devices");
        $this->info('======================================================');
        $this->newLine();
        $this->comment('Bagikan link Device Agent yang memakai domain di atas ke penerima. Tekan Ctrl+C untuk menghentikan server & tunnel.');

        if ($this->option('sync-env')) {
            $this->syncEnvUrl($publicUrl);
        }

        // Tetap berjalan sampai salah satu proses berhenti / user menekan Ctrl+C.
        while ($serve->isRunning() && $ngrok->isRunning()) {
            usleep(500_000);
        }

        $serve->stop();
        $ngrok->stop();

        return self::SUCCESS;
    }

    private function binaryExists(string $bin): bool
    {
        $which = new Process(['which', $bin]);
        $which->run();

        return $which->isSuccessful();
    }

    private function waitForNgrokUrl(int $timeoutSeconds = 20): ?string
    {
        $deadline = time() + $timeoutSeconds;

        while (time() < $deadline) {
            try {
                $response = Http::timeout(2)->get('http://127.0.0.1:4040/api/tunnels');
                if ($response->ok()) {
                    $tunnels = $response->json('tunnels', []);
                    foreach ($tunnels as $tunnel) {
                        if (($tunnel['proto'] ?? null) === 'https') {
                            return $tunnel['public_url'];
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ngrok API belum siap, coba lagi sebentar.
            }

            usleep(500_000);
        }

        return null;
    }

    private function syncEnvUrl(string $publicUrl): void
    {
        $envPath = base_path('.env');
        if (!is_file($envPath)) {
            return;
        }

        $contents = file_get_contents($envPath);
        $escaped = preg_quote($publicUrl, '/');

        if (preg_match('/^APP_URL=.*$/m', $contents)) {
            $contents = preg_replace('/^APP_URL=.*$/m', "APP_URL={$publicUrl}", $contents);
        } else {
            $contents .= "\nAPP_URL={$publicUrl}\n";
        }

        file_put_contents($envPath, $contents);
        $this->info('APP_URL di .env telah disinkronkan dengan URL ngrok.');
    }
}
