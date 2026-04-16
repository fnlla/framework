<?php

declare(strict_types=1);

return [
    'enabled' => env('REDIRECTS_ENABLED', true),
    'force_https' => env('REDIRECTS_FORCE_HTTPS', env('APP_ENV') === 'prod'),
    'trailing_slash' => env('REDIRECTS_TRAILING_SLASH', 'remove'), // add|remove|ignore
    'rules' => [
        // '/old' => '/new',
        // '/legacy' => ['to' => '/services', 'code' => 301],
    ],
];
