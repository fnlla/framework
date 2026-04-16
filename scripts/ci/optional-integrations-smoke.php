<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$harnessDir = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
$autoload = $harnessDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/**
 * @return array{0:int,1:string}
 */
function runCommand(string $command, string $cwd): array
{
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes, $cwd);
    if (!is_resource($process)) {
        return [1, 'unable to start process'];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exit = proc_close($process);
    $output = trim((string) ($stderr !== '' ? $stderr : $stdout));

    return [$exit, $output];
}

if (!is_file($autoload)) {
    fwrite(STDOUT, "Installing harness dependencies for optional integration smoke...\n");
    [$installExit, $installOut] = runCommand('composer install --no-interaction --prefer-dist --no-progress', $harnessDir);
    if ($installExit !== 0) {
        fwrite(STDERR, "composer install failed for tools/harness\n");
        if ($installOut !== '') {
            fwrite(STDERR, $installOut . PHP_EOL);
        }
        exit(1);
    }
}

$tests = [
    'oauth' => $root . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'oauth' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'smoke.php',
    'sentry' => $root . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'sentry' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'smoke.php',
    'storage-s3' => $root . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'storage-s3' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'smoke.php',
    'stripe' => $root . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'stripe' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'smoke.php',
];

$results = [];
$hasFailures = false;

foreach ($tests as $name => $testPath) {
    if (!is_file($testPath)) {
        $results[] = [$name, 'FAIL', 'missing smoke test file'];
        $hasFailures = true;
        continue;
    }

    $pkgDir = dirname(dirname($testPath));
    $bootstrap = sprintf(
        "require %s; chdir(%s); require %s;",
        var_export($autoload, true),
        var_export($pkgDir, true),
        var_export($testPath, true)
    );

    [$exitCode, $output] = runCommand(PHP_BINARY . ' -r ' . escapeshellarg($bootstrap), $pkgDir);
    $status = $exitCode === 0 ? 'OK' : 'FAIL';
    $details = $output === '' ? ('exit ' . $exitCode) : $output;

    if (stripos($details, ' skip ') !== false || str_contains(strtolower($details), 'skip(')) {
        $status = 'FAIL';
        $details = 'unexpected skip: ' . $details;
    }

    if ($status === 'FAIL') {
        $hasFailures = true;
    }

    $results[] = [$name, $status, $details];
}

$headers = ['Integration', 'Status', 'Details'];
$widths = array_map('strlen', $headers);
foreach ($results as [$name, $status, $details]) {
    $widths[0] = max($widths[0], strlen($name));
    $widths[1] = max($widths[1], strlen($status));
    $widths[2] = max($widths[2], strlen($details));
}

$line = '+' . str_repeat('-', $widths[0] + 2)
    . '+' . str_repeat('-', $widths[1] + 2)
    . '+' . str_repeat('-', $widths[2] + 2) . "+\n";

echo $line;
printf("| %-" . $widths[0] . "s | %-" . $widths[1] . "s | %-" . $widths[2] . "s |\n", ...$headers);
echo $line;
foreach ($results as [$name, $status, $details]) {
    printf("| %-" . $widths[0] . "s | %-" . $widths[1] . "s | %-" . $widths[2] . "s |\n", $name, $status, $details);
}
echo $line;

exit($hasFailures ? 1 : 0);
