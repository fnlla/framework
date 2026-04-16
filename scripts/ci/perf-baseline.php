<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$defaults = [
    'runs' => 5,
    'output' => $root . DIRECTORY_SEPARATOR . '.artifacts' . DIRECTORY_SEPARATOR . 'perf-baseline.json',
    'thresholds' => $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'ci' . DIRECTORY_SEPARATOR . 'perf-baseline-thresholds.json',
];

$options = getopt('', ['runs::', 'output::', 'thresholds::']);
$runs = isset($options['runs']) ? (int) $options['runs'] : $defaults['runs'];
$outputPath = isset($options['output']) && is_string($options['output']) && $options['output'] !== ''
    ? $options['output']
    : $defaults['output'];
$thresholdsPath = isset($options['thresholds']) && is_string($options['thresholds']) && $options['thresholds'] !== ''
    ? $options['thresholds']
    : $defaults['thresholds'];

if ($runs < 1 || $runs > 50) {
    fwrite(STDERR, "--runs must be between 1 and 50.\n");
    exit(1);
}

if (!str_starts_with($outputPath, DIRECTORY_SEPARATOR) && preg_match('/^[A-Za-z]:\\\\/', $outputPath) !== 1) {
    $outputPath = $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $outputPath);
}
if (!str_starts_with($thresholdsPath, DIRECTORY_SEPARATOR) && preg_match('/^[A-Za-z]:\\\\/', $thresholdsPath) !== 1) {
    $thresholdsPath = $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $thresholdsPath);
}

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

$harnessDir = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
$autoload = $harnessDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!is_file($autoload)) {
    fwrite(STDOUT, "Installing tools/harness dependencies for perf baseline...\n");
    [$installExit, $installOut] = runCommand('composer install --no-interaction --prefer-dist --no-progress', $harnessDir);
    if ($installExit !== 0) {
        fwrite(STDERR, "composer install failed in tools/harness\n");
        if ($installOut !== '') {
            fwrite(STDERR, $installOut . PHP_EOL);
        }
        exit(1);
    }
}

$container = [];
$headers = [];
$rawRuns = [];
for ($i = 1; $i <= $runs; $i++) {
    [$exitCode, $output] = runCommand(PHP_BINARY . ' scripts/dev/bench.php', $root);
    if ($exitCode !== 0) {
        fwrite(STDERR, "bench run {$i} failed\n");
        if ($output !== '') {
            fwrite(STDERR, $output . PHP_EOL);
        }
        exit(1);
    }

    if (
        preg_match('/Container resolves:\s+([0-9.]+)\s+ms/i', $output, $mContainer) !== 1
        || preg_match('/Header lookups:\s+([0-9.]+)\s+ms/i', $output, $mHeaders) !== 1
    ) {
        fwrite(STDERR, "Unable to parse bench output for run {$i}:\n{$output}\n");
        exit(1);
    }

    $containerMs = (float) $mContainer[1];
    $headerMs = (float) $mHeaders[1];
    $container[] = $containerMs;
    $headers[] = $headerMs;
    $rawRuns[] = [
        'run' => $i,
        'container_resolves_ms' => $containerMs,
        'header_lookups_ms' => $headerMs,
    ];
}

$containerAvg = array_sum($container) / count($container);
$headerAvg = array_sum($headers) / count($headers);

$result = [
    'generated_at_utc' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeInterface::ATOM),
    'runs' => $runs,
    'metrics' => [
        'container_resolves_ms' => [
            'avg' => round($containerAvg, 2),
            'min' => round((float) min($container), 2),
            'max' => round((float) max($container), 2),
        ],
        'header_lookups_ms' => [
            'avg' => round($headerAvg, 2),
            'min' => round((float) min($headers), 2),
            'max' => round((float) max($headers), 2),
        ],
    ],
    'raw' => $rawRuns,
];

$thresholds = [];
if (is_file($thresholdsPath)) {
    $decoded = json_decode((string) file_get_contents($thresholdsPath), true);
    if (is_array($decoded)) {
        $thresholds = $decoded;
    }
}

$violations = [];
$containerMax = $thresholds['container_resolves_ms_avg_max'] ?? null;
if (is_numeric($containerMax) && $containerAvg > (float) $containerMax) {
    $violations[] = sprintf(
        'container_resolves_ms avg %.2f exceeds threshold %.2f',
        $containerAvg,
        (float) $containerMax
    );
}
$headerMax = $thresholds['header_lookups_ms_avg_max'] ?? null;
if (is_numeric($headerMax) && $headerAvg > (float) $headerMax) {
    $violations[] = sprintf(
        'header_lookups_ms avg %.2f exceeds threshold %.2f',
        $headerAvg,
        (float) $headerMax
    );
}

$result['thresholds'] = $thresholds;
$result['status'] = $violations === [] ? 'ok' : 'fail';
$result['violations'] = $violations;

$outDir = dirname($outputPath);
if (!is_dir($outDir) && !mkdir($outDir, 0775, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Unable to create output dir: {$outDir}\n");
    exit(1);
}

if (file_put_contents($outputPath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL) === false) {
    fwrite(STDERR, "Unable to write output file: {$outputPath}\n");
    exit(1);
}

echo sprintf(
    "Perf baseline: container avg %.2f ms, headers avg %.2f ms (%d runs)\n",
    $containerAvg,
    $headerAvg,
    $runs
);
echo "Wrote: {$outputPath}\n";

if ($violations !== []) {
    foreach ($violations as $violation) {
        fwrite(STDERR, "FAIL: {$violation}\n");
    }
    exit(1);
}

exit(0);
