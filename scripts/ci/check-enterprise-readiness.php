<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$strict = in_array('--strict', $_SERVER['argv'] ?? [], true)
    || toBool(getenv('Fnlla_ENTERPRISE_CHECK_STRICT'), false)
    || toBool(getenv('CI'), false);

$results = [];
$failures = 0;
$warnings = 0;

$addResult = static function (string $label, string $status, string $detail = '') use (&$results, &$failures, &$warnings): void {
    $results[] = [$label, $status, $detail];
    if ($status === 'FAIL') {
        $failures++;
    } elseif ($status === 'WARN') {
        $warnings++;
    }
};

$checkFile = static function (string $label, string $path, bool $required = true) use ($addResult): void {
    if (is_file($path)) {
        $addResult($label, 'OK', str_replace('\\', '/', $path));
        return;
    }
    if ($required) {
        $addResult($label, 'FAIL', 'missing ' . str_replace('\\', '/', $path));
        return;
    }
    $addResult($label, 'WARN', 'recommended file missing: ' . str_replace('\\', '/', $path));
};

$checkFile('readme', $root . DIRECTORY_SEPARATOR . 'README.md');
$checkFile('license', $root . DIRECTORY_SEPARATOR . 'LICENSE.md');
$checkFile('security-policy', $root . DIRECTORY_SEPARATOR . '.github' . DIRECTORY_SEPARATOR . 'SECURITY.md');
$checkFile('ci-workflow', $root . DIRECTORY_SEPARATOR . '.github' . DIRECTORY_SEPARATOR . 'workflows' . DIRECTORY_SEPARATOR . 'ci.yml');
$checkFile('release-workflow', $root . DIRECTORY_SEPARATOR . '.github' . DIRECTORY_SEPARATOR . 'workflows' . DIRECTORY_SEPARATOR . 'release.yml');
$checkFile('osv-workflow', $root . DIRECTORY_SEPARATOR . '.github' . DIRECTORY_SEPARATOR . 'workflows' . DIRECTORY_SEPARATOR . 'osv.yml');
$checkFile('release-gate-script', $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'release' . DIRECTORY_SEPARATOR . 'release-gate.sh');
$checkFile('operations-doc', $root . DIRECTORY_SEPARATOR . 'documentation' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'operations.md');

$readmePath = $root . DIRECTORY_SEPARATOR . 'README.md';
$readmeContent = is_file($readmePath) ? (string) file_get_contents($readmePath) : '';
if ($readmeContent !== '' && str_contains($readmeContent, 'Developed by TechAyo')) {
    $addResult('readme-badge-author', 'OK', 'badge present');
} else {
    $addResult('readme-badge-author', 'FAIL', 'missing "Developed by TechAyo" badge');
}
if ($readmeContent !== '' && str_contains($readmeContent, 'Latest Release')) {
    $addResult('readme-badge-release', 'OK', 'badge present');
} else {
    $addResult('readme-badge-release', 'FAIL', 'missing release badge');
}

$operationsPath = $root . DIRECTORY_SEPARATOR . 'documentation' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'operations.md';
$operationsContent = is_file($operationsPath) ? (string) file_get_contents($operationsPath) : '';
if (
    $operationsContent !== ''
    && preg_match('/\\*\\*ENTERPRISE READINESS\\*\\*/i', $operationsContent) === 1
    && preg_match('/\\*\\*MINIMUM ENTERPRISE BAR \\(SUMMARY\\)\\*\\*/i', $operationsContent) === 1
) {
    $addResult('operations-enterprise', 'OK', 'enterprise sections present');
} else {
    $addResult('operations-enterprise', 'FAIL', 'missing enterprise readiness sections in operations doc');
}

$releaseWorkflowPath = $root . DIRECTORY_SEPARATOR . '.github' . DIRECTORY_SEPARATOR . 'workflows' . DIRECTORY_SEPARATOR . 'release.yml';
$releaseWorkflow = is_file($releaseWorkflowPath) ? (string) file_get_contents($releaseWorkflowPath) : '';
if ($releaseWorkflow !== '' && preg_match('/workflow_dispatch\\s*:/i', $releaseWorkflow) === 1) {
    $addResult('release-policy', 'OK', 'manual release workflow enabled');
} else {
    $addResult('release-policy', 'FAIL', 'release workflow should expose workflow_dispatch');
}

$supportPolicyScript = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'ci' . DIRECTORY_SEPARATOR . 'check-support-policy.php';
if (!is_file($supportPolicyScript)) {
    $addResult('support-policy-sync', 'FAIL', 'missing scripts/ci/check-support-policy.php');
} else {
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($supportPolicyScript);
    [$exitCode, $output] = runCommand($command, $root);
    if ($exitCode === 0) {
        $addResult('support-policy-sync', 'OK', trim($output) !== '' ? trim($output) : 'support policy sync OK');
    } else {
        $message = trim($output);
        $addResult('support-policy-sync', 'FAIL', $message !== '' ? $message : 'support policy check failed');
    }
}

$headers = ['Check', 'Status', 'Details'];
$widths = array_map('strlen', $headers);
foreach ($results as $row) {
    $widths[0] = max($widths[0], strlen($row[0]));
    $widths[1] = max($widths[1], strlen($row[1]));
    $widths[2] = max($widths[2], strlen($row[2]));
}

$line = '+' . str_repeat('-', $widths[0] + 2)
    . '+' . str_repeat('-', $widths[1] + 2)
    . '+' . str_repeat('-', $widths[2] + 2) . "+\n";

echo $line;
printf("| %-" . $widths[0] . "s | %-" . $widths[1] . "s | %-" . $widths[2] . "s |\n", ...$headers);
echo $line;
foreach ($results as $row) {
    printf(
        "| %-" . $widths[0] . "s | %-" . $widths[1] . "s | %-" . $widths[2] . "s |\n",
        $row[0],
        $row[1],
        $row[2]
    );
}
echo $line;

if ($failures > 0) {
    exit(1);
}
if ($strict && $warnings > 0) {
    exit(1);
}

exit(0);

function toBool(mixed $value, bool $default = false): bool
{
    if (is_bool($value)) {
        return $value;
    }
    if (is_int($value)) {
        return $value === 1;
    }
    if (!is_string($value)) {
        return $default;
    }

    $normalized = strtolower(trim($value));
    if ($normalized === '') {
        return $default;
    }

    return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
}

/**
 * @return array{0:int,1:string}
 */
function runCommand(string $command, string $cwd): array
{
    $descriptor = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptor, $pipes, $cwd);
    if (!is_resource($process)) {
        return [1, 'Unable to start command: ' . $command];
    }

    $stdout = isset($pipes[1]) && is_resource($pipes[1]) ? (string) stream_get_contents($pipes[1]) : '';
    if (isset($pipes[1]) && is_resource($pipes[1])) {
        fclose($pipes[1]);
    }
    $stderr = isset($pipes[2]) && is_resource($pipes[2]) ? (string) stream_get_contents($pipes[2]) : '';
    if (isset($pipes[2]) && is_resource($pipes[2])) {
        fclose($pipes[2]);
    }

    $exitCode = (int) proc_close($process);
    $combined = trim($stdout . ($stderr !== '' ? "\n" . $stderr : ''));

    return [$exitCode, $combined];
}
