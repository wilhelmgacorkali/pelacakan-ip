<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => '/tmp/storage/app',
            'throw' => false,
        ],
        'public' => [
            'driver' => 'local',
            'root' => '/tmp/storage/app/public',
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
    ],
    'links' => [],
];
