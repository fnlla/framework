<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\MailPreview;

use Finella\Core\Container;
use Finella\Support\ServiceProvider;

final class MailPreviewServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        // Routes are registered manually via MailPreviewRoutes.
    }
}
