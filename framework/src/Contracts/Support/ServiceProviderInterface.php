<?php

/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Contracts\Support;

use Finella\Core\Container;

/**
 * @api
 */
interface ServiceProviderInterface
{
    public function register(Container $app): void;

    public function boot(Container $app): void;

    public static function manifest(): \Finella\Support\ProviderManifest;
}

