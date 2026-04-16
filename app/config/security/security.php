<?php

declare(strict_types=1);

$env = (string) env('APP_ENV', 'local');
$hstsEnabled = $env === 'prod';
$hstsMaxAge = (int) env('SECURITY_HSTS_MAX_AGE', 31536000);
$hstsIncludeSubdomains = env('SECURITY_HSTS_SUBDOMAINS', true);
$hstsPreload = env('SECURITY_HSTS_PRELOAD', true);

$hsts = null;
if ($hstsEnabled && $hstsMaxAge > 0) {
    $hsts = 'max-age=' . $hstsMaxAge;
    if ($hstsIncludeSubdomains) {
        $hsts .= '; includeSubDomains';
    }
    if ($hstsPreload) {
        $hsts .= '; preload';
    }
}

return [
    'headers' => [
        'Strict-Transport-Security' => $hsts,
    ],
    'csp' => env('SECURITY_CSP', null),
];
