<?php

declare(strict_types=1);

$args = $argv;
array_shift($args);

$range = null;

for ($i = 0, $count = count($args); $i < $count; $i++) {
    $arg = $args[$i];
    if ($arg === '--range' && isset($args[$i + 1])) {
        $range = trim((string) $args[++$i]);
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        printUsage();
        exit(0);
    }
}

if (!commandAvailable('git')) {
    fwrite(STDERR, "Git not available. Unable to validate commit subjects.\n");
    exit(1);
}

if ($range === null || $range === '') {
    $range = detectRange();
}

if ($range === null || $range === '') {
    echo "No commit range detected; skipping commit subject policy check.\n";
    exit(0);
}

[$ok, $logOutput] = run('git log --pretty=format:%H%x1f%s ' . escapeshellarg($range));
if (!$ok) {
    fwrite(STDERR, "Unable to read git log for range: {$range}\n");
    exit(1);
}

$disallowedPattern = '/^\s*chore(?:\([^)]+\))?:\s+/i';
$violations = [];

$entries = array_values(array_filter(explode("\n", $logOutput), static fn (string $line): bool => trim($line) !== ''));
foreach ($entries as $entry) {
    $parts = explode("\x1f", $entry, 2);
    $hash = trim((string) ($parts[0] ?? ''));
    $subject = trim((string) ($parts[1] ?? ''));
    if ($hash === '' || $subject === '') {
        continue;
    }
    if (preg_match($disallowedPattern, $subject) === 1) {
        $violations[] = [
            'hash' => substr($hash, 0, 7),
            'subject' => $subject,
        ];
    }
}

if ($violations !== []) {
    fwrite(STDERR, "ERROR: commit subjects starting with 'chore:' are not allowed.\n");
    fwrite(STDERR, "Use a plain subject without the conventional prefix.\n");
    fwrite(STDERR, "Invalid commits in range {$range}:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation['hash'] . ' ' . $violation['subject'] . "\n");
    }
    exit(1);
}

echo "Commit subject policy check passed ({$range}).\n";

function printUsage(): void
{
    $usage = <<<TXT
Usage:
  php scripts/ci/check-commit-subject-prefix.php [--range <git-range>]

Rules:
  - Disallow commit subjects beginning with "chore:" or "chore(scope):".
  - Default range is detected from GitHub event payload (push/PR), then falls back to HEAD~1..HEAD.

TXT;

    echo $usage;
}

function detectRange(): ?string
{
    $event = loadGitHubEvent();
    if (is_array($event)) {
        $pullRequestBase = trim((string) ($event['pull_request']['base']['sha'] ?? ''));
        if ($pullRequestBase !== '' && !isZeroSha($pullRequestBase) && gitCommitExists($pullRequestBase)) {
            return $pullRequestBase . '..HEAD';
        }

        $before = trim((string) ($event['before'] ?? ''));
        if ($before !== '' && !isZeroSha($before) && gitCommitExists($before)) {
            return $before . '..HEAD';
        }
    }

    [$hasHead, ] = run('git rev-parse --verify HEAD');
    if (!$hasHead) {
        return null;
    }

    [$hasPrevious, ] = run('git rev-parse --verify HEAD~1');
    if ($hasPrevious) {
        return 'HEAD~1..HEAD';
    }

    return 'HEAD';
}

function loadGitHubEvent(): ?array
{
    $path = getenv('GITHUB_EVENT_PATH');
    if (!is_string($path) || $path === '' || !is_file($path)) {
        return null;
    }

    $raw = file_get_contents($path);
    if (!is_string($raw) || trim($raw) === '') {
        return null;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

function isZeroSha(string $value): bool
{
    return preg_match('/^0{40}$/', $value) === 1;
}

function gitCommitExists(string $sha): bool
{
    [$ok, ] = run('git cat-file -e ' . escapeshellarg($sha . '^{commit}'));
    return $ok;
}

function commandAvailable(string $command): bool
{
    [$ok, ] = run($command . ' --version');
    return $ok;
}

function run(string $command): array
{
    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($command, $descriptors, $pipes);
    if (!is_resource($process)) {
        return [false, ''];
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    return [
        $exitCode === 0,
        trim((string) $stdout . (((string) $stderr) !== '' ? "\n" . (string) $stderr : '')),
    ];
}
