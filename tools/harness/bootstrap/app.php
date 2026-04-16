<?php

declare(strict_types=1);

use Finella\Core\Application;
use Finella\Core\ConfigRepository;
use Finella\Http\HttpKernel;
use Finella\Contracts\Http\KernelInterface;
use Finella\Support\ComposerProviderDiscovery;
use Finella\Support\Dotenv;
use Finella\Support\ProviderCache;
use Finella\Support\ProviderRepository;
use Finella\Support\ProviderReport;

$root = getenv('APP_ROOT');
if (!is_string($root) || trim($root) === '') {
    $root = dirname(__DIR__);
}
$root = rtrim($root, '/\\');

if (!defined('APP_ROOT')) {
    define('APP_ROOT', $root);
}

$envPath = $root . '/.env';
$envContents = is_file($envPath) ? file_get_contents($envPath) : false;
if (is_string($envContents) && str_starts_with($envContents, "\xEF\xBB\xBF")) {
    $envContents = substr($envContents, 3);
    @file_put_contents($envPath, $envContents);
}
$dotenv = new Dotenv();
$dotenv->usePutenv(true);

$loaded = false;
if (is_file($envPath)) {
    if (method_exists($dotenv, 'bootEnv')) {
        $dotenv->bootEnv($envPath);
        $loaded = true;
    } elseif (method_exists($dotenv, 'loadEnv')) {
        $dotenv->loadEnv($envPath);
        $loaded = true;
    } else {
        $dotenv->load($envPath);
        $loaded = true;
    }
}

if (!$loaded) {
    $env = getenv('APP_ENV');
    $env = is_string($env) ? strtolower(trim($env)) : '';
    $candidates = [];

    $localPath = $root . '/.env.local';
    if ($env !== 'test' && is_file($localPath)) {
        $candidates[] = $localPath;
    }

    if ($env !== '') {
        $envPath = $root . '/.env.' . $env;
        $envLocalPath = $root . '/.env.' . $env . '.local';
        if (is_file($envPath)) {
            $candidates[] = $envPath;
        }
        if (is_file($envLocalPath)) {
            $candidates[] = $envLocalPath;
        }
    }

    if ($candidates !== []) {
        $dotenv->load(...$candidates);
    }
}

foreach (['APP_ENV', 'APP_DEBUG'] as $key) {
    $value = getenv($key);
    if ($value !== false) {
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
        if (!array_key_exists($key, $_SERVER)) {
            $_SERVER[$key] = $value;
        }
    }
}

$configRepo = ConfigRepository::fromRoot($root);
$app = new Application($root, $configRepo);
$GLOBALS['finella_app'] = $app;

$providerConfig = $configRepo->get('providers', []);
if (!is_array($providerConfig)) {
    $providerConfig = [];
}

$autoDiscovery = (bool) ($providerConfig['auto_discovery'] ?? true);
$disabled = $providerConfig['disabled'] ?? [];
if (!is_array($disabled)) {
    $disabled = [];
}
$manual = $providerConfig['manual'] ?? [];
if (!is_array($manual)) {
    $manual = [];
}

$cachePath = $root . '/bootstrap/cache/providers.php';
$discovered = [];
$discoveredMeta = [];
$discoverMonorepo = static function (string $packagesDir): array {
    $providers = [];
    $meta = [];
    $paths = glob(rtrim($packagesDir, '/\\') . '/*/composer.json', GLOB_NOSORT) ?: [];
    foreach ($paths as $path) {
        $contents = file_get_contents($path);
        if ($contents === false || $contents === '') {
            continue;
        }
        $data = json_decode($contents, true);
        if (!is_array($data)) {
            continue;
        }
        $extra = $data['extra']['finella']['providers'] ?? null;
        if (!is_array($extra)) {
            continue;
        }
        $packageName = is_string($data['name'] ?? null) ? $data['name'] : null;
        $packageVersion = is_string($data['version'] ?? null) ? $data['version'] : null;
        foreach ($extra as $provider) {
            if (!is_string($provider) || $provider === '') {
                continue;
            }
            $providers[$provider] = true;
            if (!isset($meta[$provider])) {
                $entry = ['source' => 'monorepo'];
                if (is_string($packageName) && $packageName !== '') {
                    $entry['package'] = $packageName;
                }
                if (is_string($packageVersion) && $packageVersion !== '') {
                    $entry['version'] = $packageVersion;
                }
                $meta[$provider] = $entry;
            }
        }
    }

    return [
        'providers' => array_keys($providers),
        'meta' => $meta,
    ];
};
if (is_file($cachePath)) {
    $cached = require $cachePath;
    if (is_array($cached)) {
        if (isset($cached['providers']) || isset($cached['meta'])) {
            $discovered = is_array($cached['providers'] ?? null) ? $cached['providers'] : [];
            $discoveredMeta = is_array($cached['meta'] ?? null) ? $cached['meta'] : [];
        } else {
            $discovered = $cached;
        }
    }
} elseif ($autoDiscovery) {
    $discovery = ComposerProviderDiscovery::discover($root . '/vendor');
    $discovered = is_array($discovery['providers'] ?? null) ? $discovery['providers'] : [];
    $discoveredMeta = is_array($discovery['meta'] ?? null) ? $discovery['meta'] : [];

    if ($discovered === []) {
        $monorepoRoot = dirname($root, 2);
        $packagesDir = $monorepoRoot . '/packages';
        if (is_dir($packagesDir)) {
            $monorepoDiscovery = $discoverMonorepo($packagesDir);
            $discovered = $monorepoDiscovery['providers'];
            $discoveredMeta = $monorepoDiscovery['meta'];
        }
    }

    ProviderCache::write($cachePath, ['providers' => $discovered, 'meta' => $discoveredMeta]);
}

$env = strtolower((string) getenv('APP_ENV'));
if ($env === '') {
    $env = 'prod';
}

$providers = [];
$providerMeta = [];
foreach ($discovered as $provider) {
    if (!is_string($provider) || $provider === '') {
        continue;
    }

    $rules = $disabled[$provider] ?? null;
    if (is_array($rules)) {
        if (!empty($rules['all'])) {
            continue;
        }
        if (isset($rules[$env]) && $rules[$env]) {
            continue;
        }
    }

    $providers[] = $provider;
    $providerMeta[$provider] = is_array($discoveredMeta[$provider] ?? null) ? $discoveredMeta[$provider] : ['source' => 'auto'];
}

foreach ($manual as $provider) {
    if (is_string($provider) && $provider !== '') {
        $providers[] = $provider;
        $providerMeta[$provider] = ['source' => 'manual'];
    }
}

$providers = array_values(array_unique($providers));
$repository = new ProviderRepository($app);
foreach ($providers as $provider) {
    $meta = $providerMeta[$provider] ?? [];
    $source = (string) ($meta['source'] ?? 'auto');
    $repository->add($provider, 0, $source, true, $meta);
}
$repository->registerAll();
$repository->bootAll();

$debug = getenv('APP_DEBUG') === '1';
if ($debug && $app->has(ProviderReport::class)) {
    $report = $app->make(ProviderReport::class);
    if ($report instanceof ProviderReport) {
        $logDir = $root . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logPath = $logDir . '/finella-providers.log';
        @file_put_contents($logPath, $report->toText());
    }
}

$kernel = new HttpKernel($app);

return $kernel;
