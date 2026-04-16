<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Csrf;

use Finella\Core\Container;
use Finella\Session\SessionInterface;
use Finella\Support\ServiceProvider;

final class CsrfServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        $app->singleton(CsrfTokenManager::class, function () use ($app): CsrfTokenManager {
            $session = $app->make(SessionInterface::class);
            return new CsrfTokenManager($session);
        });
    }
}
