<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Rbac;

use Finella\Authorization\Gate;
use Finella\Core\Container;
use Finella\Database\ConnectionManager;
use Finella\Support\ServiceProvider;
use Finella\Cache\CacheManager;

final class RbacServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        $app->singleton(RbacManager::class, function () use ($app): RbacManager {
            $config = $app->config()->get('rbac', []);
            if (!is_array($config)) {
                $config = [];
            }

            $cache = null;
            if (class_exists(CacheManager::class) && $app->has(CacheManager::class)) {
                $cache = $app->make(CacheManager::class);
                if (!$cache instanceof CacheManager) {
                    $cache = null;
                }
            }

            $connections = $app->make(ConnectionManager::class);
            return new RbacManager($connections, $cache, $config);
        });
    }

    public function boot(Container $app): void
    {
        if (!$app->has(Gate::class) || !$app->has(RbacManager::class)) {
            return;
        }

        $gate = $app->make(Gate::class);
        $rbac = $app->make(RbacManager::class);
        if (!$gate instanceof Gate || !$rbac instanceof RbacManager) {
            return;
        }

        $gate->define('role', function ($user, $role) use ($rbac): bool {
            if (!is_string($role) || $role === '') {
                return false;
            }
            return $rbac->hasRole($user, $role);
        });

        $gate->define('permission', function ($user, $permission) use ($rbac): bool {
            if (!is_string($permission) || $permission === '') {
                return false;
            }
            return $rbac->hasPermission($user, $permission);
        });
    }
}
