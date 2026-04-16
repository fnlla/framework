<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Cookie;

use Finella\Support\ServiceProvider;

final class CookieServiceProvider extends ServiceProvider
{
    public function register(\Finella\Core\Container $app): void
    {
        $app->singleton(CookieJar::class, fn (): CookieJar => new CookieJar());
    }
}
