<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Monitoring;

use Finella\Core\Container;
use Finella\Support\ServiceProvider;

final class MonitoringServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        $app->singleton(MonitoringManager::class, function () use ($app): MonitoringManager {
            $config = $app->config()->get('monitoring', []);
            if (!is_array($config)) {
                $config = [];
            }
            $cache = $app->make(\Finella\Cache\CacheManager::class);
            return new MonitoringManager($cache, $config);
        });
    }
}
