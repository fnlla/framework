<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Orm;

use Finella\Core\Container;
use Finella\Database\ConnectionManager;
use Finella\Support\ServiceProvider;

final class OrmServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        Model::setContainer($app);
    }

    public function boot(Container $app): void
    {
        if ($app->has(ConnectionManager::class)) {
            $manager = $app->make(ConnectionManager::class);
            if ($manager instanceof ConnectionManager) {
                Model::setConnectionManager($manager);
            }
        }
    }
}
