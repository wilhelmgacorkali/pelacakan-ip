<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ngrok Tunnel Settings
    |--------------------------------------------------------------------------
    | Dipakai oleh `php artisan tracker:tunnel` untuk mengekspos server lokal
    | ke internet supaya link Device Agent bisa dibuka dari perangkat lain.
    */

    // Path/nama binary ngrok. Default 'ngrok' (harus ada di PATH).
    'binary' => env('NGROK_BINARY', 'ngrok'),

    // Authtoken dari dashboard ngrok (https://dashboard.ngrok.com/get-started/your-authtoken)
    'authtoken' => env('NGROK_AUTHTOKEN'),

    // Opsional: custom/reserved domain ngrok (mis. akun berbayar), contoh: myapp.ngrok-free.app
    'domain' => env('NGROK_DOMAIN'),
];
