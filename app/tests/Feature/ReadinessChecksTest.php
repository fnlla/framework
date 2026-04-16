<?php

declare(strict_types=1);

use Finella\Database\ConnectionManager;
use PHPUnit\Framework\Assert;
use Tests\Feature\Support\FeatureTestCase;

final class ReadinessChecksTest extends FeatureTestCase
{
    public function testReadyReturnsOkJsonByDefault(): void
    {
        $payload = $this->readyJson(200);
        Assert::assertSame('ok', $payload['status'] ?? null);
    }

    public function testReadyPayloadContainsExpectedTopLevelKeys(): void
    {
        $payload = $this->readyJson(200);

        Assert::assertArrayHasKey('status', $payload);
        Assert::assertArrayHasKey('time', $payload);
        Assert::assertArrayHasKey('checks', $payload);
        Assert::assertIsArray($payload['checks']);
    }

    public function testReadyPayloadContainsExpectedChecks(): void
    {
        $payload = $this->readyJson(200);
        $checks = $payload['checks'] ?? [];

        Assert::assertIsArray($checks);
        Assert::assertArrayHasKey('database', $checks);
        Assert::assertArrayHasKey('cache', $checks);
        Assert::assertArrayHasKey('queue', $checks);
        Assert::assertArrayHasKey('ops_admin_auth', $checks);
        Assert::assertArrayHasKey('ops_docs_access', $checks);
        Assert::assertArrayHasKey('ops_warm_kernel', $checks);
    }

    public function testReadyReturns503WhenCacheRedisIsUnavailable(): void
    {
        $this->setConfigValue('cache.driver', 'redis');
        $this->setConfigValue('cache.redis', [
            'host' => '127.0.0.1',
            'port' => 1,
            'timeout' => 0.2,
            'read_timeout' => 0.2,
        ]);

        $payload = $this->readyJson(503);
        Assert::assertSame('fail', $payload['checks']['cache']['status'] ?? null);
    }

    public function testReadyReturns503WhenQueueRedisIsUnavailable(): void
    {
        $this->ensureQueueManagerClassAvailable();
        $this->setConfigValue('queue.driver', 'redis');
        $this->setConfigValue('queue.redis', [
            'host' => '127.0.0.1',
            'port' => 1,
            'timeout' => 0.2,
            'read_timeout' => 0.2,
            'queue' => 'default',
        ]);

        $payload = $this->readyJson(503);
        Assert::assertSame('fail', $payload['checks']['queue']['status'] ?? null);
    }

    public function testReadyReturns503WhenDatabaseConnectionFails(): void
    {
        $this->setConfigValue('database.default', 'sqlite');
        $this->setConfigValue('database.connections.sqlite.path', $this->appRoot . '/storage/missing-dir/ready.sqlite');
        $this->rebindConnectionManagerFromConfig();

        $payload = $this->readyJson(503);
        Assert::assertSame('fail', $payload['checks']['database']['status'] ?? null);
    }

    public function testReadyReturns503WhenQueueDatabaseDependsOnFailedDatabase(): void
    {
        $this->ensureQueueManagerClassAvailable();
        $this->setConfigValue('queue.driver', 'database');
        $this->setConfigValue('database.default', 'sqlite');
        $this->setConfigValue('database.connections.sqlite.path', $this->appRoot . '/storage/missing-dir/queue.sqlite');
        $this->rebindConnectionManagerFromConfig();

        $payload = $this->readyJson(503);
        Assert::assertSame('fail', $payload['checks']['queue']['status'] ?? null);
        Assert::assertStringContainsString('database:', (string) ($payload['checks']['queue']['detail'] ?? ''));
    }

    public function testReadyKeepsOverallOkWhenAdminCheckIsWarning(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'ADMIN_ENABLED' => '1',
            'ADMIN_LOGIN_REQUIRED' => '1',
            'ADMIN_AUTH_ALLOW_AUTH' => '0',
            'ADMIN_LOGIN_PASSWORD' => 'PlainPassword123!',
        ]);
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD_HASH');

        $payload = $this->readyJson(200);
        Assert::assertSame('ok', $payload['status'] ?? null);
        Assert::assertSame('warn', $payload['checks']['ops_admin_auth']['status'] ?? null);
    }

    public function testReadyKeepsOverallOkWhenDocsCheckIsWarning(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'DOCS_ENABLED' => '1',
            'DOCS_PUBLIC' => '0',
        ]);
        $this->unsetEnvValue('DOCS_ACCESS_TOKEN');

        $payload = $this->readyJson(200);
        Assert::assertSame('ok', $payload['status'] ?? null);
        Assert::assertSame('warn', $payload['checks']['ops_docs_access']['status'] ?? null);
    }

    public function testReadyMarksDocsCheckAsOkWhenTokenIsConfiguredInProd(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'DOCS_ENABLED' => '1',
            'DOCS_PUBLIC' => '0',
            'DOCS_ACCESS_TOKEN' => 'release-secret',
        ]);

        $payload = $this->readyJson(200);
        Assert::assertSame('ok', $payload['checks']['ops_docs_access']['status'] ?? null);
    }

    public function testReadyMarksAdminCheckAsOkWhenPasswordHashIsConfiguredInProd(): void
    {
        $this->applyProdDefaults();
        $hash = password_hash('AdminPass123!', PASSWORD_BCRYPT);
        Assert::assertIsString($hash);
        $this->setEnvValues([
            'ADMIN_ENABLED' => '1',
            'ADMIN_LOGIN_REQUIRED' => '1',
            'ADMIN_AUTH_ALLOW_AUTH' => '0',
            'ADMIN_LOGIN_PASSWORD_HASH' => $hash,
        ]);
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD');

        $payload = $this->readyJson(200);
        Assert::assertSame('ok', $payload['checks']['ops_admin_auth']['status'] ?? null);
    }

    public function testReadyReportsFileCacheAsHealthyByDefault(): void
    {
        $payload = $this->readyJson(200);

        Assert::assertSame('ok', $payload['checks']['cache']['status'] ?? null);
        Assert::assertSame('file', $payload['checks']['cache']['detail'] ?? null);
    }

    public function testReadyReportsSyncQueueAsHealthyByDefault(): void
    {
        $payload = $this->readyJson(200);
        $status = (string) ($payload['checks']['queue']['status'] ?? '');
        Assert::assertContains($status, ['ok', 'skipped']);
        if ($status === 'ok') {
            Assert::assertSame('sync', $payload['checks']['queue']['detail'] ?? null);
        }
    }

    public function testStatusEndpointReturnsHealthAndReadinessData(): void
    {
        $response = $this->get('/status?format=json')->assertStatus(200);
        $payload = $response->json();

        Assert::assertArrayHasKey('health', $payload);
        Assert::assertArrayHasKey('readiness', $payload);
        Assert::assertSame('ok', $payload['health']['status'] ?? null);
    }

    public function testStatusEndpointReflectsReadinessFailureFromCacheRedisOutage(): void
    {
        $this->setConfigValue('cache.driver', 'redis');
        $this->setConfigValue('cache.redis', [
            'host' => '127.0.0.1',
            'port' => 1,
            'timeout' => 0.2,
            'read_timeout' => 0.2,
        ]);

        $response = $this->get('/status?format=json')->assertStatus(200);
        $payload = $response->json();

        Assert::assertSame('fail', $payload['readiness']['status'] ?? null);
        Assert::assertSame('fail', $payload['readiness']['checks']['cache']['status'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    private function readyJson(int $expectedHttpStatus): array
    {
        $response = $this->get('/ready?format=json')->assertStatus($expectedHttpStatus);
        $payload = $response->json();

        Assert::assertIsArray($payload);
        Assert::assertArrayHasKey('checks', $payload);

        return $payload;
    }

    private function rebindConnectionManagerFromConfig(): void
    {
        $config = $this->app()->config()->get('database', []);
        if (!is_array($config)) {
            $config = [];
        }

        $this->app()->instance(ConnectionManager::class, new ConnectionManager($config));
    }

    private function ensureQueueManagerClassAvailable(): void
    {
        if (class_exists(\Finella\Queue\QueueManager::class)) {
            return;
        }

        $queueManagerPath = dirname($this->appRoot) . '/packages/queue/src/QueueManager.php';
        Assert::assertFileExists($queueManagerPath);
        require_once $queueManagerPath;

        Assert::assertTrue(class_exists(\Finella\Queue\QueueManager::class));
    }
}
