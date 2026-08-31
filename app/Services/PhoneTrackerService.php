<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PhoneTrackerService
{
    /**
     * Database Prefix Operator Seluler di Indonesia
     */
    protected array $indonesianCarriers = [
        'Telkomsel' => [
            'prefixes' => ['0811', '0812', '0813', '0821', '0822', '0823', '0851', '0852', '0853'],
            'color' => '#E50914',
            'brand' => 'Telkomsel (Kartu Halo, simPATI, KARTU As, By.U)',
            'company' => 'PT Telekomunikasi Selular',
            'website' => 'https://www.telkomsel.com'
        ],
        'Indosat Ooredoo Hutchison (IM3)' => [
            'prefixes' => ['0814', '0815', '0816', '0855', '0856', '0857', '0858'],
            'color' => '#FFCC00',
            'brand' => 'Indosat Ooredoo (IM3, Mentari, Matrix)',
            'company' => 'PT Indosat Tbk (IOH)',
            'website' => 'https://indosatooredoo.com'
        ],
        'XL Axiata' => [
            'prefixes' => ['0817', '0818', '0819', '0859', '0877', '0878'],
            'color' => '#00539B',
            'brand' => 'XL (XL Prabayar, PRIORITAS, Live.On)',
            'company' => 'PT XL Axiata Tbk',
            'website' => 'https://www.xl.co.id'
        ],
        'AXIS (XL Axiata)' => [
            'prefixes' => ['0831', '0832', '0833', '0838'],
            'color' => '#9B26B6',
            'brand' => 'AXIS',
            'company' => 'PT XL Axiata Tbk',
            'website' => 'https://axis.co.id'
        ],
        'Tri (3) Hutchison' => [
            'prefixes' => ['0895', '0896', '0897', '0898', '0899'],
            'color' => '#000000',
            'brand' => 'Tri (3 Indonesia)',
            'company' => 'PT Indosat Tbk (IOH)',
            'website' => 'https://tri.co.id'
        ],
        'Smartfren' => [
            'prefixes' => ['0881', '0882', '0883', '0884', '0885', '0886', '0887', '0888', '0889'],
            'color' => '#E6007E',
            'brand' => 'Smartfren (4G LTE / eSIM)',
            'company' => 'PT Smartfren Telecom Tbk',
            'website' => 'https://www.smartfren.com'
        ],
        'Net1 / Sampoerna' => [
            'prefixes' => ['0828'],
            'color' => '#00A859',
            'brand' => 'Net1 Indonesia (STI)',
            'company' => 'PT Sampoerna Telekomunikasi Indonesia',
            'website' => '-'
        ]
    ];

    /**
     * Database HLR (Home Location Register) Distribusi Wilayah & Koordinat Nomor Seluler
     */
    protected array $hlrDatabase = [
        // Telkomsel 0852 series
        '085249' => ['city' => 'Banjarmasin / Samarinda', 'province' => 'Kalimantan Selatan & Timur', 'lat' => -3.316694, 'lon' => 114.590111, 'area_name' => 'Regional Kalimantan (Area 4)'],
        '085250' => ['city' => 'Balikpapan', 'province' => 'Kalimantan Timur', 'lat' => -1.2379, 'lon' => 116.8529, 'area_name' => 'Regional Kalimantan Timur'],
        '085251' => ['city' => 'Palangka Raya', 'province' => 'Kalimantan Tengah', 'lat' => -2.2161, 'lon' => 113.9165, 'area_name' => 'Regional Kalimantan Tengah'],
        '085252' => ['city' => 'Pontianak', 'province' => 'Kalimantan Barat', 'lat' => -0.0263, 'lon' => 109.3425, 'area_name' => 'Regional Kalimantan Barat'],
        '08524'  => ['city' => 'Banjarmasin & Sekitarnya', 'province' => 'Kalimantan', 'lat' => -3.3167, 'lon' => 114.5901, 'area_name' => 'Regional Kalimantan'],
        '08521'  => ['city' => 'Jakarta & Jabodetabek', 'province' => 'DKI Jakarta', 'lat' => -6.2088, 'lon' => 106.8456, 'area_name' => 'Regional Jabodetabek'],
        '08522'  => ['city' => 'Bandung & Jawa Barat', 'province' => 'Jawa Barat', 'lat' => -6.9175, 'lon' => 107.6191, 'area_name' => 'Regional Jawa Barat'],
        '08523'  => ['city' => 'Surabaya & Jawa Timur', 'province' => 'Jawa Timur', 'lat' => -7.2575, 'lon' => 112.7521, 'area_name' => 'Regional Jawa Timur'],
        '08526'  => ['city' => 'Medan / Palembang', 'province' => 'Sumatera', 'lat' => 3.5952, 'lon' => 98.6722, 'area_name' => 'Regional Sumatera'],
        '08527'  => ['city' => 'Padang / Pekanbaru', 'province' => 'Sumatera Barat & Riau', 'lat' => -0.9471, 'lon' => 100.4172, 'area_name' => 'Regional Sumatera Tengah'],
        '08528'  => ['city' => 'Bandar Lampung', 'province' => 'Lampung', 'lat' => -5.4500, 'lon' => 105.2667, 'area_name' => 'Regional Lampung'],
        '08529'  => ['city' => 'Semarang / Yogyakarta', 'province' => 'Jawa Tengah & DIY', 'lat' => -7.7956, 'lon' => 110.3695, 'area_name' => 'Regional Jateng & DIY'],

        // Telkomsel 0812 series
        '081210' => ['city' => 'Jakarta Pusat / Selatan', 'province' => 'DKI Jakarta', 'lat' => -6.2088, 'lon' => 106.8456, 'area_name' => 'Jabodetabek Inner Ring'],
        '081211' => ['city' => 'Jakarta Timur / Bekasi', 'province' => 'Jawa Barat & DKI', 'lat' => -6.2383, 'lon' => 106.9756, 'area_name' => 'Jabodetabek Timur'],
        '081212' => ['city' => 'Jakarta Barat / Tangerang', 'province' => 'Banten & DKI', 'lat' => -6.1783, 'lon' => 106.6319, 'area_name' => 'Jabodetabek Barat'],
        '08122'  => ['city' => 'Bandung, Cirebon, Tasik', 'province' => 'Jawa Barat', 'lat' => -6.9175, 'lon' => 107.6191, 'area_name' => 'Regional Jawa Barat'],
        '08123'  => ['city' => 'Surabaya, Malang, Kediri', 'province' => 'Jawa Timur', 'lat' => -7.2575, 'lon' => 112.7521, 'area_name' => 'Regional Jawa Timur'],
        '08124'  => ['city' => 'Makassar / Manado / Jayapura', 'province' => 'Sulawesi & Papua', 'lat' => -5.1477, 'lon' => 119.4327, 'area_name' => 'Regional Pamasuka (Papua, Maluku, Sulawesi, Kalimantan)'],
        '08125'  => ['city' => 'Balikpapan / Pontianak', 'province' => 'Kalimantan', 'lat' => -1.2379, 'lon' => 116.8529, 'area_name' => 'Regional Kalimantan'],
        '08126'  => ['city' => 'Medan, Banda Aceh', 'province' => 'Sumatera Utara & Aceh', 'lat' => 3.5952, 'lon' => 98.6722, 'area_name' => 'Regional Sumbagut'],
        '08127'  => ['city' => 'Palembang, Jambi, Lampung', 'province' => 'Sumatera Selatan', 'lat' => -2.9909, 'lon' => 104.7565, 'area_name' => 'Regional Sumbagsel'],
        '08128'  => ['city' => 'Depok, Bogor, Tangerang', 'province' => 'Jabodetabek', 'lat' => -6.4025, 'lon' => 106.7942, 'area_name' => 'Regional Jabodetabek Outer'],
        '08129'  => ['city' => 'DKI Jakarta Raya', 'province' => 'DKI Jakarta', 'lat' => -6.2088, 'lon' => 106.8456, 'area_name' => 'Regional DKI Jakarta'],

        // Telkomsel 0813 series
        '08131'  => ['city' => 'Jabodetabek', 'province' => 'DKI Jakarta & Sekitarnya', 'lat' => -6.2088, 'lon' => 106.8456, 'area_name' => 'Regional Jabodetabek'],
        '08132'  => ['city' => 'Semarang & Jawa Tengah', 'province' => 'Jawa Tengah', 'lat' => -6.9667, 'lon' => 110.4167, 'area_name' => 'Regional Jawa Tengah'],
        '08133'  => ['city' => 'Surabaya & Bali', 'province' => 'Jawa Timur & Bali', 'lat' => -8.6705, 'lon' => 115.2126, 'area_name' => 'Regional Jatim & Bali Nusra'],
        '08134'  => ['city' => 'Balikpapan & Makassar', 'province' => 'Kalimantan & Sulawesi', 'lat' => -5.1477, 'lon' => 119.4327, 'area_name' => 'Regional Indonesia Timur'],
        '08135'  => ['city' => 'Manado & Maluku', 'province' => 'Sulawesi Utara & Maluku', 'lat' => 1.4748, 'lon' => 124.8428, 'area_name' => 'Regional Sulut & Maluku'],
        '08136'  => ['city' => 'Medan, Pekanbaru', 'province' => 'Sumatera Utara & Riau', 'lat' => 3.5952, 'lon' => 98.6722, 'area_name' => 'Regional Sumatera'],
        '08137'  => ['city' => 'Padang, Palembang, Lampung', 'province' => 'Sumatera', 'lat' => -2.9909, 'lon' => 104.7565, 'area_name' => 'Regional Sumbagsel'],
        '08138'  => ['city' => 'Tangerang & Banten', 'province' => 'Banten', 'lat' => -6.1783, 'lon' => 106.6319, 'area_name' => 'Regional Banten'],
        '08139'  => ['city' => 'Yogyakarta & Solo', 'province' => 'DI Yogyakarta & Jateng', 'lat' => -7.7956, 'lon' => 110.3695, 'area_name' => 'Regional DIY-Jateng'],

        // Indosat 0815, 0857, 0856 series
        '0815'   => ['city' => 'Nasional / Pusat Jakarta', 'province' => 'DKI Jakarta & Nasional', 'lat' => -6.2088, 'lon' => 106.8456, 'area_name' => 'Jaringan Indosat Ooredoo Nasional'],
        '0856'   => ['city' => 'Jawa & Sumatera', 'province' => 'Indonesia', 'lat' => -6.9175, 'lon' => 107.6191, 'area_name' => 'IM3 Seluler Nasional'],
        '0857'   => ['city' => 'Jabodetabek & Seluruh Indonesia', 'province' => 'Indonesia', 'lat' => -6.2088, 'lon' => 106.8456, 'area_name' => 'IM3 Ooredoo Nasional'],
        
        // XL & AXIS
        '0817'   => ['city' => 'Jakarta, Bandung, Surabaya', 'province' => 'Jawa & Nasional', 'lat' => -6.2088, 'lon' => 106.8456, 'area_name' => 'XL Axiata Utama'],
        '0818'   => ['city' => 'Jabodetabek & Jawa', 'province' => 'Jawa', 'lat' => -6.2088, 'lon' => 106.8456, 'area_name' => 'XL Axiata Regional Jawa'],
        '0819'   => ['city' => 'Bali, Lombok & Jawa', 'province' => 'Bali Nusra & Jawa', 'lat' => -8.6705, 'lon' => 115.2126, 'area_name' => 'XL Axiata Bali-Nusra & Jawa'],
        '0838'   => ['city' => 'Nasional (AXIS)', 'province' => 'Indonesia', 'lat' => -6.2088, 'lon' => 106.8456, 'area_name' => 'AXIS Seluler Nasional'],
        '0896'   => ['city' => 'Nasional (Tri IOH)', 'province' => 'Indonesia', 'lat' => -6.2088, 'lon' => 106.8456, 'area_name' => 'Tri 3 Seluler Nasional'],
        '0888'   => ['city' => 'Nasional (Smartfren)', 'province' => 'Indonesia', 'lat' => -6.2088, 'lon' => 106.8456, 'area_name' => 'Smartfren 4G LTE Nasional']
    ];

    /**
     * Database Kode Area PSTN Fixed Line di Indonesia dengan Koordinat
     */
    protected array $indonesianPstnCodes = [
        '021'  => ['Jabodetabek (Jakarta, Bogor, Depok, Tangerang, Bekasi)', 'DKI Jakarta & Jawa Barat/Banten', -6.2088, 106.8456],
        '022'  => ['Bandung, Cimahi', 'Jawa Barat', -6.9175, 107.6191],
        '0231' => ['Cirebon', 'Jawa Barat', -6.7320, 108.5523],
        '0232' => ['Kuningan', 'Jawa Barat', -6.9764, 108.4831],
        '0233' => ['Majalengka', 'Jawa Barat', -6.8361, 108.2278],
        '0234' => ['Indramayu', 'Jawa Barat', -6.3264, 108.3200],
        '024'  => ['Semarang', 'Jawa Tengah', -6.9667, 110.4167],
        '0251' => ['Bogor (Kota & Kab)', 'Jawa Barat', -6.5971, 106.8060],
        '0254' => ['Serang, Cilegon', 'Banten', -6.1104, 106.1600],
        '0260' => ['Subang', 'Jawa Barat', -6.5590, 107.7570],
        '0264' => ['Purwakarta', 'Jawa Barat', -6.5569, 107.4433],
        '0265' => ['Tasikmalaya, Ciamis, Banjar', 'Jawa Barat', -7.3274, 108.2207],
        '0266' => ['Sukabumi', 'Jawa Barat', -6.9277, 106.9300],
        '0267' => ['Karawang', 'Jawa Barat', -6.3073, 107.2887],
        '0271' => ['Solo / Surakarta, Sukoharjo', 'Jawa Tengah', -7.5755, 110.8243],
        '0274' => ['Yogyakarta, Sleman, Bantul', 'DI Yogyakarta', -7.7956, 110.3695],
        '0281' => ['Purwokerto, Banyumas', 'Jawa Tengah', -7.4243, 109.2302],
        '0283' => ['Tegal, Brebes', 'Jawa Tengah', -6.8694, 109.1402],
        '0285' => ['Pekalongan, Batang', 'Jawa Tengah', -6.8886, 109.6753],
        '0293' => ['Magelang, Temanggung', 'Jawa Tengah', -7.4706, 110.2178],
        '031'  => ['Surabaya, Sidoarjo, Gresik', 'Jawa Timur', -7.2575, 112.7521],
        '0321' => ['Mojokerto, Jombang', 'Jawa Timur', -7.4726, 112.4381],
        '0341' => ['Malang, Batu', 'Jawa Timur', -7.9797, 112.6304],
        '0351' => ['Madiun, Magetan, Ngawi', 'Jawa Timur', -7.6298, 111.5239],
        '0354' => ['Kediri', 'Jawa Timur', -7.8480, 112.0178],
        '0361' => ['Denpasar, Badung, Gianyar', 'Bali', -8.6705, 115.2126],
        '0370' => ['Mataram, Lombok', 'Nusa Tenggara Barat', -8.5768, 116.0999],
        '0380' => ['Kupang', 'Nusa Tenggara Timur', -10.1772, 123.6070],
        '061'  => ['Medan, Binjai, Deli Serdang', 'Sumatera Utara', 3.5952, 98.6722],
        '0711' => ['Palembang, Banyuasin', 'Sumatera Selatan', -2.9909, 104.7565],
        '0721' => ['Bandar Lampung', 'Lampung', -5.4500, 105.2667],
        '0741' => ['Jambi', 'Jambi', -1.6101, 103.6131],
        '0751' => ['Padang, Pariaman', 'Sumatera Barat', -0.9471, 100.4172],
        '0761' => ['Pekanbaru', 'Riau', 0.5071, 101.4478],
        '0778' => ['Batam', 'Kepulauan Riau', 1.1301, 104.0529],
        '0411' => ['Makassar, Maros, Gowa', 'Sulawesi Selatan', -5.1477, 119.4327],
        '0431' => ['Manado, Tomohon', 'Sulawesi Utara', 1.4748, 124.8428],
        '0451' => ['Palu', 'Sulawesi Tengah', -0.9003, 119.8779],
        '0511' => ['Banjarmasin, Banjarbaru', 'Kalimantan Selatan', -3.3167, 114.5901],
        '0541' => ['Samarinda', 'Kalimantan Timur', -0.5022, 117.1536],
        '0542' => ['Balikpapan', 'Kalimantan Timur', -1.2379, 116.8529],
        '0561' => ['Pontianak, Kubu Raya', 'Kalimantan Barat', -0.0263, 109.3425],
        '0911' => ['Ambon', 'Maluku', -3.6954, 128.1814],
        '0967' => ['Jayapura', 'Papua', -2.5916, 140.6690]
    ];

    /**
     * Database Kode Negara Internasional
     */
    protected array $countryDialCodes = [
        '62' => ['name' => 'Indonesia', 'iso' => 'ID', 'flag' => '🇮🇩', 'lat' => -0.7893, 'lon' => 113.9213],
        '60' => ['name' => 'Malaysia', 'iso' => 'MY', 'flag' => '🇲🇾', 'lat' => 4.2105, 'lon' => 101.9758],
        '65' => ['name' => 'Singapura', 'iso' => 'SG', 'flag' => '🇸🇬', 'lat' => 1.3521, 'lon' => 103.8198],
        '66' => ['name' => 'Thailand', 'iso' => 'TH', 'flag' => '🇹🇭', 'lat' => 15.8700, 'lon' => 100.9925],
        '63' => ['name' => 'Filipina', 'iso' => 'PH', 'flag' => '🇵🇭', 'lat' => 12.8797, 'lon' => 121.7740],
        '84' => ['name' => 'Vietnam', 'iso' => 'VN', 'flag' => '🇻🇳', 'lat' => 14.0583, 'lon' => 108.2772],
        '1'  => ['name' => 'Amerika Serikat / Kanada', 'iso' => 'US', 'flag' => '🇺🇸', 'lat' => 37.0902, 'lon' => -95.7129],
        '44' => ['name' => 'Inggris Raya (UK)', 'iso' => 'GB', 'flag' => '🇬🇧', 'lat' => 55.3781, 'lon' => -3.4360],
        '61' => ['name' => 'Australia', 'iso' => 'AU', 'flag' => '🇦🇺', 'lat' => -25.2744, 'lon' => 133.7751],
        '81' => ['name' => 'Jepang', 'iso' => 'JP', 'flag' => '🇯🇵', 'lat' => 36.2048, 'lon' => 138.2529],
        '82' => ['name' => 'Korea Selatan', 'iso' => 'KR', 'flag' => '🇰🇷', 'lat' => 35.9078, 'lon' => 127.7669],
        '86' => ['name' => 'China', 'iso' => 'CN', 'flag' => '🇨🇳', 'lat' => 35.8617, 'lon' => 104.1954],
        '91' => ['name' => 'India', 'iso' => 'IN', 'flag' => '🇮🇳', 'lat' => 20.5937, 'lon' => 78.9629],
        '966'=> ['name' => 'Arab Saudi', 'iso' => 'SA', 'flag' => '🇸🇦', 'lat' => 23.8859, 'lon' => 45.0792],
        '971'=> ['name' => 'Uni Emirat Arab', 'iso' => 'AE', 'flag' => '🇦🇪', 'lat' => 23.4241, 'lon' => 53.8478],
        '49' => ['name' => 'Jerman', 'iso' => 'DE', 'flag' => '🇩🇪', 'lat' => 51.1657, 'lon' => 10.4515],
        '33' => ['name' => 'Prancis', 'iso' => 'FR', 'flag' => '🇫🇷', 'lat' => 46.2276, 'lon' => 2.2137],
        '7'  => ['name' => 'Rusia', 'iso' => 'RU', 'flag' => '🇷🇺', 'lat' => 61.5240, 'lon' => 105.3188]
    ];

    /**
     * Lacak dan analisis nomor telepon dengan estimasi lokasi & WhatsApp toolkit
     */
    public function track(string $rawPhone): array
    {
        $cleanPhone = preg_replace('/[^0-9+]/', '', trim($rawPhone));

        if (empty($cleanPhone)) {
            return [
                'status' => 'error',
                'message' => 'Harap masukkan nomor telepon yang valid (contoh: 08123456789 atau +6281234567890).'
            ];
        }

        // 1. Deteksi Negara Asal
        $countryInfo = $this->detectCountry($cleanPhone);
        $countryCode = $countryInfo['code'];
        $countryName = $countryInfo['name'];
        $countryFlag = $countryInfo['flag'];
        $countryIso  = $countryInfo['iso'];
        $defaultLat  = $countryInfo['lat'] ?? -0.7893;
        $defaultLon  = $countryInfo['lon'] ?? 113.9213;

        // Normalisasi nomor
        $normalizedData = $this->normalizeNumber($cleanPhone, $countryCode);
        $e164 = $normalizedData['e164'];
        $local = $normalizedData['local'];
        $waNumber = ltrim($e164, '+');
        $waLink = "https://wa.me/{$waNumber}";
        $waShareLocLink = "https://wa.me/{$waNumber}?text=" . urlencode("Halo, mohon bagikan titik lokasi terkini Anda (Share Live Location via WhatsApp) untuk verifikasi alamat.");

        // 2. Coba provider Phone Intelligence terlebih dahulu.
        // Provider hanya mengembalikan data nomor/carrier/wilayah; bukan GPS perangkat.
        $external = $this->lookupExternalPhone($e164, $countryIso);
        if ($external !== null) {
            $external['whatsapp_number'] = $waNumber;
            $external['whatsapp_link'] = $waLink;
            $external['whatsapp_request_loc_link'] = $waShareLocLink;
            $external['tracked_at'] = now()->translatedFormat('d F Y, H:i:s T');
            return $external;
        }

        // 3. Jika Nomor Indonesia (+62 / 0xx)
        if ($countryCode === '62' || str_starts_with($cleanPhone, '08') || str_starts_with($cleanPhone, '02') || str_starts_with($cleanPhone, '03') || str_starts_with($cleanPhone, '04') || str_starts_with($cleanPhone, '05') || str_starts_with($cleanPhone, '06') || str_starts_with($cleanPhone, '07') || str_starts_with($cleanPhone, '09')) {
            $analysis = $this->analyzeIndonesianNumber($local, $e164);
            $analysis['whatsapp_number'] = $waNumber;
            $analysis['whatsapp_link'] = $waLink;
            $analysis['whatsapp_request_loc_link'] = $waShareLocLink;
            return $analysis;
        }

        // 3. Jika Nomor Internasional selain Indonesia
        return [
            'status' => 'success',
            'query' => $rawPhone,
            'clean_number' => $cleanPhone,
            'e164_format' => $e164,
            'local_format' => $local,
            'rfc3966_format' => 'tel:' . $e164,
            'whatsapp_number' => $waNumber,
            'whatsapp_link' => $waLink,
            'whatsapp_request_loc_link' => $waShareLocLink,
            'is_valid' => strlen($cleanPhone) >= 7 && strlen($cleanPhone) <= 15,
            'country' => $countryName,
            'country_code' => '+' . $countryCode,
            'country_flag' => $countryFlag,
            'country_iso' => $countryIso,
            'carrier' => 'Operator Internasional (' . $countryName . ')',
            'carrier_brand' => 'Operator Seluler Luar Negeri',
            'carrier_color' => '#4A5568',
            'line_type' => 'Mobile / Landline (Internasional)',
            'location' => $countryName,
            'region' => 'Luar Negeri',
            'address_estimate' => "Perkiraan Wilayah: {$countryName} (Kode Negara: +{$countryCode})",
            'latitude' => $defaultLat,
            'longitude' => $defaultLon,
            'coordinates_str' => "{$defaultLat}, {$defaultLon}",
            'location_source' => 'Kode negara (estimasi)',
            'location_accuracy' => 'Negara saja; bukan GPS aktual',
            'gps_actual' => false,
            'map_is_estimate' => true,
            'tracked_at' => now()->translatedFormat('d F Y, H:i:s T')
        ];
    }

    /**
     * Analisis mendalam khusus Nomor Indonesia dengan Peta & Alamat HLR
     */
    protected function analyzeIndonesianNumber(string $localNumber, string $e164Number): array
    {
        if (!str_starts_with($localNumber, '0')) {
            $localNumber = '0' . $localNumber;
        }

        $length = strlen($localNumber);
        $isValidLength = ($length >= 9 && $length <= 14);

        // A. Cek Seluler (dimulai dengan 08)
        if (str_starts_with($localNumber, '08')) {
            $prefix4 = substr($localNumber, 0, 4);
            $carrierInfo = $this->findIndonesianCarrier($prefix4);
            $hlrInfo = $this->findHlrLocation($localNumber);

            $city = $hlrInfo['city'] ?? 'Indonesia';
            $province = $hlrInfo['province'] ?? 'Nasional';
            $areaName = $hlrInfo['area_name'] ?? 'Jangkauan Nasional';
            $lat = $hlrInfo['lat'] ?? -6.2088;
            $lon = $hlrInfo['lon'] ?? 106.8456;

            $addressEstimate = "Area Registrasi HLR Awal: {$city}, {$province} ({$areaName})";

            return [
                'status' => 'success',
                'query' => $localNumber,
                'clean_number' => $localNumber,
                'e164_format' => $e164Number,
                'local_format' => $this->formatIndonesianLocal($localNumber),
                'rfc3966_format' => 'tel:' . $e164Number,
                'is_valid' => $isValidLength && ($carrierInfo !== null),
                'country' => 'Indonesia',
                'country_code' => '+62',
                'country_flag' => '🇮🇩',
                'country_iso' => 'ID',
                'carrier' => $carrierInfo['carrier'] ?? 'Operator Tidak Dikenal',
                'carrier_brand' => $carrierInfo['brand'] ?? 'Nomor Seluler Indonesia',
                'carrier_company' => $carrierInfo['company'] ?? '-',
                'carrier_color' => $carrierInfo['color'] ?? '#6B7280',
                'carrier_website' => $carrierInfo['website'] ?? '-',
                'line_type' => 'Mobile (Seluler & WhatsApp)',
                'location' => "{$city}, {$province}",
                'region' => $areaName,
                'address_estimate' => $addressEstimate,
                'latitude' => $lat,
                'longitude' => $lon,
                'coordinates_str' => "{$lat}, {$lon}",
                'location_source' => 'HLR/prefix database (estimasi)',
                'location_accuracy' => 'Wilayah registrasi; bukan GPS aktual',
                'gps_actual' => false,
                'map_is_estimate' => true,
                'number_length' => $length . ' digit (Standar: 10-13 digit)',
                'tracked_at' => now()->translatedFormat('d F Y, H:i:s T')
            ];
        }

        // B. Cek Telepon Rumah / Kantor (PSTN Fixed Line)
        $pstnMatch = $this->findIndonesianPstnArea($localNumber);
        if ($pstnMatch) {
            $city = $pstnMatch['city'];
            $province = $pstnMatch['province'];
            $lat = $pstnMatch['lat'];
            $lon = $pstnMatch['lon'];

            return [
                'status' => 'success',
                'query' => $localNumber,
                'clean_number' => $localNumber,
                'e164_format' => $e164Number,
                'local_format' => $localNumber,
                'rfc3966_format' => 'tel:' . $e164Number,
                'is_valid' => $isValidLength,
                'country' => 'Indonesia',
                'country_code' => '+62',
                'country_flag' => '🇮🇩',
                'country_iso' => 'ID',
                'carrier' => 'PT Telkom Indonesia (PSTN / IndiHome)',
                'carrier_brand' => 'Telepon Rumah / Kantor (Fixed Line)',
                'carrier_company' => 'PT Telekomunikasi Indonesia Tbk',
                'carrier_color' => '#DE1B1B',
                'carrier_website' => 'https://www.telkom.co.id',
                'line_type' => 'Fixed Line (PSTN / Telepon Kabel)',
                'location' => $city,
                'region' => $province,
                'address_estimate' => "Sentral Telepon Otomat (STO) Wilayah: {$city}, {$province}",
                'latitude' => $lat,
                'longitude' => $lon,
                'coordinates_str' => "{$lat}, {$lon}",
                'location_source' => 'Kode area PSTN (estimasi)',
                'location_accuracy' => 'Wilayah sentral telepon; bukan GPS aktual',
                'gps_actual' => false,
                'map_is_estimate' => true,
                'number_length' => $length . ' digit',
                'tracked_at' => now()->translatedFormat('d F Y, H:i:s T')
            ];
        }

        // C. Nomor Layanan Khusus
        return [
            'status' => 'success',
            'query' => $localNumber,
            'clean_number' => $localNumber,
            'e164_format' => $e164Number,
            'local_format' => $localNumber,
            'rfc3966_format' => 'tel:' . $e164Number,
            'is_valid' => $isValidLength,
            'country' => 'Indonesia',
            'country_code' => '+62',
            'country_flag' => '🇮🇩',
            'country_iso' => 'ID',
            'carrier' => 'Layanan Khusus / Format Bebas Pulsa',
            'carrier_brand' => 'Hotline Khusus',
            'carrier_color' => '#10B981',
            'line_type' => 'Special Service',
            'location' => 'Indonesia',
            'region' => 'Nasional',
            'address_estimate' => 'Jangkauan Layanan Nasional Indonesia',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'coordinates_str' => '-6.2088, 106.8456',
            'location_source' => 'Wilayah nasional (fallback)',
            'location_accuracy' => 'Sangat umum; bukan GPS aktual',
            'gps_actual' => false,
            'map_is_estimate' => true,
            'tracked_at' => now()->translatedFormat('d F Y, H:i:s T')
        ];
    }

    /**
     * Cari Lokasi HLR berdasarkan 6 digit, 5 digit, atau 4 digit nomor
     */
    protected function findHlrLocation(string $number): array
    {
        $prefix6 = substr($number, 0, 6);
        if (isset($this->hlrDatabase[$prefix6])) {
            return $this->hlrDatabase[$prefix6];
        }

        $prefix5 = substr($number, 0, 5);
        if (isset($this->hlrDatabase[$prefix5])) {
            return $this->hlrDatabase[$prefix5];
        }

        $prefix4 = substr($number, 0, 4);
        if (isset($this->hlrDatabase[$prefix4])) {
            return $this->hlrDatabase[$prefix4];
        }

        // Fallback default pusat Indonesia (Jakarta)
        return [
            'city' => 'Kota Regional Indonesia',
            'province' => 'Nasional',
            'area_name' => 'Jangkauan Seluler Seluruh Indonesia',
            'lat' => -6.2088,
            'lon' => 106.8456
        ];
    }

    /**
     * Cari Operator berdasarkan prefix 4 digit
     */
    protected function findIndonesianCarrier(string $prefix4): ?array
    {
        foreach ($this->indonesianCarriers as $name => $data) {
            if (in_array($prefix4, $data['prefixes'])) {
                return array_merge(['carrier' => $name], $data);
            }
        }
        return null;
    }

    /**
     * Cari Kode Area PSTN
     */
    protected function findIndonesianPstnArea(string $number): ?array
    {
        $prefix4 = substr($number, 0, 4);
        if (isset($this->indonesianPstnCodes[$prefix4])) {
            $data = $this->indonesianPstnCodes[$prefix4];
            return [
                'city' => $data[0],
                'province' => $data[1],
                'lat' => $data[2] ?? -6.2088,
                'lon' => $data[3] ?? 106.8456
            ];
        }

        $prefix3 = substr($number, 0, 3);
        if (isset($this->indonesianPstnCodes[$prefix3])) {
            $data = $this->indonesianPstnCodes[$prefix3];
            return [
                'city' => $data[0],
                'province' => $data[1],
                'lat' => $data[2] ?? -6.2088,
                'lon' => $data[3] ?? 106.8456
            ];
        }

        return null;
    }

    /**
     * Phone Intelligence API server-side.
     *
     * Provider yang didukung saat ini: IPQualityScore (IPQS).
     * API key wajib disimpan di .env dan tidak pernah dikirim ke browser.
     * Hasil lokasi adalah region/city yang diasosiasikan dengan nomor,
     * bukan koordinat GPS perangkat.
     */
    protected function lookupExternalPhone(string $e164, string $countryIso): ?array
    {
        $provider = strtolower((string) config('tracker.phone_provider', 'ipqs'));
        $apiKey = trim((string) config('tracker.phone_api_key', ''));

        if ($provider === 'none' || $apiKey === '') {
            return null;
        }

        try {
            if ($provider !== 'ipqs') {
                return null;
            }

            $baseUrl = rtrim((string) config('tracker.phone_api_url', 'https://www.ipqualityscore.com/api/json/phone'), '/');
            $response = Http::timeout((int) config('tracker.phone_timeout', 8))
                ->acceptJson()
                ->withHeaders(['IPQS-KEY' => $apiKey])
                ->get($baseUrl, [
                    'phone' => $e164,
                    'country' => $countryIso,
                    'strictness' => (int) config('tracker.phone_strictness', 0),
                ]);

            if (!$response->successful()) {
                return null;
            }

            $payload = $response->json();
            if (!is_array($payload) || ($payload['success'] ?? false) !== true) {
                return null;
            }

            $country = strtoupper((string) ($payload['country'] ?? $countryIso));
            $region = trim((string) ($payload['region'] ?? ''));
            $city = trim((string) ($payload['city'] ?? ''));
            $carrier = trim((string) ($payload['carrier'] ?? ''));
            $lineType = trim((string) ($payload['line_type'] ?? ''));
            $valid = (bool) ($payload['valid'] ?? false);

            $countryName = $this->countryNameFromIso($country) ?: ($this->countryDialCodes[$this->detectCountry($e164)['code']]['name'] ?? $country);
            $locationParts = array_values(array_filter([$city, $region, $countryName], fn($v) => $v !== '' && strtoupper($v) !== 'N/A'));
            $location = implode(', ', array_unique($locationParts));

            $coords = $this->geocodePhoneArea($city, $region, $countryName);
            if ($coords === null) {
                $countryData = $this->countryDataFromIso($country);
                $coords = [
                    'lat' => $countryData['lat'] ?? -0.7893,
                    'lon' => $countryData['lon'] ?? 113.9213,
                    'source' => 'country fallback',
                ];
            }

            $isIndonesia = $country === 'ID';
            $carrierInfo = $isIndonesia ? $this->findIndonesianCarrier(substr(preg_replace('/[^0-9]/', '', $e164), 0, 4) === '0062' ? '0000' : substr($this->normalizeNumber($e164, '62')['local'], 0, 4)) : null;

            return [
                'status' => 'success',
                'query' => $e164,
                'clean_number' => preg_replace('/[^0-9+]/', '', $e164),
                'e164_format' => $payload['formatted'] ?? $e164,
                'local_format' => $payload['local_format'] ?? $e164,
                'rfc3966_format' => 'tel:' . $e164,
                'is_valid' => $valid,
                'country' => $countryName,
                'country_code' => '+' . ($payload['dialing_code'] ?? $this->detectCountry($e164)['code']),
                'country_flag' => $this->flagForIso($country),
                'country_iso' => $country,
                'carrier' => ($carrier !== '' && strtoupper($carrier) !== 'N/A') ? $carrier : ($carrierInfo['carrier'] ?? 'Operator Tidak Dikenal'),
                'carrier_brand' => $carrierInfo['brand'] ?? 'Phone Intelligence Provider',
                'carrier_company' => $carrierInfo['company'] ?? '-',
                'carrier_color' => $carrierInfo['color'] ?? '#10B981',
                'carrier_website' => $carrierInfo['website'] ?? '-',
                'line_type' => ($lineType !== '' && strtoupper($lineType) !== 'N/A') ? $lineType : 'Unknown',
                'location' => $location !== '' ? $location : $countryName,
                'region' => $region !== '' && strtoupper($region) !== 'N/A' ? $region : 'Tidak tersedia',
                'address_estimate' => $location !== ''
                    ? "Lokasi terasosiasi nomor: {$location}"
                    : "Lokasi terasosiasi nomor: {$countryName}",
                'latitude' => $coords['lat'],
                'longitude' => $coords['lon'],
                'coordinates_str' => sprintf('%s, %s', $coords['lat'], $coords['lon']),
                'location_source' => 'IPQS Phone Intelligence + geocoding wilayah',
                'location_accuracy' => 'Perkiraan city/region terkait nomor; bukan GPS aktual',
                'gps_actual' => false,
                'map_is_estimate' => true,
                'phone_api_provider' => 'IPQualityScore',
                'phone_api_active' => $payload['active'] ?? null,
                'phone_api_prepaid' => $payload['prepaid'] ?? null,
                'phone_api_voip' => $payload['VOIP'] ?? null,
                'phone_api_timezone' => $payload['timezone'] ?? null,
                'phone_api_request_id' => $payload['request_id'] ?? null,
            ];
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    protected function geocodePhoneArea(string $city, string $region, string $country): ?array
    {
        $city = trim($city);
        $region = trim($region);
        $country = trim($country);
        if ($city === '' || strtoupper($city) === 'N/A') {
            return null;
        }

        $query = implode(', ', array_filter([$city, $region, $country], fn($v) => $v !== '' && strtoupper($v) !== 'N/A'));
        $cacheKey = 'phone-geocode:' . sha1(strtolower($query));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($query) {
            try {
                $response = Http::timeout(6)
                    ->withHeaders([
                        'User-Agent' => config('tracker.geocoder_user_agent', 'GeoTrack-Pro/1.0 contact-admin@example.com'),
                        'Accept-Language' => 'id,en',
                    ])
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $query,
                        'format' => 'jsonv2',
                        'limit' => 1,
                    ]);

                if (!$response->successful()) {
                    return null;
                }

                $item = $response->json()[0] ?? null;
                if (!$item || !isset($item['lat'], $item['lon'])) {
                    return null;
                }

                return [
                    'lat' => (float) $item['lat'],
                    'lon' => (float) $item['lon'],
                    'source' => 'OpenStreetMap Nominatim',
                ];
            } catch (\Throwable $e) {
                report($e);
                return null;
            }
        });
    }

    protected function countryNameFromIso(string $iso): ?string
    {
        foreach ($this->countryDialCodes as $data) {
            if (($data['iso'] ?? '') === $iso) {
                return $data['name'];
            }
        }
        return null;
    }

    protected function countryDataFromIso(string $iso): ?array
    {
        foreach ($this->countryDialCodes as $data) {
            if (($data['iso'] ?? '') === $iso) {
                return $data;
            }
        }
        return null;
    }

    protected function flagForIso(string $iso): string
    {
        $data = $this->countryDataFromIso($iso);
        return $data['flag'] ?? '🌐';
    }

    /**
     * Deteksi Negara berdasarkan dial code awalan
     */
    protected function detectCountry(string $number): array
    {
        $clean = ltrim($number, '+');

        $codes = $this->countryDialCodes;
        uksort($codes, fn($a, $b) => strlen($b) <=> strlen($a));

        foreach ($codes as $code => $info) {
            if (str_starts_with($clean, (string)$code)) {
                return array_merge(['code' => (string)$code], $info);
            }
        }

        return [
            'code' => '62',
            'name' => 'Indonesia',
            'iso' => 'ID',
            'flag' => '🇮🇩',
            'lat' => -0.7893,
            'lon' => 113.9213
        ];
    }

    /**
     * Normalisasi nomor ke format E.164 dan format lokal
     */
    protected function normalizeNumber(string $rawNumber, string $countryCode): array
    {
        $clean = preg_replace('/[^0-9]/', '', $rawNumber);

        if (str_starts_with($clean, $countryCode)) {
            $e164 = '+' . $clean;
            $local = '0' . substr($clean, strlen($countryCode));
        } elseif (str_starts_with($clean, '0')) {
            $e164 = '+' . $countryCode . substr($clean, 1);
            $local = $clean;
        } else {
            $e164 = '+' . $countryCode . $clean;
            $local = '0' . $clean;
        }

        return [
            'e164' => $e164,
            'local' => $local,
            'national_prefix' => substr($local, 0, 4)
        ];
    }

    /**
     * Format tampilan nomor seluler lokal: 0812-3456-7890
     */
    protected function formatIndonesianLocal(string $number): string
    {
        if (strlen($number) >= 10 && strlen($number) <= 13) {
            return substr($number, 0, 4) . '-' . substr($number, 4, 4) . '-' . substr($number, 8);
        }
        return $number;
    }
}
