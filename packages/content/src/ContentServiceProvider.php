<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Content;

use Finella\Core\ConfigRepository;
use Finella\Core\Container;
use Finella\Support\ServiceProvider;

final class ContentServiceProvider extends ServiceProvider
{
    public function register(Container $app): void
    {
        $config = $app->has(ConfigRepository::class) ? $app->make(ConfigRepository::class) : null;
        $root = method_exists($app, 'basePath') ? (string) $app->basePath() : ConfigRepository::resolveAppRoot();
        $path = $config instanceof ConfigRepository ? $config->get('content.path', 'content') : 'content';
        if (!is_string($path) || $path === '') {
            $path = 'content';
        }
        if (!str_starts_with($path, '/') && !preg_match('/^[A-Za-z]:\\\\/', $path)) {
            $path = rtrim($root, '/\\') . '/' . $path;
        }

        $app->singleton(ContentRepository::class, fn (): ContentRepository => new ContentRepository($path));
    }
}
