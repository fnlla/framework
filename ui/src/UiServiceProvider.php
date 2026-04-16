<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Ui;

use Finella\Core\Container;
use Finella\Support\ServiceProvider;

final class UiServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        // UI package is asset + template only. No runtime services yet.
    }
}
