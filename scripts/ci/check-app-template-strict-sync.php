<?php

declare(strict_types=1);

function fail(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

function collectFiles(string $root, array $excludePrefixes, array $excludePaths): array
{
    $files = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $relative = substr($file->getPathname(), strlen($root) + 1);
        $relative = str_replace('\\', '/', $relative);

        foreach ($excludePrefixes as $prefix) {
            if (str_starts_with($relative, $prefix)) {
                continue 2;
            }
        }

        if (in_array($relative, $excludePaths, true)) {
            continue;
        }

        $files[] = $relative;
    }

    sort($files);

    return $files;
}

$root = dirname(__DIR__, 2);
$appRoot = $root . '/app';
$harnessRoot = $root . '/tools/harness';

if (!is_dir($appRoot) || !is_dir($harnessRoot)) {
    fail('ERROR: expected directories app/ and tools/harness/.');
}

$excludePrefixes = [
    'vendor/',
    'storage/',
    'bootstrap/cache/',
    '.composer-cache/',
    '.composer-home/',
];

$excludePaths = [
    'composer.json',
    'composer.lock',
    'README.md',
];

$appFiles = collectFiles($appRoot, $excludePrefixes, $excludePaths);
$harnessFiles = collectFiles($harnessRoot, $excludePrefixes, $excludePaths);

$common = array_values(array_intersect($appFiles, $harnessFiles));

$diffs = [];
foreach ($common as $relative) {
    $appPath = $appRoot . '/' . $relative;
    $harnessPath = $harnessRoot . '/' . $relative;
    if (!is_file($appPath) || !is_file($harnessPath)) {
        continue;
    }
    if (hash_file('sha256', $appPath) !== hash_file('sha256', $harnessPath)) {
        $diffs[] = $relative;
    }
}

if ($diffs !== []) {
    $message = "App template strict sync failed. Mismatched files:\n";
    foreach ($diffs as $path) {
        $message .= " - {$path}\n";
    }
    $message .= "Fix the diffs or update exclusions in scripts/ci/check-app-template-strict-sync.php.";
    fail(rtrim($message));
}

echo sprintf(
    "App template strict sync OK (checked %d shared files).\n",
    count($common)
);
