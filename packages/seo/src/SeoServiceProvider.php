<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Seo;

use Finella\Core\ConfigRepository;
use Finella\Core\Container;
use Finella\Support\ServiceProvider;

final class SeoServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        $app->singleton(SeoManager::class, function () use ($app): SeoManager {
            $defaults = [];
            if ($app->has(ConfigRepository::class)) {
                $resolved = $app->make(ConfigRepository::class);
                if ($resolved instanceof ConfigRepository) {
                    $defaults = $resolved->get('seo.defaults', []);
                }
            }
            if (!is_array($defaults)) {
                $defaults = [];
            }
            return new SeoManager($defaults);
        });
    }
}
