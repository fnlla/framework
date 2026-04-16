<?php

declare(strict_types=1);

/**
 * Usage:
 *   php scripts/composer-profile.php dev install --no-interaction --prefer-dist
 */

$root = dirname(__DIR__);
$argv = $_SERVER['argv'] ?? [];

$profile = $argv[1] ?? '';
if (!is_string($profile) || $profile === '') {
    fwrite(STDERR, "Usage: php scripts/composer-profile.php <dev|prod> <composer-args...>\n");
    exit(1);
}

$profile = strtolower(trim($profile));
$composerFile = match ($profile) {
    'dev' => 'composer.dev.json',
    'prod', 'stable' => 'composer.json',
    default => '',
};

if ($composerFile === '') {
    fwrite(STDERR, "Unsupported profile: {$profile}. Use dev or prod.\n");
    exit(1);
}

$composerPath = $root . DIRECTORY_SEPARATOR . $composerFile;
if (!is_file($composerPath)) {
    fwrite(STDERR, "Composer profile file not found: {$composerPath}\n");
    exit(1);
}

$args = array_slice($argv, 2);
if ($args === []) {
    $args = ['install', '--no-interaction', '--prefer-dist'];
}

$composerBin = getenv('COMPOSER_BIN');
if (!is_string($composerBin) || trim($composerBin) === '') {
    $composerBin = getenv('COMPOSER_BINARY');
}
if (!is_string($composerBin) || trim($composerBin) === '') {
    $composerBin = 'composer';
}
$composerBin = trim($composerBin);
if ($composerBin === '') {
    $composerBin = 'composer';
}
if (preg_match('/\s/', $composerBin) === 1 && !str_starts_with($composerBin, '"')) {
    $composerBin = '"' . $composerBin . '"';
}

$escaped = array_map(
    static fn (string $arg): string => escapeshellarg($arg),
    $args
);

$command = $composerBin . ' ' . implode(' ', $escaped);

$descriptorSpec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$previousComposer = getenv('COMPOSER');
putenv('COMPOSER=' . $composerFile);
$_ENV['COMPOSER'] = $composerFile;
$_SERVER['COMPOSER'] = $composerFile;

if (getenv('COMPOSER_ROOT_VERSION') === false) {
    putenv('COMPOSER_ROOT_VERSION=2.5.x-dev');
    $_ENV['COMPOSER_ROOT_VERSION'] = '2.5.x-dev';
    $_SERVER['COMPOSER_ROOT_VERSION'] = '2.5.x-dev';
}

$process = proc_open($command, $descriptorSpec, $pipes, $root);
if (!is_resource($process)) {
    if ($previousComposer === false) {
        putenv('COMPOSER');
        unset($_ENV['COMPOSER'], $_SERVER['COMPOSER']);
    } else {
        putenv('COMPOSER=' . $previousComposer);
        $_ENV['COMPOSER'] = $previousComposer;
        $_SERVER['COMPOSER'] = $previousComposer;
    }
    fwrite(STDERR, "Unable to start Composer.\n");
    exit(1);
}

fclose($pipes[0]);
while (!feof($pipes[1])) {
    $line = fgets($pipes[1]);
    if ($line === false) {
        break;
    }
    fwrite(STDOUT, $line);
}
while (!feof($pipes[2])) {
    $line = fgets($pipes[2]);
    if ($line === false) {
        break;
    }
    fwrite(STDERR, $line);
}
fclose($pipes[1]);
fclose($pipes[2]);

$exitCode = proc_close($process);
if ($previousComposer === false) {
    putenv('COMPOSER');
    unset($_ENV['COMPOSER'], $_SERVER['COMPOSER']);
} else {
    putenv('COMPOSER=' . $previousComposer);
    $_ENV['COMPOSER'] = $previousComposer;
    $_SERVER['COMPOSER'] = $previousComposer;
}
exit(is_int($exitCode) ? $exitCode : 1);
