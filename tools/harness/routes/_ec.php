<?php

declare(strict_types=1);

use Fnlla\Auth\Middleware\AuthMiddleware;
use Fnlla\Http\Router;
use App\Controllers\EcController;

return static function (Router $router): void {
    $router->get('/_ec/register', [EcController::class, 'registerForm'], null, ['web']);
    $router->post('/_ec/register', [EcController::class, 'registerSubmit'], null, ['web']);
    $router->get('/_ec/login', [EcController::class, 'loginForm'], null, ['web']);
    $router->post('/_ec/login', [EcController::class, 'loginSubmit'], null, ['web']);
    $router->get('/_ec/dashboard', [EcController::class, 'dashboard'], null, ['web', AuthMiddleware::class]);
    $router->get('/_ec/posts', [EcController::class, 'posts'], null, ['web']);
    $router->post('/_ec/welcome', [EcController::class, 'welcome'], null, ['web']);
    $router->get('/_ec/obs', [EcController::class, 'obs'], null, ['web']);
    $router->get('/_ec/scheduled-mark', [EcController::class, 'scheduledMark'], null, ['web']);
    $router->get('/_ec/back', [EcController::class, 'back'], null, ['web']);
    $router->post('/_ec/validate', [EcController::class, 'validate'], null, ['web']);
};
