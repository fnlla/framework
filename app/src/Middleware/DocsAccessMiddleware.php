<?php

declare(strict_types=1);

namespace App\Middleware;

use Finella\Http\Request;
use Finella\Http\Response;

final class DocsAccessMiddleware
{
    public function __invoke(Request $request, callable $next): Response
    {
        if ($this->isDevEnvironment()) {
            return $next($request);
        }

        if ($this->isPublicDocs()) {
            return $next($request);
        }

        $token = trim((string) env('DOCS_ACCESS_TOKEN', ''));
        if ($token === '') {
            return Response::html('Not Found', 404);
        }

        $provided = $this->resolveToken($request);
        if ($provided === '' || !hash_equals($token, $provided)) {
            return Response::html('Not Found', 404);
        }

        return $next($request);
    }

    private function isPublicDocs(): bool
    {
        $flag = getenv('DOCS_PUBLIC');
        if ($flag === false) {
            return false;
        }

        return $this->isTruthy($flag);
    }

    private function resolveToken(Request $request): string
    {
        $query = $request->getQueryParams();
        $token = trim((string) ($query['docs_token'] ?? ''));
        if ($token !== '') {
            return $token;
        }

        $header = $request->header('X-Docs-Token', '');
        return trim((string) $header);
    }

    private function isDevEnvironment(): bool
    {
        $envValue = strtolower((string) env('APP_ENV', 'prod'));
        $debugValue = strtolower((string) env('APP_DEBUG', '0'));
        return in_array($envValue, ['local', 'dev', 'development', 'test'], true)
            || in_array($debugValue, ['1', 'true', 'yes'], true);
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
