<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Auth\Middleware;

use Finella\Auth\AuthManager;
use Finella\Http\Request;
use Finella\Http\Response;
use Finella\Support\Psr\Http\Message\ResponseInterface;
use Finella\Support\Psr\Http\Message\ServerRequestInterface;
use Finella\Support\Psr\Http\Server\MiddlewareInterface;
use Finella\Support\Psr\Http\Server\RequestHandlerInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private AuthManager $auth)
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
        $user = $this->auth->user($request instanceof Request ? $request : null);

        if ($user === null) {
            if ($request instanceof Request && $request->wantsJson()) {
                return Response::json(['message' => 'Unauthorized'], 401);
            }
            return Response::html('Unauthorized', 401);
        }

        return $next($request);
    }
}
