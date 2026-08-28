<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IP Tracker Settings
    |--------------------------------------------------------------------------
    | Konfigurasi provider geolocation untuk pelacak IP
    */
    'ip_provider' => env('IP_TRACKER_PROVIDER', 'ip-api'),
    'ip_timeout' => env('IP_TRACKER_TIMEOUT', 6),

    /*
    |--------------------------------------------------------------------------
    | Phone Tracker Settings
    |--------------------------------------------------------------------------
    | Konfigurasi provider telekomunikasi & default country
    */
    'default_country_code' => env('PHONE_DEFAULT_COUNTRY', '62'),
    'default_country_name' => 'Indonesia',
];
