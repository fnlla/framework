<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use Finella\Database\ConnectionManager;
use Finella\Testing\TestCase;
use PDO;

abstract class FeatureTestCase extends TestCase
{
    /**
     * @var array<string, array{env: string|false, has_env: bool, env_val: mixed, has_server: bool, server_val: mixed}>
     */
    private array $envSnapshots = [];

    public function tearDown(): void
    {
        $this->restoreEnv();
        parent::tearDown();
    }

    protected function setEnvValue(string $key, string|int|bool $value): void
    {
        $this->snapshotEnv($key);

        $stringValue = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
        putenv($key . '=' . $stringValue);
        $_ENV[$key] = $stringValue;
        $_SERVER[$key] = $stringValue;
    }

    /**
     * @param array<string, string|int|bool> $values
     */
    protected function setEnvValues(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->setEnvValue($key, $value);
        }
    }

    protected function unsetEnvValue(string $key): void
    {
        $this->snapshotEnv($key);

        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }

    protected function applyProdDefaults(): void
    {
        $this->setEnvValues([
            'APP_ENV' => 'prod',
            'APP_DEBUG' => '0',
            'RATE_LIMIT_ENABLED' => '0',
        ]);
    }

    protected function setConfigValue(string $key, mixed $value): void
    {
        $this->app()->config()->set($key, $value);
    }

    /**
     * @return array{id: int, email: string, password_hash: string, password_plain: string}
     */
    protected function insertUser(string $email, string $passwordPlain = 'Password123!'): array
    {
        $connections = $this->app()->make(ConnectionManager::class);
        if (!$connections instanceof ConnectionManager) {
            throw new \RuntimeException('ConnectionManager is not available.');
        }

        $pdo = $connections->connection();
        $passwordHash = password_hash($passwordPlain, PASSWORD_BCRYPT);
        if (!is_string($passwordHash) || $passwordHash === '') {
            throw new \RuntimeException('Failed to hash password.');
        }

        $now = gmdate('Y-m-d H:i:s');
        $statement = $pdo->prepare(
            'INSERT INTO users (name, email, password, created_at, updated_at) VALUES (:name, :email, :password, :created_at, :updated_at)'
        );
        if ($statement === false) {
            throw new \RuntimeException('Failed to prepare user insert statement.');
        }

        $statement->execute([
            'name' => 'Feature User',
            'email' => $email,
            'password' => $passwordHash,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = (int) $pdo->lastInsertId();

        return [
            'id' => $id,
            'email' => $email,
            'password_hash' => $passwordHash,
            'password_plain' => $passwordPlain,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function findUserByEmail(string $email): ?array
    {
        $connections = $this->app()->make(ConnectionManager::class);
        if (!$connections instanceof ConnectionManager) {
            throw new \RuntimeException('ConnectionManager is not available.');
        }

        $pdo = $connections->connection();
        $statement = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        if ($statement === false) {
            throw new \RuntimeException('Failed to prepare user select statement.');
        }

        $statement->execute(['email' => $email]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    private function snapshotEnv(string $key): void
    {
        if (array_key_exists($key, $this->envSnapshots)) {
            return;
        }

        $this->envSnapshots[$key] = [
            'env' => getenv($key),
            'has_env' => array_key_exists($key, $_ENV),
            'env_val' => $_ENV[$key] ?? null,
            'has_server' => array_key_exists($key, $_SERVER),
            'server_val' => $_SERVER[$key] ?? null,
        ];
    }

    private function restoreEnv(): void
    {
        foreach ($this->envSnapshots as $key => $snapshot) {
            $original = $snapshot['env'];
            if ($original === false) {
                putenv($key);
            } else {
                putenv($key . '=' . $original);
            }

            if ($snapshot['has_env']) {
                $_ENV[$key] = $snapshot['env_val'];
            } else {
                unset($_ENV[$key]);
            }

            if ($snapshot['has_server']) {
                $_SERVER[$key] = $snapshot['server_val'];
            } else {
                unset($_SERVER[$key]);
            }
        }

        $this->envSnapshots = [];
    }
}
