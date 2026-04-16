<?php

declare(strict_types=1);

return [
    'guard' => env('AUTH_GUARD', 'session'),
    'session_key' => env('AUTH_SESSION_KEY', '_auth_user'),

    'redirects' => [
        'login' => env('AUTH_REDIRECT_LOGIN', '/'),
        'register' => env('AUTH_REDIRECT_REGISTER', '/'),
        'logout' => env('AUTH_REDIRECT_LOGOUT', '/login'),
        'reset' => env('AUTH_REDIRECT_RESET', '/login'),
    ],

    'remember' => [
        'enabled' => env('AUTH_REMEMBER', true),
        'cookie' => env('AUTH_REMEMBER_COOKIE', 'remember_token'),
        'lifetime' => env('AUTH_REMEMBER_LIFETIME', 1209600),
        'path' => env('AUTH_REMEMBER_PATH', '/'),
        'domain' => env('AUTH_REMEMBER_DOMAIN', ''),
        'secure' => env('AUTH_REMEMBER_SECURE', null),
        'httponly' => env('AUTH_REMEMBER_HTTPONLY', true),
        'samesite' => env('AUTH_REMEMBER_SAMESITE', 'Lax'),
        'store' => env('AUTH_REMEMBER_STORE', 'storage/auth/remember'),
    ],

    'password' => [
        'driver' => env('AUTH_HASH_DRIVER', 'bcrypt'),
        'bcrypt' => [
            'cost' => env('BCRYPT_COST', 12),
        ],
        'argon2id' => [
            'memory_cost' => env('ARGON_MEMORY', 65536),
            'time_cost' => env('ARGON_TIME', 4),
            'threads' => env('ARGON_THREADS', 2),
        ],
    ],

    'reset' => [
        'enabled' => env('AUTH_RESET_ENABLED', true),
        'ttl' => env('AUTH_RESET_TTL', 3600),
        'store' => env('AUTH_RESET_STORE', 'storage/auth/resets'),
    ],

    'provider' => [
        'by_id' => static fn ($id) => app()->make(\App\Auth\DatabaseUserProvider::class)->retrieveById($id),
        'by_credentials' => static fn (array $credentials) => app()->make(\App\Auth\DatabaseUserProvider::class)->retrieveByCredentials($credentials),
        'validate' => static fn ($user, array $credentials) => app()->make(\App\Auth\DatabaseUserProvider::class)->validateCredentials($user, $credentials),
        'create' => static fn (array $data) => app()->make(\App\Auth\DatabaseUserProvider::class)->createUser($data),
        'update_password' => static fn ($user, string $hash) => app()->make(\App\Auth\DatabaseUserProvider::class)->updatePassword($user, $hash),
    ],
];
