<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AdminAuthService;
use Finella\Http\Request;
use Finella\Http\Response;

final class AdminAccessMiddleware
{
    public function __invoke(Request $request, callable $next): Response
    {
        $path = $request->getUri()->getPath();
        if ($this->isBypassPath($path)) {
            return $next($request);
        }

        $service = new AdminAuthService();
        if (!$service->requiresLogin()) {
            return $next($request);
        }

        if (!$service->isConfigured() && $this->allowUnconfigured()) {
            return $next($request);
        }

        if ($service->isAuthenticated($request)) {
            return $next($request);
        }

        $service->rememberIntended($request);
        return Response::redirect('/admin/login');
    }

    private function isBypassPath(string $path): bool
    {
        return str_starts_with($path, '/admin/login')
            || str_starts_with($path, '/admin/logout');
    }

    private function isDevEnvironment(): bool
    {
        $envValue = strtolower((string) env('APP_ENV', 'prod'));
        $debugValue = strtolower((string) env('APP_DEBUG', '0'));
        return in_array($envValue, ['local', 'dev', 'development', 'test'], true)
            || in_array($debugValue, ['1', 'true', 'yes'], true);
    }

    private function allowUnconfigured(): bool
    {
        $flag = getenv('ADMIN_ALLOW_UNCONFIGURED');
        if ($flag === false) {
            return false;
        }

        return $this->isDevEnvironment() && $this->isTruthy($flag);
    }

    private function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
}
