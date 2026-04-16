<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$harnessRoot = $root . '/tools/harness';
$appRoot = $root . '/app';

$requiredFiles = [
    'composer.json',
    'bootstrap/app.php',
    'bootstrap/helpers.php',
    'public/index.php',
    'routes/web.php',
];

$requiredDirs = [
    'config',
    'src',
];

if (!is_dir($harnessRoot) || !is_dir($appRoot)) {
    fwrite(STDERR, "ERROR: expected directories tools/harness and app.\n");
    exit(1);
}

$missing = [
    'harness' => [],
    'app' => [],
];

foreach ($requiredFiles as $path) {
    if (!is_file($harnessRoot . '/' . $path)) {
        $missing['harness'][] = $path;
    }
    if (!is_file($appRoot . '/' . $path)) {
        $missing['app'][] = $path;
    }
}

foreach ($requiredDirs as $path) {
    if (!is_dir($harnessRoot . '/' . $path)) {
        $missing['harness'][] = $path . '/';
    }
    if (!is_dir($appRoot . '/' . $path)) {
        $missing['app'][] = $path . '/';
    }
}

if ($missing['harness'] !== [] || $missing['app'] !== []) {
    if ($missing['harness'] !== []) {
        fwrite(STDERR, "Missing in tools/harness:\n");
        foreach ($missing['harness'] as $item) {
            fwrite(STDERR, " - {$item}\n");
        }
    }
    if ($missing['app'] !== []) {
        fwrite(STDERR, "Missing in app:\n");
        foreach ($missing['app'] as $item) {
            fwrite(STDERR, " - {$item}\n");
        }
    }
    fwrite(STDERR, "App scaffold presence check failed.\n");
    exit(1);
}

echo "App scaffold presence check OK.\n";
exit(0);
