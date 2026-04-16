<?php

declare(strict_types=1);

return [
    'providers' => [
        'google' => [
            'client_id' => env('OAUTH_GOOGLE_CLIENT_ID', ''),
            'client_secret' => env('OAUTH_GOOGLE_CLIENT_SECRET', ''),
            'redirect_uri' => env('OAUTH_GOOGLE_REDIRECT_URI', ''),
            'authorize_url' => env('OAUTH_GOOGLE_AUTHORIZE_URL', 'https://accounts.google.com/o/oauth2/v2/auth'),
            'token_url' => env('OAUTH_GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token'),
            'resource_url' => env('OAUTH_GOOGLE_RESOURCE_URL', 'https://openidconnect.googleapis.com/v1/userinfo'),
        ],
        'github' => [
            'client_id' => env('OAUTH_GITHUB_CLIENT_ID', ''),
            'client_secret' => env('OAUTH_GITHUB_CLIENT_SECRET', ''),
            'redirect_uri' => env('OAUTH_GITHUB_REDIRECT_URI', ''),
            'authorize_url' => env('OAUTH_GITHUB_AUTHORIZE_URL', 'https://github.com/login/oauth/authorize'),
            'token_url' => env('OAUTH_GITHUB_TOKEN_URL', 'https://github.com/login/oauth/access_token'),
            'resource_url' => env('OAUTH_GITHUB_RESOURCE_URL', 'https://api.github.com/user'),
        ],
    ],
];
