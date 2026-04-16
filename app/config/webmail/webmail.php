<?php

declare(strict_types=1);

$allowlist = static function (mixed $value): array {
    if (is_array($value)) {
        return $value;
    }
    if (!is_string($value)) {
        return [];
    }
    $value = trim($value);
    if ($value === '') {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode(',', $value)), static fn ($item) => $item !== ''));
};

return [
    'tenant_scoped' => (bool) env('WEBMAIL_TENANT_SCOPED', false),
    'tenant_prefix' => env('WEBMAIL_TENANT_PREFIX', 'tenant:'),
    'send_async' => (bool) env('WEBMAIL_SEND_ASYNC', false),
    'imap' => [
        'host' => env('WEBMAIL_IMAP_HOST', ''),
        'port' => (int) env('WEBMAIL_IMAP_PORT', 993),
        'flags' => env('WEBMAIL_IMAP_FLAGS', '/imap/ssl'),
        'username' => env('WEBMAIL_IMAP_USER', ''),
        'password' => env('WEBMAIL_IMAP_PASS', ''),
        'folder' => env('WEBMAIL_IMAP_FOLDER', 'INBOX'),
    ],
    'smtp' => [
        'dsn' => env('WEBMAIL_SMTP_DSN', ''),
        'host' => env('WEBMAIL_SMTP_HOST', ''),
        'port' => (int) env('WEBMAIL_SMTP_PORT', 587),
        'username' => env('WEBMAIL_SMTP_USER', ''),
        'password' => env('WEBMAIL_SMTP_PASS', ''),
        'encryption' => env('WEBMAIL_SMTP_ENCRYPTION', 'tls'),
        'from_address' => env('WEBMAIL_SMTP_FROM_ADDRESS', ''),
        'from_name' => env('WEBMAIL_SMTP_FROM_NAME', ''),
    ],
    'security' => [
        'require_encryption' => (bool) env('WEBMAIL_REQUIRE_ENCRYPTION', false),
        'imap_host_allowlist' => $allowlist(env('WEBMAIL_IMAP_ALLOWLIST', '')),
        'smtp_host_allowlist' => $allowlist(env('WEBMAIL_SMTP_ALLOWLIST', '')),
        'test_enabled' => (bool) env('WEBMAIL_TEST_ENABLED', true),
        'test_recipient_allowlist' => $allowlist(env('WEBMAIL_TEST_RECIPIENT_ALLOWLIST', '')),
    ],
];
