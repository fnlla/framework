<?php

/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Support;

use Finella\Core\Container;
use Finella\Contracts\Support\ServiceProviderInterface;
use Finella\Support\ProviderManifest;

abstract class ServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected Container $app)
    {
    }

    public function register(Container $app): void
    {
    }

    public function boot(Container $app): void
    {
    }

    public static function manifest(): ProviderManifest
    {
        return new ProviderManifest(static::class, [], [], []);
    }
}
