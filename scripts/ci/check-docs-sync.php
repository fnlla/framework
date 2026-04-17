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

$argv = $_SERVER['argv'] ?? [];
$defaultAppRoot = $root . DIRECTORY_SEPARATOR . 'app';
if (!is_dir($defaultAppRoot)) {
    $defaultAppRoot = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
}

$appRoot = $parseOption('--app', $argv) ?? $parseOption('-a', $argv) ?? $defaultAppRoot;
$appRoot = rtrim((string) $appRoot, '/\\');

if (!is_dir($appRoot)) {
    fwrite(STDERR, "App path not found: {$appRoot}\n");
    exit(1);
}

$bin = $appRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'finella';
if (!is_file($bin)) {
    fwrite(STDERR, "Finella CLI not found at {$bin}. Run composer install in the app first.\n");
    exit(1);
}

putenv('APP_ROOT=' . $appRoot);

$command = 'php ' . escapeshellarg($bin) . ' docs:generate --publish --app=' . escapeshellarg($appRoot);
passthru($command, $exitCode);
if ($exitCode !== 0) {
    fwrite(STDERR, "Docs build failed with exit code {$exitCode}.\n");
    exit($exitCode);
}

$docsRoot = $appRoot . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'docs';
if (!is_dir($docsRoot)) {
    fwrite(STDERR, "Docs published directory missing: {$docsRoot}\n");
    exit(1);
}

$hasMarkdown = false;
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($docsRoot, FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $item) {
    if ($item->isFile() && str_ends_with(strtolower($item->getFilename()), '.md')) {
        $hasMarkdown = true;
        break;
    }
}
if (!$hasMarkdown) {
    fwrite(STDERR, "Docs published but no markdown files found in {$docsRoot}\n");
    exit(1);
}

echo "Docs build check OK\n";
