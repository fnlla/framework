<?php

declare(strict_types=1);

return [
    'driver' => env('SEARCH_DRIVER', 'null'),
    'driver_class' => env('SEARCH_DRIVER_CLASS', ''),
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),
        'key' => env('MEILISEARCH_KEY', ''),
    ],
];
