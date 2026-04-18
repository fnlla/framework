<?php

declare(strict_types=1);

use App\Controllers\AdminAuthController;
use App\Controllers\AdminPagesController;
use App\Controllers\DocsController;
use App\Controllers\ErrorController;
use App\Controllers\FormController;
use App\Controllers\HealthController;
use App\Controllers\HomeController;
use App\Controllers\OnboardingController;
use App\Controllers\ProviderReportController;
use App\Controllers\ReadinessController;
use App\Controllers\StatusController;
use App\Controllers\TestController;
use App\Middleware\AdminAccessMiddleware;
use App\Middleware\DocsAccessMiddleware;
use Finella\Auth\AuthRoutes;
use Finella\Auth\Middleware\AuthMiddleware;
use Finella\Http\Router;

return static function (Router $router): void {
    if (class_exists(HealthController::class)) {
        $router->get('/health', [HealthController::class, 'show']);
    }

    if (class_exists(ReadinessController::class)) {
        $router->get('/ready', [ReadinessController::class, 'show']);
    }

    if (class_exists(StatusController::class)) {
        $router->get('/status', [StatusController::class, 'show']);
    }

    if (class_exists(FormController::class)) {
        $router->get('/form', [FormController::class, 'show']);
        $router->post('/form', [FormController::class, 'submit'], null, ['web']);
    }

    if (class_exists(ProviderReportController::class)) {
        $router->get('/_providers', [ProviderReportController::class, 'show']);
    }

    if (class_exists(\Finella\Monitoring\MonitoringRoutes::class) && env('FINELLA_MONITORING_ENABLED', false)) {
        \Finella\Monitoring\MonitoringRoutes::register($router, [
            'path' => (string) env('FINELLA_MONITORING_PATH', '/metrics'),
        ]);
    }

    if (class_exists(AuthRoutes::class)) {
        AuthRoutes::register($router, [
            'prefix' => '/auth',
            'middleware' => ['web'],
        ]);
    }

    $docsEnabled = (bool) env('FINELLA_DOCS_ENABLED', false);
    if ($docsEnabled && class_exists(DocsController::class)) {
        $docsMiddleware = ['web'];
        if (class_exists(DocsAccessMiddleware::class)) {
            $docsMiddleware[] = DocsAccessMiddleware::class;
        }
        $router->group(['middleware' => $docsMiddleware], function (Router $router): void {
            $router->get('/docs', [DocsController::class, 'index']);
            $router->get('/docs/{slug:.+}', [DocsController::class, 'show']);
        });
    }

    $adminRoutesEnabled = (bool) env('FINELLA_ADMIN_ENABLED', false);

    if ($adminRoutesEnabled && class_exists(AdminAuthController::class)) {
        $router->group(['prefix' => '/admin', 'middleware' => ['web']], function (Router $router): void {
            $router->get('/login', [AdminAuthController::class, 'loginForm']);
            $router->post('/login', [AdminAuthController::class, 'loginSubmit'], null, ['rate:5,1,ip']);
            $router->post('/logout', [AdminAuthController::class, 'logout']);
        });
    }

    if ($adminRoutesEnabled && class_exists(AdminPagesController::class) && class_exists(AdminAccessMiddleware::class)) {
        $router->group(['prefix' => '/admin', 'middleware' => ['web', AdminAccessMiddleware::class]], function (Router $router): void {
            $router->get('', [AdminPagesController::class, 'index']);
            $router->get('/analytics', [AdminPagesController::class, 'analytics']);
            $router->get('/audit', [AdminPagesController::class, 'audit']);
            $router->get('/settings', [AdminPagesController::class, 'settings']);
        });
    }

    if (strtolower((string) getenv('APP_ENV')) === 'test' && class_exists(TestController::class)) {
        $router->post('/_test/validate', [TestController::class, 'validate'], null, ['web']);
        if (class_exists(AuthMiddleware::class)) {
            $router->get('/_test/protected', [TestController::class, 'protected'], null, ['web', AuthMiddleware::class]);
        }
        $router->post('/_test/mail', [TestController::class, 'mail'], null, ['web']);
        $router->post('/_test/queue', [TestController::class, 'queue'], null, ['web']);

        $ecRoutes = __DIR__ . '/_ec.php';
        if (is_file($ecRoutes)) {
            $loaded = require $ecRoutes;
            if (is_callable($loaded)) {
                $loaded($router);
            }
        }
    }

    $router->group(['middleware' => ['web']], function (Router $router): void {
        if (class_exists(OnboardingController::class)) {
            $router->get('/onboarding', [OnboardingController::class, 'show']);
            $router->post('/onboarding', [OnboardingController::class, 'submit']);
        }

        if (class_exists(HomeController::class)) {
            $router->get('/', [HomeController::class, 'index']);
        }

        if (class_exists(ErrorController::class)) {
            $router->get('/{path:.+}', [ErrorController::class, 'notFound']);
        }
    });
};
