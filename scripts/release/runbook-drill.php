<?php

declare(strict_types=1);

/**
 * Backup/restore + rollback dry-run exercise for the operations runbook.
 *
 * Example:
 *   php scripts/release/runbook-drill.php --app=app
 */

function fail(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

/**
 * @return array<string, string|bool>
 */
function parseOptions(array $argv): array
{
    $options = [
        'app' => 'app',
        'artifacts' => '.artifacts/runbook-drill',
        'rollback_ref' => '',
        'keep_artifacts' => false,
    ];

    foreach ($argv as $argument) {
        if (!is_string($argument) || !str_starts_with($argument, '--')) {
            continue;
        }

        if ($argument === '--keep-artifacts') {
            $options['keep_artifacts'] = true;
            continue;
        }

        $parts = explode('=', $argument, 2);
        $key = $parts[0] ?? '';
        $value = $parts[1] ?? '';
        if (!is_string($key)) {
            continue;
        }

        switch ($key) {
            case '--app':
                if ($value !== '') {
                    $options['app'] = $value;
                }
                break;
            case '--artifacts':
                if ($value !== '') {
                    $options['artifacts'] = $value;
                }
                break;
            case '--rollback-ref':
                $options['rollback_ref'] = $value;
                break;
        }
    }

    return $options;
}

/**
 * @return array{code: int, stdout: string, stderr: string}
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
        return ['code' => 1, 'stdout' => '', 'stderr' => 'unable to start process'];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $code = proc_close($process);

    return [
        'code' => is_int($code) ? $code : 1,
        'stdout' => is_string($stdout) ? $stdout : '',
        'stderr' => is_string($stderr) ? $stderr : '',
    ];
}

function ensureDir(string $path): void
{
    if (is_dir($path)) {
        return;
    }
    if (!mkdir($path, 0775, true) && !is_dir($path)) {
        fail('Unable to create directory: ' . $path);
    }
}

$root = dirname(__DIR__, 2);
$options = parseOptions(array_slice($_SERVER['argv'] ?? [], 1));

$appRelative = (string) ($options['app'] ?? 'app');
$artifactsRelative = (string) ($options['artifacts'] ?? '.artifacts/runbook-drill');
$rollbackRef = trim((string) ($options['rollback_ref'] ?? ''));
$keepArtifacts = (bool) ($options['keep_artifacts'] ?? false);

$appPath = $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $appRelative);
if (!is_dir($appPath)) {
    fail('App directory not found: ' . $appPath);
}

$artifactsPath = $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $artifactsRelative);
ensureDir($artifactsPath);

$timestamp = gmdate('Ymd_His');
$drillRoot = $artifactsPath . DIRECTORY_SEPARATOR . 'drill_' . $timestamp;
ensureDir($drillRoot);
ensureDir($drillRoot . DIRECTORY_SEPARATOR . 'live');
ensureDir($drillRoot . DIRECTORY_SEPARATOR . 'backup');
ensureDir($drillRoot . DIRECTORY_SEPARATOR . 'reports');

$results = [];
$failures = 0;

$addResult = static function (string $step, string $status, string $detail = '') use (&$results, &$failures): void {
    $results[] = [$step, $status, $detail];
    if ($status === 'FAIL') {
        $failures++;
    }
};

// 1) Backup / restore drill (SQLite synthetic dataset)
$liveDb = $drillRoot . DIRECTORY_SEPARATOR . 'live' . DIRECTORY_SEPARATOR . 'drill.sqlite';
$backupDb = $drillRoot . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'drill.sqlite';

try {
    $pdo = new PDO('sqlite:' . $liveDb);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE ledger_entries (id INTEGER PRIMARY KEY AUTOINCREMENT, note TEXT NOT NULL, amount INTEGER NOT NULL, created_at TEXT NOT NULL)');
    $stmt = $pdo->prepare('INSERT INTO ledger_entries (note, amount, created_at) VALUES (:note, :amount, :created_at)');
    if ($stmt === false) {
        throw new RuntimeException('Unable to prepare insert statement.');
    }
    $stmt->execute(['note' => 'invoice', 'amount' => 1250, 'created_at' => gmdate('c')]);
    $stmt->execute(['note' => 'refund', 'amount' => -200, 'created_at' => gmdate('c')]);

    clearstatcache(true, $liveDb);
    $preBackupHash = hash_file('sha256', $liveDb);
    if (!is_string($preBackupHash) || $preBackupHash === '') {
        throw new RuntimeException('Unable to hash live DB before backup.');
    }

    if (!copy($liveDb, $backupDb)) {
        throw new RuntimeException('Unable to create DB backup copy.');
    }

    $pdo->exec('DELETE FROM ledger_entries');
    $stmt->execute(['note' => 'corrupted-entry', 'amount' => 999999, 'created_at' => gmdate('c')]);
    unset($pdo);

    if (!copy($backupDb, $liveDb)) {
        throw new RuntimeException('Unable to restore DB backup copy.');
    }

    $restoredHash = hash_file('sha256', $liveDb);
    if (!is_string($restoredHash) || $restoredHash === '') {
        throw new RuntimeException('Unable to hash restored DB.');
    }

    $verify = new PDO('sqlite:' . $liveDb);
    $count = (int) $verify->query('SELECT COUNT(*) FROM ledger_entries')->fetchColumn();
    unset($verify);

    if ($preBackupHash !== $restoredHash) {
        throw new RuntimeException('Backup/restore hash mismatch.');
    }
    if ($count !== 2) {
        throw new RuntimeException('Unexpected restored row count: ' . $count);
    }

    $backupReport = [
        'live_db' => $liveDb,
        'backup_db' => $backupDb,
        'hash' => $preBackupHash,
        'restored_rows' => $count,
        'timestamp_utc' => gmdate('c'),
    ];
    file_put_contents(
        $drillRoot . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . 'backup-restore.json',
        json_encode($backupReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    $addResult('backup-restore-drill', 'PASS', 'hash verified + restored rows=' . $count);
} catch (Throwable $e) {
    $addResult('backup-restore-drill', 'FAIL', $e->getMessage());
}

// 2) Rollback dry-run drill (git metadata + plan generation)
if (!is_dir($root . DIRECTORY_SEPARATOR . '.git')) {
    $addResult('rollback-drill', 'FAIL', 'git repository not found');
} else {
    $head = runCommand('git rev-parse HEAD', $root);
    $headSha = trim($head['stdout']);
    if ($head['code'] !== 0 || $headSha === '') {
        $addResult('rollback-drill', 'FAIL', 'unable to resolve HEAD');
    } else {
        $target = $rollbackRef;
        if ($target === '') {
            $tags = runCommand('git tag --sort=-creatordate', $root);
            if ($tags['code'] === 0) {
                $lines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $tags['stdout']) ?: [])));
                foreach ($lines as $tag) {
                    $tagSha = runCommand('git rev-list -n 1 ' . escapeshellarg($tag), $root);
                    if ($tagSha['code'] === 0 && trim($tagSha['stdout']) !== '' && trim($tagSha['stdout']) !== $headSha) {
                        $target = $tag;
                        break;
                    }
                }
            }
        }
        if ($target === '') {
            $target = 'HEAD~1';
        }

        $verifyTarget = runCommand('git rev-parse --verify ' . escapeshellarg($target), $root);
        if ($verifyTarget['code'] !== 0) {
            $addResult('rollback-drill', 'FAIL', 'rollback target not found: ' . $target);
        } else {
            $changes = runCommand('git diff --name-only ' . escapeshellarg($target) . '..HEAD', $root);
            $changedFiles = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $changes['stdout']) ?: [])));
            $changedCount = count($changedFiles);

            $plan = [];
            $plan[] = '# Rollback drill plan';
            $plan[] = 'timestamp_utc: ' . gmdate('c');
            $plan[] = 'head: ' . $headSha;
            $plan[] = 'rollback_target: ' . $target;
            $plan[] = 'changed_files_since_target: ' . $changedCount;
            $plan[] = '';
            $plan[] = '## Suggested operator commands (manual)';
            $plan[] = '1. git fetch --tags --prune';
            $plan[] = '2. git checkout ' . $target;
            $plan[] = '3. cd app && composer install --no-dev --optimize-autoloader --prefer-dist';
            $plan[] = '4. cd app && php bin/fnlla routes:cache';
            $plan[] = '5. cd app && php scripts/production-profile-check.php';
            $plan[] = '6. smoke / health probes: /health + /ready';
            $plan[] = '';
            $plan[] = '## Changed files preview';
            if ($changedFiles === []) {
                $plan[] = '- (none)';
            } else {
                foreach ($changedFiles as $file) {
                    $plan[] = '- ' . $file;
                }
            }

            file_put_contents(
                $drillRoot . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . 'rollback-plan.md',
                implode(PHP_EOL, $plan) . PHP_EOL
            );

            $addResult('rollback-drill', 'PASS', 'target=' . $target . ', changed_files=' . $changedCount);
        }
    }
}

// Final summary
$headers = ['Step', 'Status', 'Details'];
$widths = array_map('strlen', $headers);
foreach ($results as $row) {
    $widths[0] = max($widths[0], strlen($row[0]));
    $widths[1] = max($widths[1], strlen($row[1]));
    $widths[2] = max($widths[2], strlen($row[2]));
}

$line = '+' . str_repeat('-', $widths[0] + 2)
    . '+' . str_repeat('-', $widths[1] + 2)
    . '+' . str_repeat('-', $widths[2] + 2) . "+\n";

echo "Runbook drill directory: {$drillRoot}\n";
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

if (!$keepArtifacts && $failures === 0) {
    // Keep only reports for successful runs.
    $reportsDir = $drillRoot . DIRECTORY_SEPARATOR . 'reports';
    $reportFiles = glob($reportsDir . DIRECTORY_SEPARATOR . '*') ?: [];
    $targetDir = $artifactsPath . DIRECTORY_SEPARATOR . 'latest';
    ensureDir($targetDir);
    foreach ($reportFiles as $reportFile) {
        if (!is_file($reportFile)) {
            continue;
        }
        copy($reportFile, $targetDir . DIRECTORY_SEPARATOR . basename($reportFile));
    }
}

exit($failures > 0 ? 1 : 0);
