<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Tenancy;

use Finella\Core\ConfigRepository;
use Finella\Http\Request;
use Finella\Http\Response;
use Finella\Support\Psr\Http\Message\ResponseInterface;
use Finella\Support\Psr\Http\Message\ServerRequestInterface;
use Finella\Support\Psr\Http\Server\MiddlewareInterface;
use Finella\Support\Psr\Http\Server\RequestHandlerInterface;

final class TenantMiddleware implements MiddlewareInterface
{
    public function __construct(
        private TenantResolverInterface $resolver,
        private ConfigRepository $config
    ) {
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
        $config = $this->config->get('tenancy', []);
        if (!is_array($config)) {
            $config = [];
        }

        if (!(bool) ($config['enabled'] ?? false)) {
            return $next($request);
        }

        $tenantId = $this->resolver->resolve($request);
        $tenantId = is_string($tenantId) ? trim($tenantId) : '';

        if ($tenantId === '') {
            if ((bool) ($config['required'] ?? false)) {
                $status = (int) ($config['required_status'] ?? 400);
                $message = (string) ($config['required_message'] ?? 'Tenant identifier required.');
                return Response::json(['message' => $message], $status);
            }

            return $next($request);
        }

        TenantContext::setId($tenantId);

        $attribute = (string) ($config['attribute'] ?? 'tenant_id');
        if ($attribute !== '' && method_exists($request, 'withAttribute')) {
            $request = $request->withAttribute($attribute, $tenantId);
        }

        try {
            return $next($request);
        } finally {
            TenantContext::clear();
        }
    }
}
