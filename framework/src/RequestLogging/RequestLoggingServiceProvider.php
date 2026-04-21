<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\RequestLogging;

use Finella\Core\Container;
use Finella\Support\ServiceProvider;

final class RequestLoggingServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        // No bindings required; middleware can be resolved directly via the container.
    }
}
