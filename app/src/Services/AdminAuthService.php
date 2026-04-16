<?php

declare(strict_types=1);

namespace App\Services;

use Finella\Auth\AuthManager;
use Finella\Authorization\Gate;
use Finella\Contracts\Log\LoggerInterface;
use Finella\Http\Request;
use Finella\Session\SessionInterface;
use Throwable;

final class AdminAuthService
{
    private const SESSION_GRANTED = 'admin_access_granted';
    private const SESSION_EMAIL = 'admin_access_email';
    private const SESSION_AT = 'admin_access_at';
    private const SESSION_INTENDED = 'admin_intended_path';

    public function requiresLogin(): bool
    {
        return (bool) env('ADMIN_LOGIN_REQUIRED', true);
    }

    public function isConfigured(): bool
    {
        $password = trim((string) env('ADMIN_LOGIN_PASSWORD', ''));
        $hash = trim((string) env('ADMIN_LOGIN_PASSWORD_HASH', ''));
        return $password !== '' || $hash !== '';
    }

    public function expectedEmail(): string
    {
        return trim((string) env('ADMIN_LOGIN_EMAIL', ''));
    }

    public function isAuthenticated(?Request $request = null): bool
    {
        $session = $this->session();
        if ($session !== null && $this->isSessionValid($session)) {
            return true;
        }

        if ($this->allowsAuth($request)) {
            return true;
        }

        return false;
    }

    public function attempt(string $email, string $password, ?Request $request = null): array
    {
        if (!$this->isConfigured()) {
            $this->logFailedAttempt($email, 'not_configured', $request);
            return ['ok' => false, 'error' => 'Admin login is not configured.'];
        }

        if ($this->requiresHashedPassword() && trim((string) env('ADMIN_LOGIN_PASSWORD_HASH', '')) === '') {
            $this->logFailedAttempt($email, 'hash_required', $request);
            return ['ok' => false, 'error' => 'Admin login requires ADMIN_LOGIN_PASSWORD_HASH in this environment.'];
        }

        $expectedEmail = $this->expectedEmail();
        if ($expectedEmail !== '' && !$this->emailMatches($expectedEmail, $email)) {
            $this->logFailedAttempt($email, 'email_mismatch', $request);
            return ['ok' => false, 'error' => 'Invalid admin credentials.'];
        }

        if (!$this->verifyPassword($password)) {
            $this->logFailedAttempt($email, 'invalid_password', $request);
            return ['ok' => false, 'error' => 'Invalid admin credentials.'];
        }

        $session = $this->session();
        if ($session !== null) {
            $session->regenerateId(true);
            $session->put(self::SESSION_GRANTED, true);
            $session->put(self::SESSION_EMAIL, $expectedEmail !== '' ? $expectedEmail : $email);
            $session->put(self::SESSION_AT, time());
        }

        return ['ok' => true];
    }

    public function logout(): void
    {
        $session = $this->session();
        if ($session === null) {
            return;
        }

        $session->forget(self::SESSION_GRANTED);
        $session->forget(self::SESSION_EMAIL);
        $session->forget(self::SESSION_AT);
        $session->forget(self::SESSION_INTENDED);
        $session->regenerateId(true);
    }

    public function rememberIntended(Request $request): void
    {
        $session = $this->session();
        if ($session === null) {
            return;
        }

        if (!$this->shouldCaptureIntended($request)) {
            return;
        }

        $session->put(self::SESSION_INTENDED, $this->intendedPath($request));
    }

    public function peekIntended(): string
    {
        $session = $this->session();
        if ($session === null) {
            return '';
        }

        return trim((string) $session->get(self::SESSION_INTENDED, ''));
    }

    public function consumeIntended(): string
    {
        $session = $this->session();
        if ($session === null) {
            return '';
        }

        $value = trim((string) $session->get(self::SESSION_INTENDED, ''));
        $session->forget(self::SESSION_INTENDED);
        return $value;
    }

    public function sanitizeRedirect(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path[0] !== '/') {
            return '/admin';
        }
        if (str_starts_with($path, '//')) {
            return '/admin';
        }
        if (!str_starts_with($path, '/admin')) {
            return '/admin';
        }
        return $path;
    }

    private function isSessionValid(SessionInterface $session): bool
    {
        if (!(bool) $session->get(self::SESSION_GRANTED, false)) {
            return false;
        }

        $ttlMinutes = (int) env('ADMIN_SESSION_TTL_MINUTES', 0);
        if ($ttlMinutes <= 0) {
            return true;
        }

        $timestamp = (int) $session->get(self::SESSION_AT, 0);
        if ($timestamp <= 0) {
            return false;
        }

        if ((time() - $timestamp) > ($ttlMinutes * 60)) {
            $session->forget(self::SESSION_GRANTED);
            $session->forget(self::SESSION_EMAIL);
            $session->forget(self::SESSION_AT);
            return false;
        }

        return true;
    }

    private function allowsAuth(?Request $request = null): bool
    {
        if (!(bool) env('ADMIN_AUTH_ALLOW_AUTH', false)) {
            return false;
        }

        try {
            if (!class_exists(AuthManager::class)) {
                return false;
            }
            $app = app();
            if (!$app->has(AuthManager::class)) {
                return false;
            }
            $auth = $app->make(AuthManager::class);
            if (!$auth instanceof AuthManager) {
                return false;
            }
            if (!$auth->check($request)) {
                return false;
            }

            $ability = trim((string) env('ADMIN_AUTH_GATE', ''));
            if ($ability === '') {
                return true;
            }

            $gate = $this->resolveGate();
            if (!$gate instanceof Gate) {
                return false;
            }

            return $gate->allows($ability, null, $request);
        } catch (Throwable) {
            return false;
        }
    }

    private function resolveGate(): ?Gate
    {
        try {
            if (!class_exists(Gate::class)) {
                return null;
            }
            $app = app();
            if ($app->has(Gate::class)) {
                $gate = $app->make(Gate::class);
                return $gate instanceof Gate ? $gate : null;
            }
            return new Gate($app, new \Finella\Authorization\PolicyRegistry());
        } catch (Throwable) {
            return null;
        }
    }

    private function verifyPassword(string $password): bool
    {
        $hash = trim((string) env('ADMIN_LOGIN_PASSWORD_HASH', ''));
        if ($hash !== '') {
            return password_verify($password, $hash);
        }

        if ($this->requiresHashedPassword()) {
            return false;
        }

        $expected = (string) env('ADMIN_LOGIN_PASSWORD', '');
        if ($expected === '') {
            return false;
        }

        return hash_equals($expected, $password);
    }

    private function requiresHashedPassword(): bool
    {
        $env = strtolower((string) env('APP_ENV', 'prod'));
        $debugValue = env('APP_DEBUG', false);
        $debugEnabled = $debugValue === true || $debugValue === 1 || $debugValue === '1';

        return !in_array($env, ['local', 'dev', 'development', 'test'], true) && !$debugEnabled;
    }

    private function logFailedAttempt(string $email, string $reason, ?Request $request = null): void
    {
        try {
            $context = [
                'email' => $email,
                'reason' => $reason,
            ];

            if ($request !== null && method_exists($request, 'clientIp')) {
                $ip = $request->clientIp();
                if (is_string($ip) && $ip !== '') {
                    $context['ip'] = $ip;
                }
            }

            $app = app();
            if ($app->has(LoggerInterface::class)) {
                $logger = $app->make(LoggerInterface::class);
                if ($logger instanceof LoggerInterface) {
                    $logger->warning('admin.login.failed', $context);
                    return;
                }
            }
        } catch (Throwable) {
            // Ignore logging failures.
        }

        error_log('Admin login failed: ' . $reason);
    }

    private function emailMatches(string $expected, string $actual): bool
    {
        $expected = strtolower(trim($expected));
        $actual = strtolower(trim($actual));
        if ($expected === '' || $actual === '') {
            return false;
        }
        return hash_equals($expected, $actual);
    }

    private function shouldCaptureIntended(Request $request): bool
    {
        return strtoupper($request->getMethod()) === 'GET';
    }

    private function intendedPath(Request $request): string
    {
        $path = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery();
        if ($query !== '') {
            $path .= '?' . $query;
        }
        return $path !== '' ? $path : '/admin';
    }

    private function session(): ?SessionInterface
    {
        try {
            if (!interface_exists(SessionInterface::class)) {
                return null;
            }
            $app = app();
            if (!$app->has(SessionInterface::class)) {
                return null;
            }
            $session = $app->make(SessionInterface::class);
            return $session instanceof SessionInterface ? $session : null;
        } catch (Throwable) {
            return null;
        }
    }
}
