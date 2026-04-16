<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$parseOption = static function (string $name, array $argv): ?string {
    foreach ($argv as $arg) {
        if (str_starts_with($arg, $name . '=')) {
            return substr($arg, strlen($name) + 1);
        }
    }
    return null;
};

$hasFlag = static function (string $flag, array $argv): bool {
    return in_array($flag, $argv, true);
};

$argv = $_SERVER['argv'] ?? [];

$appRoot = $parseOption('--app', $argv) ?? $parseOption('-a', $argv) ?? ($root . DIRECTORY_SEPARATOR . 'app');
$source = $parseOption('--source', $argv) ?? null;
$target = $parseOption('--target', $argv) ?? null;
$clean = $hasFlag('--clean', $argv);

$appRoot = rtrim((string) $appRoot, '/\\');
if (!is_dir($appRoot)) {
    fwrite(STDERR, "App path not found: {$appRoot}\n");
    exit(1);
}

if ($source === null || trim($source) === '') {
    $defaultSource = $root . DIRECTORY_SEPARATOR . 'documentation' . DIRECTORY_SEPARATOR . 'app';
    if (!is_dir($defaultSource)) {
        $defaultSource = $root . DIRECTORY_SEPARATOR . 'docs';
    }
    if (!is_dir($defaultSource)) {
        $defaultSource = $appRoot . DIRECTORY_SEPARATOR . 'docs';
    }
    $source = $defaultSource;
}

$source = rtrim((string) $source, '/\\');
if (!is_dir($source)) {
    fwrite(STDERR, "Docs source not found: {$source}\n");
    exit(1);
}

if ($target === null || trim($target) === '') {
    $target = $appRoot . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'docs';
}
$target = rtrim((string) $target, '/\\');

$ensureDir = static function (string $path): void {
    if (!is_dir($path) && !@mkdir($path, 0777, true) && !is_dir($path)) {
        throw new RuntimeException('Unable to create directory: ' . $path);
    }
};

$deleteDir = static function (string $path): void {
    if (!is_dir($path)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } else {
            @unlink($item->getPathname());
        }
    }
    @rmdir($path);
};

if ($clean) {
    $deleteDir($target);
}

$ensureDir($target);

$copied = 0;
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $relative = ltrim(str_replace($source, '', $item->getPathname()), '/\\');
    $targetPath = $target . DIRECTORY_SEPARATOR . $relative;

    if ($item->isDir()) {
        $ensureDir($targetPath);
        continue;
    }

    $ensureDir(dirname($targetPath));
    if (@copy($item->getPathname(), $targetPath)) {
        $copied++;
    }
}

echo "Docs synced: {$copied} files\n";

echo "Source: {$source}\n";

echo "Target: {$target}\n";

