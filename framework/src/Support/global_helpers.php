<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

if (!function_exists('app')) {
    function app(): \Finella\Core\Container
    {
        $app = $GLOBALS['finella_app'] ?? null;
        if (!$app instanceof \Finella\Core\Container) {
            throw new RuntimeException(
                'Finella application container not initialized. Ensure bootstrap/app.php sets $GLOBALS[\'finella_app\'].'
            );
        }
        return $app;
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = [], ?string $layout = null): \Finella\Http\Response
    {
        $app = app();
        if (function_exists('view_render')) {
            $path = function_exists('view_path') ? view_path($template) : '';
            if ($path !== '' && is_file($path)) {
                $html = view_render($template, $data, $layout);
                return \Finella\Http\Response::html($html);
            }
        }

        $html = \Finella\View\View::render($app, $template, $data, $layout);
        return \Finella\Http\Response::html($html);
    }
}

if (!function_exists('view_path')) {
    function view_path(string $template): string
    {
        $app = app();
        $config = $app->configRepository();
        $default = defined('APP_ROOT')
            ? APP_ROOT . '/resources/views'
            : getcwd() . '/resources/views';
        $viewsPath = rtrim((string) $config->get('views_path', $default), '/');
        return $viewsPath . '/' . ltrim($template, '/') . '.php';
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return \Finella\Support\absolute_url(app(), $path);
    }
}

if (!function_exists('site_url')) {
    function site_url(): string
    {
        return \Finella\Support\site_url(app());
    }
}

if (!function_exists('absolute_url')) {
    function absolute_url(string $path = ''): string
    {
        return \Finella\Support\absolute_url(app(), $path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return \Finella\Support\asset(app(), $path);
    }
}

if (!function_exists('route')) {
    function route(string $name, array $params = [], bool $absolute = false): string
    {
        $app = app();
        $router = $app->make(\Finella\Http\Router::class);
        if (!$router instanceof \Finella\Http\Router) {
            return '';
        }
        $path = $router->url($name, $params);
        if (!$absolute) {
            return $path;
        }
        return \Finella\Support\absolute_url($app, $path);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        $app = app();
        if (!class_exists(\Finella\Csrf\CsrfTokenManager::class) || !interface_exists(\Finella\Session\SessionInterface::class)) {
            throw new RuntimeException('CSRF support is not available. Ensure the core CSRF and Session modules are enabled.');
        }
        $session = $app->make(\Finella\Session\SessionInterface::class);
        if (!$session instanceof \Finella\Session\SessionInterface) {
            throw new RuntimeException('Session service is not available.');
        }
        $manager = new \Finella\Csrf\CsrfTokenManager($session);
        return $manager->token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('errors')) {
    function errors(string $bag = 'default'): \Finella\Support\ErrorBag
    {
        static $cached = null;
        static $cachedBag = null;

        if ($cached === null) {
            $app = app();
            if (!interface_exists(\Finella\Session\SessionInterface::class)) {
                return new \Finella\Support\ErrorBag([], $bag);
            }
            $session = $app->make(\Finella\Session\SessionInterface::class);
            if (!$session instanceof \Finella\Session\SessionInterface) {
                return new \Finella\Support\ErrorBag([], $bag);
            }
            if (method_exists($session, 'getFlash')) {
                $cached = $session->getFlash('_finella_errors', []);
                $cachedBag = $session->getFlash('_finella_error_bag', 'default');
            } else {
                $cached = $session->get('_finella_errors', []);
                $cachedBag = $session->get('_finella_error_bag', 'default');
            }
        }

        if ($cachedBag !== $bag) {
            return new \Finella\Support\ErrorBag([], $bag);
        }

        $errors = is_array($cached) ? $cached : [];
        return new \Finella\Support\ErrorBag($errors, $bag);
    }
}

if (!function_exists('old')) {
    function old(?string $key = null, mixed $default = null): mixed
    {
        static $cached = null;

        if ($cached === null) {
            $app = app();
            if (!interface_exists(\Finella\Session\SessionInterface::class)) {
                return $default;
            }
            $session = $app->make(\Finella\Session\SessionInterface::class);
            if (!$session instanceof \Finella\Session\SessionInterface) {
                return $default;
            }
            if (method_exists($session, 'getFlash')) {
                $cached = $session->getFlash('_finella_old', []);
            } else {
                $cached = $session->get('_finella_old', []);
            }
        }

        if ($key === null) {
            return $cached;
        }

        if (!is_array($cached) || !array_key_exists($key, $cached)) {
            return $default;
        }

        return $cached[$key];
    }
}

if (!function_exists('redirect')) {
    function redirect(string $to, int $status = 302): \Finella\Http\Response
    {
        return \Finella\Http\Response::redirect($to, $status);
    }
}

if (!function_exists('back')) {
    function back(int $status = 302): \Finella\Http\Response
    {
        $req = null;
        if (isset($GLOBALS['finella_app']) && $GLOBALS['finella_app'] instanceof \Finella\Core\Container) {
            $app = $GLOBALS['finella_app'];
            if ($app->has(\Finella\Http\Request::class)) {
                $resolved = $app->make(\Finella\Http\Request::class);
                if ($resolved instanceof \Finella\Http\Request) {
                    $req = $resolved;
                }
            }
        }
        if (!$req instanceof \Finella\Http\Request) {
            $target = '/';
        } else {
            $target = \Finella\Http\RedirectTarget::fromReferer($req, '/');
        }
        return \Finella\Http\Response::redirect($target, $status);
    }
}

if (!function_exists('can')) {
    function can(string $ability, mixed $target = null, ?\Finella\Http\Request $request = null): bool
    {
        $app = app();
        if (!class_exists(\Finella\Authorization\Gate::class)) {
            return false;
        }

        $gate = $app->has(\Finella\Authorization\Gate::class)
            ? $app->make(\Finella\Authorization\Gate::class)
            : new \Finella\Authorization\Gate($app, new \Finella\Authorization\PolicyRegistry());

        if (!$gate instanceof \Finella\Authorization\Gate) {
            return false;
        }

        return $gate->allows($ability, $target, $request);
    }
}
