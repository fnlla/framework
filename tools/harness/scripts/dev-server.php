<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/scripts/ensure-php85.php';

$host = getenv('Fnlla_DEV_HOST');
if (!is_string($host) || trim($host) === '') {
    $host = '127.0.0.1';
}

$port = getenv('Fnlla_DEV_PORT');
if (!is_string($port) || trim($port) === '' || !ctype_digit($port)) {
    $port = '8000';
}

$docroot = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public';
$command = sprintf(
    '%s -S %s:%s -t %s',
    escapeshellarg(PHP_BINARY),
    escapeshellarg($host),
    escapeshellarg($port),
    escapeshellarg($docroot)
);

$exitCode = 1;
passthru($command, $exitCode);
exit(is_int($exitCode) ? $exitCode : 1);
