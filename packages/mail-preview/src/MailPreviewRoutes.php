<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\MailPreview;

use Finella\Http\Router;
use Finella\MailPreview\Http\MailPreviewController;

final class MailPreviewRoutes
{
    public static function register(Router $router, array $options = []): void
    {
        $prefix = (string) ($options['prefix'] ?? '/mail/preview');
        $middleware = $options['middleware'] ?? [];
        if (!is_array($middleware)) {
            $middleware = [$middleware];
        }

        $router->group(['prefix' => $prefix, 'middleware' => $middleware], function (Router $router): void {
            $router->get('', [MailPreviewController::class, 'show'], 'mail.preview');
        });
    }
}
