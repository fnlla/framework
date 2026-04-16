<?php

declare(strict_types=1);

return [
    'brand' => env('ADMIN_BRAND', 'Admin'),
    'nav' => [
        [
            'label' => 'Dashboard',
            'url' => '/admin',
            'icon' => 'home',
            'can' => 'permission:admin.view',
        ],
        [
            'label' => 'Users',
            'url' => '/admin/users',
            'icon' => 'users',
            'can' => 'permission:users.view',
        ],
        [
            'label' => 'Settings',
            'url' => '/admin/settings',
            'icon' => 'settings',
            'can' => 'permission:settings.view',
        ],
        [
            'label' => 'Analytics',
            'url' => '/admin/analytics',
            'icon' => 'chart',
            'can' => 'permission:analytics.view',
        ],
        [
            'label' => 'Audit',
            'url' => '/admin/audit',
            'icon' => 'shield',
            'can' => 'permission:audit.view',
        ],
    ],
];
