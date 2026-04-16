<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Cors;

use Finella\Core\ConfigRepository;
use Finella\Http\Request;
use Finella\Http\Response;
use Finella\Support\Psr\Http\Message\ResponseInterface;
use Finella\Support\Psr\Http\Message\ServerRequestInterface;
use Finella\Support\Psr\Http\Server\MiddlewareInterface;
use Finella\Support\Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(private ConfigRepository $config)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->handle($request, fn ($req): ResponseInterface => $handler->handle($req));
    }

    public function __invoke(Request $request, callable $next): ResponseInterface
    {
        return $this->handle($request, $next);
    }

    private function handle(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $config = $this->config->get('cors', []);
        if (!is_array($config)) {
            $config = [];
        }

        $enabled = $config['enabled'] ?? true;
        if ($enabled === false || $enabled === 0 || $enabled === '0') {
            return $next($request);
        }

        $origin = trim((string) $request->getHeaderLine('Origin'));
        $headers = $this->resolveHeaders($config, $origin, $request);
        if ($headers === []) {
            return $next($request);
        }

        $isPreflight = strtoupper($request->getMethod()) === 'OPTIONS'
            && $request->getHeaderLine('Access-Control-Request-Method') !== '';

        if ($isPreflight) {
            return Response::text('', 204, $headers);
        }

        $response = $next($request);
        return $this->applyHeaders($response, $headers);
    }

    private function resolveHeaders(array $config, string $origin, ServerRequestInterface $request): array
    {
        $allowedOrigins = $config['allowed_origins'] ?? ['*'];
        if (!is_array($allowedOrigins)) {
            $allowedOrigins = [$allowedOrigins];
        }

        $allowOrigin = null;
        if ($origin !== '') {
            if (in_array('*', $allowedOrigins, true)) {
                $allowOrigin = '*';
            } elseif (in_array($origin, $allowedOrigins, true)) {
                $allowOrigin = $origin;
            }
        }

        if ($allowOrigin === null) {
            return [];
        }

        $allowCredentials = (bool) ($config['allow_credentials'] ?? false);
        if ($allowCredentials && $allowOrigin === '*' && $origin !== '') {
            $allowOrigin = $origin;
        }

        $allowedMethods = $config['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        if (!is_array($allowedMethods)) {
            $allowedMethods = [$allowedMethods];
        }

        $allowedHeaders = $config['allowed_headers'] ?? ['Content-Type', 'Authorization', 'X-Requested-With'];
        if (!is_array($allowedHeaders)) {
            $allowedHeaders = [$allowedHeaders];
        }

        $requestHeaders = trim((string) $request->getHeaderLine('Access-Control-Request-Headers'));
        if ($requestHeaders !== '' && ($config['allowed_headers'] ?? null) === null) {
            $allowedHeaders = array_map('trim', explode(',', $requestHeaders));
        }

        $exposedHeaders = $config['exposed_headers'] ?? [];
        if (!is_array($exposedHeaders)) {
            $exposedHeaders = [$exposedHeaders];
        }

        $maxAge = $config['max_age'] ?? null;

        $headers = [
            'Access-Control-Allow-Origin' => $allowOrigin,
            'Access-Control-Allow-Methods' => implode(', ', array_map('strtoupper', $allowedMethods)),
            'Access-Control-Allow-Headers' => implode(', ', $allowedHeaders),
        ];

        if ($allowCredentials) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }
        if ($exposedHeaders !== []) {
            $headers['Access-Control-Expose-Headers'] = implode(', ', $exposedHeaders);
        }
        if ($maxAge !== null && $maxAge !== '') {
            $headers['Access-Control-Max-Age'] = (string) $maxAge;
        }
        if ($allowOrigin !== '*') {
            $headers['Vary'] = 'Origin';
        }

        return $headers;
    }

    private function applyHeaders(ResponseInterface $response, array $headers): ResponseInterface
    {
        foreach ($headers as $name => $value) {
            $updated = $response->withHeader($name, (string) $value);
            if (!$updated instanceof ResponseInterface) {
                return $response;
            }
            $response = $updated;
        }

        return $response;
    }
}
