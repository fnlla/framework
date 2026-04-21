<?php

/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Http\Middleware;

use Finella\Http\Request;
use Finella\Http\Response;
use Finella\Support\Psr\Http\Message\ResponseInterface;
use Finella\Support\Psr\Http\Message\ServerRequestInterface;
use Finella\Support\Psr\Http\Server\MiddlewareInterface;
use Finella\Support\Psr\Http\Server\RequestHandlerInterface;

final class TrustedProxyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request instanceof Request) {
            $request = $request
                ->withAttribute('client_ip', $request->clientIp())
                ->withAttribute('is_secure', $request->isSecure());
        }

        return $handler->handle($request);
    }

    public function __invoke(Request $request, callable $next): Response
    {
        $request = $request
            ->withAttribute('client_ip', $request->clientIp())
            ->withAttribute('is_secure', $request->isSecure());

        return $next($request);
    }
}






