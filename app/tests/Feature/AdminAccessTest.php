<?php

declare(strict_types=1);

use Finella\Testing\TestCase;

final class AdminAccessTest extends TestCase
{
    public function testAdminRequiresLoginInProd(): void
    {
        $this->setEnvValue('APP_ENV', 'prod');
        $this->setEnvValue('APP_DEBUG', '0');
        $this->setEnvValue('ADMIN_ENABLED', '1');
        $this->setEnvValue('ADMIN_DEV_ENABLED', '0');
        $this->setEnvValue('ADMIN_LOGIN_REQUIRED', '1');
        $this->setEnvValue('ADMIN_AUTH_ALLOW_AUTH', '0');
        $this->setEnvValue('ADMIN_ALLOW_UNCONFIGURED', '0');

        $this->get('/admin')->assertRedirect('/admin/login');
    }

    private function setEnvValue(string $key, string $value): void
    {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
