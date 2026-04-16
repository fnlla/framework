<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\SecurityHeaders;

use Finella\Core\ConfigRepository;
use Finella\Http\Request;
use Finella\Http\Response;
use Finella\Runtime\RequestContext;
use Finella\Support\Psr\Http\Message\ResponseInterface;
use Finella\Support\Psr\Http\Message\ServerRequestInterface;
use Finella\Support\Psr\Http\Server\MiddlewareInterface;
use Finella\Support\Psr\Http\Server\RequestHandlerInterface;

final class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function __construct(private ConfigRepository $config, private RequestContext $context)
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
        $response = $next($request);
        if ($response instanceof Response) {
            // keep as-is
        } elseif ($response instanceof ResponseInterface) {
            $response = new Response(
                $response->getStatusCode(),
                $response->getHeaders(),
                \Finella\Http\Stream::fromString((string) $response->getBody()),
                $response->getReasonPhrase()
            );
        } else {
            $response = Response::html((string) $response);
        }

        $config = $this->config->get('security', []);
        if (!is_array($config)) {
            $config = [];
        }

        $headers = $this->defaultHeaders();
        $customHeaders = $config['headers'] ?? [];
        if (is_array($customHeaders)) {
            foreach ($customHeaders as $name => $value) {
                $headers[$name] = $value;
            }
        }

        $setHeaders = [];
        foreach ($headers as $name => $value) {
            if ($value === null) {
                continue;
            }
            if (!$response->hasHeader($name)) {
                $setHeaders[$name] = (string) $value;
            }
        }

        $csp = $config['csp'] ?? null;
        if ($csp !== null && !$response->hasHeader('Content-Security-Policy')) {
            $nonce = $this->context->cspNonce();
            $cspValue = str_contains((string) $csp, '%s') ? sprintf((string) $csp, $nonce) : (string) $csp;
            $setHeaders['Content-Security-Policy'] = $cspValue;
        }

        if ($setHeaders === []) {
            return $response;
        }

        return $response->withHeaders($setHeaders);
    }

    private function defaultHeaders(): array
    {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ];
    }
}
