<?php

declare(strict_types=1);

namespace App\Services;

final class AppStatusService
{
    public function snapshot(): array
    {
        $app = app();
        $config = $app->config();

        return [
            'status' => 'ok',
            'service' => (string) $config->get('name', 'Fnlla'),
            'env' => (string) $config->get('env', 'local'),
            'version' => (string) $config->get('version', 'dev'),
            'Fnlla' => \Fnlla\Core\Application::VERSION,
            'php' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'host' => (string) (function_exists('gethostname') ? gethostname() : ''),
            'timezone' => date_default_timezone_get(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_limit' => (string) ini_get('memory_limit'),
            'upload_max_filesize' => (string) ini_get('upload_max_filesize'),
            'post_max_size' => (string) ini_get('post_max_size'),
            'time' => gmdate('c'),
        ];
    }
}
