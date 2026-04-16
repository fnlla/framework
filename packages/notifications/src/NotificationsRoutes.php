<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Notifications;

use Finella\Http\Router;
use Finella\Notifications\Http\NotificationsController;

final class NotificationsRoutes
{
    public static function register(Router $router, array $options = []): void
    {
        $prefix = (string) ($options['prefix'] ?? '/api/notifications');
        $middleware = $options['middleware'] ?? [];
        if (!is_array($middleware)) {
            $middleware = [$middleware];
        }

        $router->group(['prefix' => $prefix, 'middleware' => $middleware], function (Router $router): void {
            $router->get('/', [NotificationsController::class, 'index'], 'notifications.index');
            $router->get('/{id}', [NotificationsController::class, 'show'], 'notifications.show');
            $router->post('/send', [NotificationsController::class, 'send'], 'notifications.send');
        });
    }
}


