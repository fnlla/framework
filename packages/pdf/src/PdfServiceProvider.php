<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Pdf;

use Finella\Core\Container;
use Finella\Support\ServiceProvider;

final class PdfServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        $app->singleton(PdfRendererInterface::class, function () use ($app): PdfRendererInterface {
            $config = $app->config()->get('pdf', []);
            if (!is_array($config)) {
                $config = [];
            }

            return new DompdfRenderer($config);
        });

        $app->singleton(PdfManager::class, function () use ($app): PdfManager {
            return new PdfManager($app->config(), $app->make(PdfRendererInterface::class));
        });
    }
}


