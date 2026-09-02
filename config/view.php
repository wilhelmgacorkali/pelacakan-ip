<?php

return [
    'paths' => [
        resource_path('views'),
    ],
    'compiled' => env(
        'VIEW_COMPILED_PATH',
        (DIRECTORY_SEPARATOR === '/' && is_dir('/tmp')) ? '/tmp/storage/framework/views' : storage_path('framework/views')
    ),
];

