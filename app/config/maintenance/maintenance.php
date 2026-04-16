<?php

declare(strict_types=1);

return [
    'enabled' => env('MAINTENANCE_MODE', false),
    'secret' => env('MAINTENANCE_SECRET', ''),
    'allowed_ips' => array_filter(array_map('trim', explode(',', (string) env('MAINTENANCE_ALLOWED_IPS', '')))),
    'retry_after' => (int) env('MAINTENANCE_RETRY_AFTER', 60),
];
