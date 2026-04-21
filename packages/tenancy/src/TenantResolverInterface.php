<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Tenancy;

use Finella\Support\Psr\Http\Message\ServerRequestInterface;

interface TenantResolverInterface
{
    public function resolve(ServerRequestInterface $request): ?string;
}
