<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Analytics;

use Finella\Contracts\Log\LoggerInterface;
use Finella\Core\ConfigRepository;
use Finella\Core\Container;
use Finella\Support\ServiceProvider;

final class AnalyticsServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        $app->singleton(AnalyticsClient::class, function () use ($app): AnalyticsClient {
            $logger = null;
            $enabled = true;
            if ($app->has(ConfigRepository::class)) {
                $resolvedConfig = $app->make(ConfigRepository::class);
                if ($resolvedConfig instanceof ConfigRepository) {
                    $enabled = (bool) $resolvedConfig->get('analytics.enabled', true);
                }
            }

            if ($enabled && $app->has(LoggerInterface::class)) {
                $resolved = $app->make(LoggerInterface::class);
                if ($resolved instanceof LoggerInterface) {
                    $logger = $resolved;
                }
            }
            return new AnalyticsClient($logger);
        });
    }
}
