<?php

declare(strict_types=1);

require dirname(__DIR__) . '/ensure-php85.php';

$root = dirname(__DIR__, 2);
$appDir = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
$autoload = $appDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing tools/harness/vendor/autoload.php. Run composer install in tools/harness.\n");
    exit(1);
}

require $autoload;


function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'finella_routes_cache_smoke_' . bin2hex(random_bytes(4));
$configDir = $tmp . DIRECTORY_SEPARATOR . 'config';
$routesDir = $tmp . DIRECTORY_SEPARATOR . 'routes';
$cacheDir = $tmp . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache';

@mkdir($configDir, 0775, true);
@mkdir($routesDir, 0775, true);
@mkdir($cacheDir, 0775, true);

$cachePath = $cacheDir . DIRECTORY_SEPARATOR . 'routes.php';
$configTemplate = <<<'PHP'
<?php

declare(strict_types=1);

return [
    'name' => 'finella-smoke',
    'env' => 'test',
    'debug' => true,
    'routes_cache' => %s,
    'routes_cache_strict' => false,
];
PHP;

file_put_contents($configDir . DIRECTORY_SEPARATOR . 'app.php', sprintf($configTemplate, var_export($cachePath, true)));

$routesTemplate = <<<'PHP'
<?php

use Finella\Http\Router;

return function (Router $router): void {
    $router->get('/closure', function () {
        return 'ok';
    });
};
PHP;

file_put_contents($routesDir . DIRECTORY_SEPARATOR . 'web.php', $routesTemplate);

file_put_contents($tmp . DIRECTORY_SEPARATOR . 'run.php', <<<'PHP'
<?php

declare(strict_types=1);

$autoload = $argv[1] ?? '';
$root = $argv[2] ?? '';

if (!is_string($autoload) || $autoload === '' || !is_file($autoload)) {
    fwrite(STDERR, "autoload missing\n");
    exit(1);
}

require $autoload;

use Finella\Console\Commands\RoutesCacheCommand;
use Finella\Console\ConsoleIO;

$command = new RoutesCacheCommand();
$io = new ConsoleIO();
exit($command->run([], [], $io, $root));
PHP);

$phpBin = PHP_BINARY;
$cmd = [$phpBin, $tmp . DIRECTORY_SEPARATOR . 'run.php', $autoload, $tmp];
$descriptorSpec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$process = proc_open($cmd, $descriptorSpec, $pipes, $tmp);
if (!is_resource($process)) {
    fwrite(STDERR, "Failed to start routes:cache smoke.\n");
    exit(1);
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);

$exitCode = proc_close($process);
if ($exitCode !== 0) {
    if ($stderr !== '') {
        fwrite(STDERR, $stderr . "\n");
    }
    if ($stdout !== '') {
        fwrite(STDOUT, $stdout . "\n");
    }
    exit(1);
}

ok($exitCode === 0, 'routes:cache exits with 0 on closure routes');
if (!is_string($stdout)) {
    $stdout = '';
}
$output = $stdout;
ok(str_contains($output, 'Routes cache disabled'), 'routes:cache reports disabled cache');
ok(
    str_contains($output, 'non-cacheable handlers/middleware') || str_contains($output, 'closures'),
    'routes:cache mentions non-cacheable handlers'
);

echo "Routes cache smoke tests OK\n";
