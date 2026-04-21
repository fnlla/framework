<?php

declare(strict_types=1);

namespace App\Services;

use Fnlla\Support\RedisConnector;
use Throwable;

final class AppReadinessService
{
    public function snapshot(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'ops_admin_auth' => $this->checkAdminAuth(),
            'ops_docs_access' => $this->checkDocsAccess(),
            'ops_warm_kernel' => $this->checkWarmKernel(),
        ];

        $failed = false;
        foreach ($checks as $check) {
            if (($check['status'] ?? '') === 'fail') {
                $failed = true;
                break;
            }
        }

        return [
            'status' => $failed ? 'fail' : 'ok',
            'time' => gmdate('c'),
            'checks' => $checks,
        ];
    }

    private function checkDatabase(): array
    {
        if (!class_exists(\Fnlla\Database\ConnectionManager::class)) {
            return [
                'status' => 'skipped',
                'detail' => 'database core module not available',
            ];
        }

        $app = app();
        $config = $app->config()->get('database', []);
        if (!is_array($config)) {
            $config = [];
        }

        if (!$this->isDatabaseConfigured($config)) {
            return [
                'status' => 'skipped',
                'detail' => 'database not configured',
            ];
        }

        try {
            $manager = $app->make(\Fnlla\Database\ConnectionManager::class);
            if (!$manager instanceof \Fnlla\Database\ConnectionManager) {
                return [
                    'status' => 'fail',
                    'detail' => 'ConnectionManager not available',
                ];
            }
            $pdo = $manager->connection();
            $pdo->query('select 1');

            return [
                'status' => 'ok',
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'fail',
                'detail' => $e->getMessage(),
            ];
        }
    }

    private function checkQueue(): array
    {
        if (!class_exists(\Fnlla\Queue\QueueManager::class)) {
            return [
                'status' => 'skipped',
                'detail' => 'fnlla/queue not installed',
            ];
        }

        $app = app();
        $config = $app->config()->get('queue', []);
        if (!is_array($config)) {
            $config = [];
        }

        $driver = strtolower((string) ($config['driver'] ?? 'sync'));
        if ($driver === '' || $driver === 'sync') {
            return [
                'status' => 'ok',
                'detail' => 'sync',
            ];
        }

        if ($driver === 'database') {
            $db = $this->checkDatabase();
            if (($db['status'] ?? '') !== 'ok') {
                return [
                    'status' => 'fail',
                    'detail' => 'database: ' . ($db['detail'] ?? 'unavailable'),
                ];
            }
            return [
                'status' => 'ok',
                'detail' => 'database',
            ];
        }

        if ($driver === 'redis') {
            $redisConfig = $config['redis'] ?? [];
            if (!is_array($redisConfig)) {
                $redisConfig = [];
            }
            return $this->checkRedis($redisConfig, 'queue');
        }

        return [
            'status' => 'fail',
            'detail' => 'unsupported driver: ' . $driver,
        ];
    }

    private function checkCache(): array
    {
        if (!class_exists(\Fnlla\Cache\CacheManager::class)) {
            return [
                'status' => 'skipped',
                'detail' => 'cache core module not available',
            ];
        }

        $app = app();
        $config = $app->config()->get('cache', []);
        if (!is_array($config)) {
            $config = [];
        }

        $driver = strtolower((string) ($config['driver'] ?? 'file'));
        if ($driver === '' || $driver === 'file' || $driver === 'array') {
            return [
                'status' => 'ok',
                'detail' => $driver === '' ? 'file' : $driver,
            ];
        }

        if ($driver === 'redis') {
            $redisConfig = $config['redis'] ?? [];
            if (!is_array($redisConfig)) {
                $redisConfig = [];
            }
            return $this->checkRedis($redisConfig, 'cache');
        }

        return [
            'status' => 'fail',
            'detail' => 'unsupported driver: ' . $driver,
        ];
    }

    private function checkAdminAuth(): array
    {
        $adminRoutesEnabled = $this->toBool(getenv('Fnlla_ADMIN_ENABLED'), false);
        $adminLoginRequired = $this->toBool(getenv('Fnlla_ADMIN_LOGIN_REQUIRED'), true);
        $adminAuthAllow = $this->toBool(getenv('Fnlla_ADMIN_AUTH_ALLOW_AUTH'), false);
        $adminHash = trim((string) getenv('Fnlla_ADMIN_LOGIN_PASSWORD_HASH'));

        if (!$adminRoutesEnabled) {
            return [
                'status' => 'skipped',
                'detail' => 'admin routes disabled',
            ];
        }

        if (!$adminLoginRequired) {
            return [
                'status' => 'warn',
                'detail' => 'Fnlla_ADMIN_LOGIN_REQUIRED=0',
            ];
        }

        if ($adminHash === '') {
            return [
                'status' => 'fail',
                'detail' => 'Fnlla_ADMIN_LOGIN_PASSWORD_HASH is required',
            ];
        }

        if ($adminAuthAllow) {
            return [
                'status' => 'warn',
                'detail' => 'Fnlla_ADMIN_AUTH_ALLOW_AUTH=1',
            ];
        }

        return [
            'status' => 'ok',
            'detail' => 'hash configured',
        ];
    }

    private function checkDocsAccess(): array
    {
        $docsEnabled = $this->toBool(getenv('Fnlla_DOCS_ENABLED'), false);
        if (!$docsEnabled) {
            return [
                'status' => 'skipped',
                'detail' => 'Fnlla_DOCS_ENABLED=0',
            ];
        }

        $docsPublic = $this->toBool(getenv('Fnlla_DOCS_PUBLIC'), false);
        if ($docsPublic) {
            return [
                'status' => 'warn',
                'detail' => 'Fnlla_DOCS_PUBLIC=1',
            ];
        }

        $docsToken = trim((string) getenv('Fnlla_DOCS_ACCESS_TOKEN'));
        if ($docsToken === '') {
            return [
                'status' => 'fail',
                'detail' => 'Fnlla_DOCS_ACCESS_TOKEN missing',
            ];
        }

        return [
            'status' => 'ok',
            'detail' => 'token configured',
        ];
    }

    private function checkWarmKernel(): array
    {
        $warmKernel = $this->toBool(getenv('Fnlla_WARM_KERNEL'), false);
        if (!$warmKernel) {
            return [
                'status' => 'skipped',
                'detail' => 'Fnlla_WARM_KERNEL=0',
            ];
        }

        $app = app();
        $resetters = $app->resetters();
        $count = count($resetters);

        if ($count === 0) {
            return [
                'status' => 'warn',
                'detail' => 'no resetters registered',
            ];
        }

        return [
            'status' => 'ok',
            'detail' => 'resetters=' . $count,
        ];
    }

    private function toBool(mixed $value, bool $default = false): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value === 1;
        }
        if (!is_string($value)) {
            return $default;
        }

        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return $default;
        }

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    private function checkRedis(array $config, string $label): array
    {
        if (!class_exists(\Redis::class)) {
            return [
                'status' => 'fail',
                'detail' => $label . ': ext-redis missing',
            ];
        }

        try {
            $redis = RedisConnector::connect($config);
            $pong = $redis->ping();
            if ($pong === false) {
                return [
                    'status' => 'fail',
                    'detail' => $label . ': ping failed',
                ];
            }

            return [
                'status' => 'ok',
                'detail' => $label . ': redis',
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'fail',
                'detail' => $label . ': ' . $e->getMessage(),
            ];
        }
    }

    private function isDatabaseConfigured(array $config): bool
    {
        if ($config !== []) {
            return true;
        }

        foreach (['DB_CONNECTION', 'DB_DATABASE', 'DB_PATH'] as $key) {
            $value = getenv($key);
            if ($value !== false && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }
}
