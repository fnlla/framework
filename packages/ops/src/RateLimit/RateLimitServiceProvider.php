<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\RateLimit;

use Finella\Cache\CacheManager;
use Finella\Core\Container;
use Finella\Support\ServiceProvider;

final class RateLimitServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        $app->singleton(RateLimiter::class, function () use ($app): RateLimiter {
            $cache = $app->make(CacheManager::class);
            return new RateLimiter($cache);
        });
    }
}
