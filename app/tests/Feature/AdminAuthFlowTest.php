<?php

declare(strict_types=1);

use Finella\Cache\CacheManager;
use PHPUnit\Framework\Assert;
use Tests\Feature\Support\FeatureTestCase;

final class AdminAuthFlowTest extends FeatureTestCase
{
    public function testAdminRoutesReturn404WhenDisabledInProd(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'ADMIN_ENABLED' => '0',
            'ADMIN_DEV_ENABLED' => '0',
        ]);

        $this->get('/admin')->assertStatus(404);
        $this->get('/admin/login')->assertStatus(404);
    }

    public function testAdminLoginFormIsAvailableWhenAdminIsEnabled(): void
    {
        $this->enableAdminWithHash();
        $this->get('/admin/login')->assertStatus(200);
    }

    public function testAdminLoginFormShowsNoticeWhenCredentialsAreNotConfigured(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'ADMIN_ENABLED' => '1',
            'ADMIN_LOGIN_REQUIRED' => '1',
            'ADMIN_AUTH_ALLOW_AUTH' => '0',
            'ADMIN_LOGIN_EMAIL' => 'admin@example.test',
        ]);
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD');
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD_HASH');

        $response = $this->get('/admin/login')->assertStatus(200);
        Assert::assertStringContainsString('Admin login is not configured', $response->body());
    }

    public function testAdminProtectedRouteRedirectsToLoginWhenNotAuthenticated(): void
    {
        $this->enableAdminWithHash();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function testAdminAllowsUnconfiguredAccessInDevWhenFlagIsEnabled(): void
    {
        $this->setEnvValues([
            'APP_ENV' => 'dev',
            'APP_DEBUG' => '1',
            'ADMIN_ENABLED' => '0',
            'ADMIN_DEV_ENABLED' => '1',
            'ADMIN_LOGIN_REQUIRED' => '1',
            'ADMIN_ALLOW_UNCONFIGURED' => '1',
            'ADMIN_AUTH_ALLOW_AUTH' => '0',
        ]);
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD');
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD_HASH');

        $this->get('/admin')->assertStatus(200);
    }

    public function testAdminLoginRejectsWhenConfigurationIsMissing(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'ADMIN_ENABLED' => '1',
            'ADMIN_LOGIN_REQUIRED' => '1',
            'ADMIN_AUTH_ALLOW_AUTH' => '0',
            'ADMIN_LOGIN_EMAIL' => 'admin@example.test',
        ]);
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD');
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD_HASH');

        $response = $this->postAdminLogin([
            'email' => 'admin@example.test',
            'password' => 'AdminPass123!',
        ])->assertStatus(200);

        Assert::assertStringContainsString('Admin login is not configured', $response->body());
    }

    public function testAdminLoginRejectsPlainPasswordInProdWithoutHash(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'ADMIN_ENABLED' => '1',
            'ADMIN_LOGIN_REQUIRED' => '1',
            'ADMIN_AUTH_ALLOW_AUTH' => '0',
            'ADMIN_LOGIN_EMAIL' => 'admin@example.test',
            'ADMIN_LOGIN_PASSWORD' => 'AdminPass123!',
        ]);
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD_HASH');

        $response = $this->postAdminLogin([
            'email' => 'admin@example.test',
            'password' => 'AdminPass123!',
        ])->assertStatus(200);

        Assert::assertStringContainsString('requires ADMIN_LOGIN_PASSWORD_HASH', $response->body());
    }

    public function testAdminLoginRejectsInvalidPasswordWithConfiguredHash(): void
    {
        $this->enableAdminWithHash();

        $response = $this->postAdminLogin([
            'email' => 'admin@example.test',
            'password' => 'WrongPassword!',
        ])->assertStatus(200);

        Assert::assertStringContainsString('Invalid admin credentials', $response->body());
    }

    public function testAdminLoginRejectsEmailMismatch(): void
    {
        $this->enableAdminWithHash();

        $response = $this->postAdminLogin([
            'email' => 'other@example.test',
            'password' => 'AdminPass123!',
        ])->assertStatus(200);

        Assert::assertStringContainsString('Invalid admin credentials', $response->body());
    }

    public function testAdminLoginAcceptsValidCredentialsAndRedirectsToAdmin(): void
    {
        $this->enableAdminWithHash();

        $this->postAdminLogin([
            'email' => 'admin@example.test',
            'password' => 'AdminPass123!',
        ])->assertRedirect('/admin');
    }

    public function testAdminLoginRestoresIntendedPathAfterAuthentication(): void
    {
        $this->enableAdminWithHash();

        $this->get('/admin/analytics')->assertRedirect('/admin/login');

        $this->postAdminLogin([
            'email' => 'admin@example.test',
            'password' => 'AdminPass123!',
        ])->assertRedirect('/admin/analytics');
    }

    public function testAdminLoginSanitizesExternalRedirectTarget(): void
    {
        $this->enableAdminWithHash();

        $this->postAdminLogin([
            'email' => 'admin@example.test',
            'password' => 'AdminPass123!',
            'redirect' => 'https://evil.example.test',
        ])->assertRedirect('/admin');
    }

    public function testAdminLogoutRevokesAccessAndRedirectsToLogin(): void
    {
        $this->enableAdminWithHash();

        $this->postAdminLogin([
            'email' => 'admin@example.test',
            'password' => 'AdminPass123!',
        ])->assertRedirect('/admin');

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function testAdminAccessIsAllowedWhenLoginIsNotRequired(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'ADMIN_ENABLED' => '1',
            'ADMIN_LOGIN_REQUIRED' => '0',
            'ADMIN_AUTH_ALLOW_AUTH' => '0',
        ]);
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD');
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD_HASH');

        $this->get('/admin')->assertStatus(200);
    }

    private function enableAdminWithHash(): void
    {
        $this->applyProdDefaults();
        $this->setEnvValues([
            'ADMIN_ENABLED' => '1',
            'ADMIN_DEV_ENABLED' => '0',
            'ADMIN_LOGIN_REQUIRED' => '1',
            'ADMIN_AUTH_ALLOW_AUTH' => '0',
            'ADMIN_LOGIN_EMAIL' => 'admin@example.test',
            'ADMIN_LOGIN_PASSWORD_HASH' => $this->adminPasswordHash(),
            'ADMIN_ALLOW_UNCONFIGURED' => '0',
        ]);
        $this->unsetEnvValue('ADMIN_LOGIN_PASSWORD');
    }

    private function adminPasswordHash(): string
    {
        static $hash = null;
        if (!is_string($hash)) {
            $generated = password_hash('AdminPass123!', PASSWORD_BCRYPT);
            Assert::assertIsString($generated);
            $hash = $generated;
        }

        return $hash;
    }

    /**
     * @param array<string, string> $payload
     */
    private function postAdminLogin(array $payload): \Finella\Testing\TestResponse
    {
        $cache = $this->app()->make(CacheManager::class);
        if ($cache instanceof CacheManager) {
            $cache->delete('rate:POST:/admin/login:ip:127.0.0.1');
        }

        return $this->post('/admin/login', $payload);
    }
}
