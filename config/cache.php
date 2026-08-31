<?php

return [
    'default' => env('CACHE_STORE', 'array'),
    'stores' => [
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        'file' => [
            'driver' => 'file',
            'path' => '/tmp/cache',
            'lock_path' => '/tmp/cache',
        ],
    ],
    'prefix' => env('CACHE_PREFIX', 'geotrack_cache'),
];
