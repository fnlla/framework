<?php

declare(strict_types=1);

return [
    'default' => env('STORAGE_DISK', 'local'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => env('STORAGE_LOCAL_ROOT', 'storage/uploads'),
            'url' => env('STORAGE_LOCAL_URL', '/uploads'),
        ],
        's3' => [
            'driver' => 's3',
            'bucket' => env('STORAGE_S3_BUCKET', ''),
            'region' => env('STORAGE_S3_REGION', 'eu-west-1'),
            'key' => env('STORAGE_S3_KEY', ''),
            'secret' => env('STORAGE_S3_SECRET', ''),
            'endpoint' => env('STORAGE_S3_ENDPOINT', ''),
            'prefix' => env('STORAGE_S3_PREFIX', ''),
            'public_url' => env('STORAGE_S3_PUBLIC_URL', ''),
            'use_path_style' => env('STORAGE_S3_USE_PATH_STYLE', true),
        ],
    ],
];
