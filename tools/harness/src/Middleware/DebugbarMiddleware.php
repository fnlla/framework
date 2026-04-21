<?php
/**
 * fnlla (finella) - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace App\Middleware;

use Finella\Debugbar\Middleware\DebugbarMiddleware as PackageDebugbarMiddleware;
use Finella\Support\Psr\Http\Message\ResponseInterface;
use Finella\Support\Psr\Http\Message\ServerRequestInterface;
use Finella\Support\Psr\Http\Server\MiddlewareInterface;
use Finella\Support\Psr\Http\Server\RequestHandlerInterface;

final class DebugbarMiddleware implements MiddlewareInterface
{
    private PackageDebugbarMiddleware $delegate;

    public function __construct()
    {
        $this->delegate = new PackageDebugbarMiddleware();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->delegate->process($request, $handler);
    }
}
