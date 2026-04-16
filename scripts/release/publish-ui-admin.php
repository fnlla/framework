<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$source = $root . '/ui/admin-stubs';

if (!is_dir($source)) {
    fwrite(STDERR, "UI admin stubs not found. Expected: {$source}\n");
    exit(1);
}

$args = $_SERVER['argv'] ?? [];
array_shift($args);

$options = [
    'app' => null,
    'force' => false,
    'dry_run' => false,
];

foreach ($args as $arg) {
    if ($arg === '--force') {
        $options['force'] = true;
        continue;
    }
    if ($arg === '--dry-run') {
        $options['dry_run'] = true;
        continue;
    }
    if (str_starts_with($arg, '--app=')) {
        $options['app'] = trim(substr($arg, 6));
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        echo "Usage: php scripts/release/publish-ui-admin.php [--app=PATH] [--force] [--dry-run]\n";
        exit(0);
    }
}

$defaultApp = is_dir($root . '/app') ? $root . '/app' : getcwd();
$appRoot = $options['app'] ?? $defaultApp;

if ($appRoot === null || $appRoot === '') {
    fwrite(STDERR, "Missing --app=PATH.\n");
    exit(1);
}

if (!is_dir($appRoot)) {
    fwrite(STDERR, "App path not found: {$appRoot}\n");
    exit(1);
}

$files = collectFiles($source);
$copied = 0;
$skipped = 0;

foreach ($files as $relative) {
    $from = $source . '/' . $relative;
    $to = rtrim($appRoot, '/\\') . '/' . $relative;

    if (is_file($to) && !$options['force']) {
        $skipped++;
        echo "SKIP {$relative}\n";
        continue;
    }

    $dir = dirname($to);
    if (!is_dir($dir) && !$options['dry_run']) {
        mkdir($dir, 0777, true);
    }

    if (!$options['dry_run']) {
        if (!copy($from, $to)) {
            fwrite(STDERR, "Failed to copy {$relative}\n");
            exit(1);
        }
    }

    $copied++;
    echo "COPY {$relative}\n";
}

echo "Done. Copied {$copied}, skipped {$skipped}.\n";

function collectFiles(string $base): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $path = str_replace('\\', '/', $fileInfo->getPathname());
        $relative = ltrim(substr($path, strlen(str_replace('\\', '/', $base))), '/');
        if ($relative === '') {
            continue;
        }
        $files[] = $relative;
    }

    sort($files);
    return $files;
}
