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

    /*
    | Phone Intelligence API
    | API key hanya berada di server (.env), tidak pernah di frontend.
    */
    'phone_provider' => env('PHONE_LOOKUP_PROVIDER', 'ipqs'),
    'phone_api_key' => env('PHONE_LOOKUP_API_KEY', ''),
    'phone_api_url' => env('PHONE_LOOKUP_API_URL', 'https://www.ipqualityscore.com/api/json/phone'),
    'phone_timeout' => env('PHONE_LOOKUP_TIMEOUT', 8),
    'phone_strictness' => env('PHONE_LOOKUP_STRICTNESS', 0),
    'geocoder_user_agent' => env('GEOCODER_USER_AGENT', 'GeoTrack-Pro/1.0 contact-admin@example.com'),
];
