<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$targets = [];
$frameworkSrc = $root . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'src';
if (is_dir($frameworkSrc)) {
    $targets[] = $frameworkSrc;
}

$packagesRoot = $root . DIRECTORY_SEPARATOR . 'packages';
if (is_dir($packagesRoot)) {
    foreach (glob($packagesRoot . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'src') as $dir) {
        if (is_dir($dir)) {
            $targets[] = $dir;
        }
    }
}

if ($targets === []) {
    fwrite(STDERR, "No source directories found for license header check.\n");
    exit(1);
}

$errors = [];

$maxLines = 30;
$required = 'Proprietary License';
$forbidden = [
    'MIT License',
    'Licensed under the MIT License',
];

foreach ($targets as $baseDir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }
        if (strtolower($fileInfo->getExtension()) !== 'php') {
            continue;
        }

        $path = $fileInfo->getPathname();
        $lines = @file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            $errors[] = $path . ' (unable to read)';
            continue;
        }

        $head = implode("\n", array_slice($lines, 0, $maxLines));
        if (strpos($head, $required) === false) {
            $errors[] = $path . ' (missing "' . $required . '")';
            continue;
        }
        foreach ($forbidden as $needle) {
            if (strpos($head, $needle) !== false) {
                $errors[] = $path . ' (contains "' . $needle . '")';
                break;
            }
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "License header check failed:\n");
    foreach ($errors as $error) {
        fwrite(STDERR, ' - ' . $error . "\n");
    }
    exit(1);
}
fwrite(STDOUT, "License header check passed.\n");

