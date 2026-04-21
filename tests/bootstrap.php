<?php

declare(strict_types=1);

date_default_timezone_set('UTC');
setlocale(LC_ALL, 'C');
putenv('LC_ALL=C');

$root = dirname(__DIR__);

$roots = [
    $root . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'src',
];

$packageDirs = glob($root . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'src', GLOB_ONLYDIR) ?: [];
foreach ($packageDirs as $dir) {
    if (str_contains($dir, 'packages' . DIRECTORY_SEPARATOR . '_package-template')) {
        continue;
    }
    $roots[] = $dir;
}

spl_autoload_register(static function (string $class) use ($roots): void {
    $prefix = 'Fnlla\\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix))) . '.php';
    foreach ($roots as $base) {
        $path = $base . DIRECTORY_SEPARATOR . $relative;
        if (is_file($path)) {
            require $path;
            return;
        }
    }
});
