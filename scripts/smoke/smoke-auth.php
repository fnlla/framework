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

use Finella\Auth\AuthManager;
use Finella\Auth\CallableUserProvider;
use Finella\Auth\PasswordHasher;
use Finella\Auth\PasswordResetManager;
use Finella\Auth\PasswordResetStore;
use Finella\Auth\RememberCookie;
use Finella\Auth\RememberTokenStore;
use Finella\Core\ConfigRepository;
use Finella\Csrf\CsrfMiddleware;
use Finella\Csrf\CsrfTokenManager;
use Finella\Http\Request;
use Finella\Http\RequestHandler;
use Finella\Http\Response;
use Finella\Http\Uri;
use Finella\Session\FileSessionStore;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'finella_auth_smoke_' . bin2hex(random_bytes(4));
@mkdir($tmp, 0755, true);

$sessionPath = $tmp . DIRECTORY_SEPARATOR . 'sessions';
$rememberPath = $tmp . DIRECTORY_SEPARATOR . 'remember';
$resetPath = $tmp . DIRECTORY_SEPARATOR . 'resets';

$session = new FileSessionStore($sessionPath, 3600);
$session->start();

$config = new ConfigRepository([
    'auth' => [
        'guard' => 'session',
        'session_key' => '_auth_user',
        'remember' => [
            'enabled' => true,
            'store' => $rememberPath,
            'lifetime' => 3600,
            'cookie' => 'remember_token',
        ],
        'reset' => [
            'enabled' => true,
            'ttl' => 3600,
            'store' => $resetPath,
        ],
        'password' => [
            'driver' => 'bcrypt',
            'bcrypt' => ['cost' => 4],
        ],
    ],
]);

$hasher = new PasswordHasher($config);

$users = [];
$nextId = 1;

$providerConfig = [
    'by_id' => function ($id) use (&$users) {
        return $users[$id] ?? null;
    },
    'by_credentials' => function (array $credentials) use (&$users) {
        $email = $credentials['email'] ?? null;
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    },
    'validate' => function (array $user, array $credentials) use ($hasher): bool {
        return $hasher->verify((string) ($credentials['password'] ?? ''), (string) ($user['password'] ?? ''));
    },
    'create' => function (array $data) use (&$users, &$nextId) {
        $id = $nextId++;
        $users[$id] = [
            'id' => $id,
            'email' => (string) ($data['email'] ?? ''),
            'name' => (string) ($data['name'] ?? ''),
            'password' => (string) ($data['password'] ?? ''),
        ];
        return $users[$id];
    },
    'update_password' => function (array $user, string $hash) use (&$users): void {
        $id = $user['id'] ?? null;
        if ($id !== null && isset($users[$id])) {
            $users[$id]['password'] = $hash;
        }
    },
];

$config->set('auth', array_merge($config->get('auth', []), [
    'provider' => $providerConfig,
]));

$provider = new CallableUserProvider(
    $providerConfig['by_id'],
    null,
    $providerConfig['by_credentials'],
    $providerConfig['validate'],
    $providerConfig['create'],
    $providerConfig['update_password']
);

$auth = new AuthManager(
    $config,
    $session,
    $hasher,
    new RememberTokenStore($rememberPath, 3600),
    new RememberCookie($config)
);

// Registration
$user = $auth->register([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'secret123',
]);
ok(is_array($user) && ($user['id'] ?? null) !== null, 'registration creates a user');

// Login
$result = $auth->attempt([
    'email' => 'test@example.com',
    'password' => 'secret123',
], true);
ok($result->authenticated === true, 'login succeeds');
ok(is_string($result->rememberToken) && $result->rememberToken !== '', 'remember token issued');
ok($session->get('_auth_user') === $user['id'], 'session stores user id');

// CSRF blocks missing token
$csrf = new CsrfTokenManager($session);
$csrfMiddleware = new CsrfMiddleware($csrf);
$request = new Request('POST', new Uri('http://localhost/auth/login'));
$handler = new RequestHandler(static fn () => Response::text('ok'));
$response = $csrfMiddleware->process($request, $handler);
ok($response->getStatusCode() === 419, 'CSRF blocks requests without token');

// Password reset
$resetManager = new PasswordResetManager(new PasswordResetStore($resetPath, 3600), $hasher, $provider);
$token = $resetManager->createToken($user);
$ok = $resetManager->reset($user, $token, 'new-secret');
ok($ok === true, 'password reset succeeds');

$result = $auth->attempt([
    'email' => 'test@example.com',
    'password' => 'new-secret',
], false);
ok($result->authenticated === true, 'login works with new password');

echo "Auth smoke tests OK\n";

