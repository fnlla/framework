<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$appDir = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
$autoload = $appDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing tools/harness/vendor/autoload.php. Run composer install in tools/harness.\n");
    exit(1);
}

require $autoload;

if (!class_exists(\Finella\Rbac\RbacServiceProvider::class)) {
    $rbacBase = $root . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'rbac' . DIRECTORY_SEPARATOR . 'src';
    require_once $rbacBase . DIRECTORY_SEPARATOR . 'RbacSchema.php';
    require_once $rbacBase . DIRECTORY_SEPARATOR . 'RbacManager.php';
    require_once $rbacBase . DIRECTORY_SEPARATOR . 'RbacServiceProvider.php';
}

use Finella\Authorization\AuthorizationException;
use Finella\Authorization\Gate;
use Finella\Core\ConfigRepository;
use Finella\Core\Application;
use Finella\Database\ConnectionManager;
use Finella\Rbac\RbacManager;
use Finella\Rbac\RbacServiceProvider;
use Finella\Http\Router;
use Finella\Http\Request;
use Finella\Http\Response;
use Finella\Http\Uri;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

final class Post
{
    public int $user_id = 2;
}

final class PostPolicy
{
    public function update($user, Post $post): bool
    {
        return $user !== null && ($user['id'] ?? null) === $post->user_id;
    }
}

$config = new ConfigRepository([
    'authorization' => [
        'policies' => [
            Post::class => PostPolicy::class,
        ],
        'guess' => true,
    ],
    'rbac' => [
        'auto_migrate' => true,
        'cache_ttl' => 0,
    ],
]);

$app = new Application($root, $config);
$app->singleton(ConnectionManager::class, function (): ConnectionManager {
    return new ConnectionManager([
        'driver' => 'sqlite',
        'path' => ':memory:',
    ]);
});

$gate = $app->make(Gate::class);
ok($gate instanceof Gate, 'Gate resolved');

// Gate define + can helper.
$gate->define('public', fn () => true);
$GLOBALS['finella_app'] = $app;
ok(can('public') === true, 'can() helper uses Gate defined ability');

// Policy denies.
$user = ['id' => 1];
$post = new Post();
ok($gate->allows('update', $post, null, $user) === false, 'Policy blocks action');

try {
    $gate->authorize('update', $post, null, $user);
    ok(false, 'authorize should throw for denied policy');
} catch (AuthorizationException $e) {
    ok($e->status() === 403, 'AuthorizationException status is 403');
}

// RBAC: admin role allows.
$rbacProvider = new RbacServiceProvider($app);
$rbacProvider->register($app);
$rbacProvider->boot($app);

$rbac = $app->make(RbacManager::class);
ok($rbac instanceof RbacManager, 'RbacManager resolved');

$rbac->ensureSchema();
$rbac->assignRole(1, 'admin');
$rbac->grantPermissionToRole('admin', 'posts.update');

ok($gate->allows('role', 'admin', null, $user) === true, 'admin role passes');
ok($gate->allows('permission', 'posts.update', null, $user) === true, 'admin permission passes');

// Middleware can: JSON should throw 403, HTML should redirect back.
$router = new Router('', $app);
$router->get('/posts/{post}', fn () => Response::text('ok'), null, ['can:update,post']);

$jsonRequest = new Request('GET', new Uri('http://localhost/posts/1'), ['Accept' => 'application/json']);
$caught = null;
try {
    $router->dispatch($jsonRequest);
} catch (AuthorizationException $e) {
    $caught = $e;
}
ok($caught instanceof AuthorizationException, 'can middleware throws for JSON');
ok($caught->status() === 403, 'can middleware JSON status 403');

$htmlRequest = new Request(
    'GET',
    new Uri('http://localhost/posts/1'),
    ['Accept' => 'text/html', 'Referer' => 'http://localhost/posts/1']
);
$response = $router->dispatch($htmlRequest);
ok($response->getStatusCode() === 302, 'can middleware redirects for HTML');
ok($response->getHeaderLine('Location') !== '', 'redirect has Location');

echo "Authorization smoke tests OK\n";

