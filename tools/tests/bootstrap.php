<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$autoload = $root . '/tools/vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
}

if (is_file($root . '/framework/src/Support/global_helpers.php')) {
    require_once $root . '/framework/src/Support/global_helpers.php';
}

if (is_file($root . '/framework/src/Support/helpers.php')) {
    require_once $root . '/framework/src/Support/helpers.php';
}

if (is_file($root . '/tools/harness/bootstrap/helpers.php')) {
    require_once $root . '/tools/harness/bootstrap/helpers.php';
}

$packageDirs = glob($root . '/packages/*/src', GLOB_ONLYDIR) ?: [];
$appDirs = [
    $root . '/tools/harness/app',
    $root . '/app/src',
];
$packageNamespaces = [];
$composerFiles = glob($root . '/packages/*/composer.json', GLOB_NOSORT) ?: [];
foreach ($composerFiles as $composerPath) {
    $contents = file_get_contents($composerPath);
    if ($contents === false || $contents === '') {
        continue;
    }
    $data = json_decode($contents, true);
    if (!is_array($data)) {
        continue;
    }
    $autoload = $data['autoload']['psr-4'] ?? null;
    if (!is_array($autoload)) {
        continue;
    }
    $packageRoot = dirname($composerPath);
    foreach ($autoload as $prefix => $dir) {
        if (!is_string($prefix) || !str_starts_with($prefix, 'Fnlla\\')) {
            continue;
        }
        $prefix = rtrim($prefix, '\\');
        $dirPath = null;
        if (is_string($dir)) {
            $dirPath = $dir;
        } elseif (is_array($dir) && isset($dir[0]) && is_string($dir[0])) {
            $dirPath = $dir[0];
        }
        if ($dirPath === null) {
            continue;
        }
        $packageNamespaces[$prefix] = $packageRoot . '/' . trim($dirPath, '/\\');
    }
}
uksort($packageNamespaces, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

spl_autoload_register(function (string $class) use ($root, $packageDirs, $appDirs, $packageNamespaces): void {
    $path = str_replace('\\', '/', $class) . '.php';

    if (str_starts_with($class, 'Fnlla\\')) {
        foreach ($packageNamespaces as $prefix => $baseDir) {
            if (str_starts_with($class, $prefix . '\\')) {
                $relative = str_replace('\\', '/', substr($class, strlen($prefix) + 1)) . '.php';
                $file = $baseDir . '/' . $relative;
                if (is_file($file)) {
                    require $file;
                    return;
                }
            }
        }

        $relative = substr($path, strlen('fnlla/'));
        if ($relative === false || $relative === '') {
            $relative = $path;
        }

        $candidate = $root . '/framework/src/' . $relative;
        if (is_file($candidate)) {
            require $candidate;
            return;
        }
    }

    if (str_starts_with($class, 'App\\')) {
        $relative = substr($path, strlen('App/'));
        if ($relative === false || $relative === '') {
            $relative = $path;
        }
        foreach ($appDirs as $dir) {
            $file = $dir . '/' . $relative;
            if (is_file($file)) {
                require $file;
                return;
            }
        }
    }
});

