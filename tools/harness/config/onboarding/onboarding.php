<?php

declare(strict_types=1);

return [
    'enabled' => env('ONBOARDING_ENABLED', true),
    'redirect' => env('ONBOARDING_REDIRECT', '/'),
    'role' => env('ONBOARDING_ROLE', 'owner'),
    'permissions' => [
        'app.manage',
        'billing.manage',
        'users.manage',
        'settings.manage',
        'docs.manage',
    ],
];
