<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Mail;

use Finella\Core\Container;
use Finella\Support\ServiceProvider;

final class MailServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        $app->singleton(MailManager::class, function () use ($app): MailManager {
            $config = $app->config()->get('mail', []);
            if (!is_array($config)) {
                $config = [];
            }

            return new MailManager($config);
        });

        $app->singleton(MailerInterface::class, function () use ($app): MailerInterface {
            return $app->make(MailManager::class)->mailer();
        });
    }
}
