<?php

declare(strict_types=1);

return [
    'enabled' => env('STRIPE_ENABLED', false),
    'secret' => env('STRIPE_SECRET', ''),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
    'api_version' => env('STRIPE_API_VERSION', ''),
    'timeout' => env('STRIPE_TIMEOUT', 30),
];
