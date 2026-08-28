<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpTrackerService
{
    /**
     * Lacak informasi detail dari sebuah IP address
     *
     * @param string|null $ip
     * @return array
     */
    public function track(?string $ip = null): array
    {
        $targetIp = trim($ip ?? '');

        // Jika IP kosong atau 'me', ambil IP client
        if (empty($targetIp) || $targetIp === 'me') {
            $targetIp = request()->ip() ?? '';
        }

        // Cek jika IP adalah localhost / private IP
        if ($this->isPrivateOrLocal($targetIp)) {
            // Coba ambil public IP dari client melalui external header atau fallback ke public IP mesin
            $publicIp = $this->getMyPublicIp();
            if ($publicIp && !$this->isPrivateOrLocal($publicIp)) {
                $targetIp = $publicIp;
            } else {
                // Fallback default IP publik representatif untuk demo jika di localhost offline
                $targetIp = '8.8.8.8'; 
            }
        }

        // Validasi format IP (IPv4 atau IPv6)
        if (!filter_var($targetIp, FILTER_VALIDATE_IP)) {
            return [
                'status' => 'error',
                'message' => 'Format IP Address tidak valid. Harap masukkan format IPv4 (contoh: 8.8.8.8) atau IPv6 yang benar.',
                'query' => $targetIp
            ];
        }

        // 1. Coba provider primer: ip-api.com (Detail lengkap: ISP, Org, ASN, Proxy, Lat/Long)
        $result = $this->fetchFromIpApi($targetIp);
        if ($result['status'] === 'success') {
            return $result;
        }

        // 2. Fallback provider sekunder: ipwhois.app
        $fallbackResult = $this->fetchFromIpWhois($targetIp);
        if ($fallbackResult['status'] === 'success') {
            return $fallbackResult;
        }

        return [
            'status' => 'error',
            'message' => 'Gagal mengambil data geolokasi untuk IP ini. Periksa koneksi internet atau coba beberapa saat lagi.',
            'query' => $targetIp
        ];
    }

    /**
     * Provider Primer: ip-api.com
     */
    private function fetchFromIpApi(string $ip): array
    {
        try {
            // Fields: status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,query,proxy,hosting
            $response = Http::timeout(6)->get("http://ip-api.com/json/{$ip}?fields=66846719");

            if ($response->successful()) {
                $data = $response->json();

                if (($data['status'] ?? '') === 'success') {
                    $hostname = @gethostbyaddr($ip);

                    return [
                        'status' => 'success',
                        'provider' => 'ip-api.com',
                        'ip' => $data['query'] ?? $ip,
                        'ip_version' => str_contains($ip, ':') ? 'IPv6' : 'IPv4',
                        'country' => $data['country'] ?? 'Unknown',
                        'country_code' => $data['countryCode'] ?? '',
                        'country_flag' => $this->getCountryFlagEmoji($data['countryCode'] ?? ''),
                        'region' => $data['regionName'] ?? '',
                        'city' => $data['city'] ?? '',
                        'postal_code' => !empty($data['zip']) ? $data['zip'] : '-',
                        'latitude' => $data['lat'] ?? 0.0,
                        'longitude' => $data['lon'] ?? 0.0,
                        'timezone' => $data['timezone'] ?? 'UTC',
                        'isp' => $data['isp'] ?? 'Unknown ISP',
                        'organization' => $data['org'] ?? ($data['isp'] ?? '-'),
                        'asn' => $data['as'] ?? '-',
                        'hostname' => ($hostname && $hostname !== $ip) ? $hostname : '-',
                        'currency' => $data['currency'] ?? 'IDR',
                        'is_proxy' => (bool) ($data['proxy'] ?? false),
                        'is_hosting' => (bool) ($data['hosting'] ?? false),
                        'coordinates_str' => ($data['lat'] ?? 0) . ', ' . ($data['lon'] ?? 0),
                        'tracked_at' => now()->translatedFormat('d F Y, H:i:s T')
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("IpTracker ip-api error: " . $e->getMessage());
        }

        return ['status' => 'fail'];
    }

    /**
     * Provider Sekunder / Fallback: ipwho.is
     */
    private function fetchFromIpWhois(string $ip): array
    {
        try {
            $response = Http::timeout(6)->get("https://ipwho.is/{$ip}");

            if ($response->successful()) {
                $data = $response->json();

                if (($data['success'] ?? false) === true) {
                    $hostname = @gethostbyaddr($ip);

                    return [
                        'status' => 'success',
                        'provider' => 'ipwho.is (fallback)',
                        'ip' => $data['ip'] ?? $ip,
                        'ip_version' => ($data['type'] ?? 'IPv4'),
                        'country' => $data['country'] ?? 'Unknown',
                        'country_code' => $data['country_code'] ?? '',
                        'country_flag' => $this->getCountryFlagEmoji($data['country_code'] ?? ''),
                        'region' => $data['region'] ?? '',
                        'city' => $data['city'] ?? '',
                        'postal_code' => !empty($data['postal']) ? $data['postal'] : '-',
                        'latitude' => $data['latitude'] ?? 0.0,
                        'longitude' => $data['longitude'] ?? 0.0,
                        'timezone' => $data['timezone']['id'] ?? 'UTC',
                        'isp' => $data['connection']['isp'] ?? 'Unknown ISP',
                        'organization' => $data['connection']['org'] ?? '-',
                        'asn' => $data['connection']['asn'] ?? '-',
                        'hostname' => ($hostname && $hostname !== $ip) ? $hostname : '-',
                        'currency' => $data['currency']['code'] ?? 'IDR',
                        'is_proxy' => (bool) ($data['security']['proxy'] ?? false),
                        'is_hosting' => (bool) ($data['security']['hosting'] ?? false),
                        'coordinates_str' => ($data['latitude'] ?? 0) . ', ' . ($data['longitude'] ?? 0),
                        'tracked_at' => now()->translatedFormat('d F Y, H:i:s T')
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("IpTracker ipwho.is error: " . $e->getMessage());
        }

        return ['status' => 'fail'];
    }

    /**
     * Cek apakah IP adalah localhost atau private network
     */
    public function isPrivateOrLocal(string $ip): bool
    {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Dapatkan IP publik saat ini
     */
    public function getMyPublicIp(): ?string
    {
        try {
            $resp = Http::timeout(3)->get('https://api.ipify.org?format=json');
            if ($resp->successful()) {
                return $resp->json('ip');
            }
        } catch (\Throwable $e) {
            // Ignore
        }
        return null;
    }

    /**
     * Konversi kode 2 huruf negara ke Emoji Bendera
     */
    private function getCountryFlagEmoji(string $countryCode): string
    {
        if (strlen($countryCode) !== 2) {
            return '🌐';
        }
        $countryCode = strtoupper($countryCode);
        $emoji = '';
        for ($i = 0; $i < 2; $i++) {
            $emoji .= mb_chr(ord($countryCode[$i]) - 65 + 0x1F1E6, 'UTF-8');
        }
        return $emoji;
    }
}
