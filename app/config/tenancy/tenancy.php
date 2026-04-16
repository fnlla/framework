<?php

declare(strict_types=1);

return [
    'enabled' => env('TENANCY_ENABLED', false),
    'required' => env('TENANCY_REQUIRED', false),
    'resolver' => env('TENANCY_RESOLVER', 'header'),
    'resolver_class' => env('TENANCY_RESOLVER_CLASS', ''),
    'header' => env('TENANCY_HEADER', 'X-Tenant-Id'),
    'attribute' => env('TENANCY_ATTRIBUTE', 'tenant_id'),
    'required_status' => (int) env('TENANCY_REQUIRED_STATUS', 400),
    'required_message' => env('TENANCY_REQUIRED_MESSAGE', 'Tenant identifier required.'),
    'host' => [
        'base_domain' => env('TENANCY_BASE_DOMAIN', ''),
        'map' => [],
    ],
    'path' => [
        'segment' => (int) env('TENANCY_PATH_SEGMENT', 1),
    ],
];
