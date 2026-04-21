<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$args = $_SERVER['argv'] ?? [];
array_shift($args);

$versionArg = null;
$tag = null;
$title = null;
$target = null;
$changelogPath = $root . DIRECTORY_SEPARATOR . 'CHANGELOG.md';
$dryRun = false;
$draft = false;
$prerelease = false;

for ($i = 0, $count = count($args); $i < $count; $i++) {
    $arg = $args[$i];
    if ($arg === '--version' && isset($args[$i + 1])) {
        $versionArg = trim((string) $args[++$i]);
        continue;
    }
    if ($arg === '--tag' && isset($args[$i + 1])) {
        $tag = trim((string) $args[++$i]);
        continue;
    }
    if ($arg === '--title' && isset($args[$i + 1])) {
        $title = trim((string) $args[++$i]);
        continue;
    }
    if ($arg === '--target' && isset($args[$i + 1])) {
        $target = trim((string) $args[++$i]);
        continue;
    }
    if ($arg === '--changelog' && isset($args[$i + 1])) {
        $changelogPath = trim((string) $args[++$i]);
        continue;
    }
    if ($arg === '--dry-run') {
        $dryRun = true;
        continue;
    }
    if ($arg === '--draft') {
        $draft = true;
        continue;
    }
    if ($arg === '--prerelease') {
        $prerelease = true;
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        printUsage();
        exit(0);
    }
}

if ($versionArg === null || $versionArg === '') {
    printUsage();
    exit(1);
}

$normalizedVersion = ltrim($versionArg, 'vV');
if ($normalizedVersion === '') {
    fwrite(STDERR, "Invalid version value.\n");
    exit(1);
}

$tag = $tag !== null && $tag !== '' ? $tag : 'v' . $normalizedVersion;
$title = $title !== null && $title !== '' ? $title : defaultReleaseTitle($normalizedVersion);

if (!is_file($changelogPath)) {
    fwrite(STDERR, "Changelog file not found: {$changelogPath}\n");
    exit(1);
}

$raw = file_get_contents($changelogPath);
if (!is_string($raw) || $raw === '') {
    fwrite(STDERR, "Unable to read changelog: {$changelogPath}\n");
    exit(1);
}

$content = normalizeText($raw);
$notes = extractReleaseNotes($content, $normalizedVersion);
if ($notes === null) {
    fwrite(STDERR, "Release section [{$normalizedVersion}] not found in {$changelogPath}\n");
    exit(1);
}

$notes = formatReleaseNotes($notes);
validateReleaseNotes($notes);

if ($dryRun) {
    echo $notes . PHP_EOL;
    exit(0);
}

$tmpFile = tempnam(sys_get_temp_dir(), 'fnlla-release-');
if ($tmpFile === false) {
    fwrite(STDERR, "Unable to create temporary file for release notes.\n");
    exit(1);
}

$notesFile = $tmpFile . '.md';
if (!@rename($tmpFile, $notesFile)) {
    $notesFile = $tmpFile;
}

if (file_put_contents($notesFile, $notes) === false) {
    @unlink($notesFile);
    fwrite(STDERR, "Unable to write temporary release notes file.\n");
    exit(1);
}

$exists = commandSucceeds(
    'gh release view '
    . escapeshellarg($tag)
    . ' --json tagName'
);

$command = buildGhCommand($exists, $tag, $title, $notesFile, $target, $draft, $prerelease);
$exitCode = passthruWithExitCode($command);

@unlink($notesFile);

if ($exitCode !== 0) {
    exit($exitCode);
}

exit(0);

function printUsage(): void
{
    $usage = <<<TXT
Usage:
  php scripts/release/publish-release.php --version <x.y.z|vx.y.z> [options]

Options:
  --tag <tag>          Release tag (default: v<version>)
  --title <title>      Release title (default: <version> - <product>)
  --target <ref>       Target commit/branch when creating release
  --changelog <path>   Changelog path (default: CHANGELOG.md)
  --draft              Create release as draft
  --prerelease         Mark as prerelease
  --dry-run            Print extracted notes and exit
  --help, -h           Show this help

TXT;
    echo $usage;
}

function normalizeText(string $content): string
{
    if (str_starts_with($content, "\xEF\xBB\xBF")) {
        $content = substr($content, 3);
    }
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    return $content;
}

function extractReleaseNotes(string $changelog, string $version): ?string
{
    $sections = parseReleaseSections($changelog);
    foreach ($sections as $section) {
        if (($section['version'] ?? '') !== $version) {
            continue;
        }
        $body = trim((string) ($section['body'] ?? ''));
        return $body !== '' ? $body : null;
    }

    return null;
}

function validateReleaseNotes(string $notes): void
{
    if (strpos($notes, "\xEF\xBF\xBD") !== false) {
        fwrite(STDERR, "Release notes contain replacement characters (�). Fix the source text first.\n");
        exit(1);
    }
    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $notes) === 1) {
        fwrite(STDERR, "Release notes contain control characters. Fix the source text first.\n");
        exit(1);
    }
}

function formatReleaseNotes(string $notes): string
{
    $labels = ['HIGHLIGHTS', 'ADDED', 'CHANGED', 'DEPRECATED', 'REMOVED', 'FIXED', 'SECURITY'];
    $labelMap = [];
    foreach ($labels as $label) {
        $labelMap[strtolower($label)] = $label;
    }

    $lines = explode("\n", normalizeText($notes));
    $sections = array_fill_keys($labels, []);
    $intro = [];
    $current = null;
    $inCode = false;

    $labelPattern = '/^\\s*(#{1,6}\\s*)?(\\*\\*)?([A-Za-z][A-Za-z\\s-]*)(\\*\\*)?\\s*:?$/';

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if (str_starts_with($trimmed, '```')) {
            $inCode = !$inCode;
            if ($current === null) {
                $intro[] = $line;
            } else {
                $sections[$current][] = $line;
            }
            continue;
        }

        if (!$inCode && $trimmed !== '' && preg_match($labelPattern, $trimmed, $match) === 1) {
            $key = strtolower(trim((string) ($match[3] ?? '')));
            if (isset($labelMap[$key])) {
                $current = $labelMap[$key];
                continue;
            }
        }

        if ($trimmed === '') {
            continue;
        }

        $target = $current === null ? $intro : $sections[$current];
        $normalized = $line;

        if (!$inCode) {
            $bullet = ltrim($line);
            if (str_starts_with($bullet, '**-** ')) {
                $normalized = '**-** ' . substr($bullet, 6);
            } elseif (str_starts_with($bullet, '\\- ')) {
                $normalized = '**-** ' . substr($bullet, 3);
            } elseif (str_starts_with($bullet, '- ')) {
                $normalized = '**-** ' . substr($bullet, 2);
            } elseif (str_starts_with($bullet, '* ')) {
                $normalized = '**-** ' . substr($bullet, 2);
            }
        }

        if ($current === null) {
            $intro[] = $normalized;
        } else {
            $sections[$current][] = $normalized;
        }
    }

    $output = [];
    if ($intro !== []) {
        $output = $intro;
    }

    foreach ($labels as $label) {
        $content = $sections[$label] ?? [];
        if ($content === []) {
            continue;
        }
        if ($output !== []) {
            $output[] = '';
        }
        $output[] = '**' . $label . '**';
        foreach ($content as $line) {
            $output[] = $line;
        }
    }

    while ($output !== [] && trim((string) end($output)) === '') {
        array_pop($output);
    }

    return rtrim(implode("\n", $output)) . "\n";
}

function buildGhCommand(
    bool $exists,
    string $tag,
    string $title,
    string $notesFile,
    ?string $target,
    bool $draft,
    bool $prerelease
): string {
    if ($exists) {
        return implode(' ', [
            'gh release edit',
            escapeshellarg($tag),
            '--title',
            escapeshellarg($title),
            '--notes-file',
            escapeshellarg($notesFile),
        ]);
    }

    $parts = [
        'gh release create',
        escapeshellarg($tag),
        '--title',
        escapeshellarg($title),
        '--notes-file',
        escapeshellarg($notesFile),
    ];

    if ($target !== null && $target !== '') {
        $parts[] = '--target';
        $parts[] = escapeshellarg($target);
    }
    if ($draft) {
        $parts[] = '--draft';
    }
    if ($prerelease) {
        $parts[] = '--prerelease';
    }

    return implode(' ', $parts);
}

function commandSucceeds(string $command): bool
{
    $exitCode = runQuiet($command);
    return $exitCode === 0;
}

function defaultReleaseTitle(string $version): string
{
    $slug = detectRepositorySlug();
    if ($slug === 'fnlla/framework') {
        return $version . ' - Fnlla Framework';
    }
    if ($slug === 'fnlla/fnlla') {
        return $version . ' - FNLLA Starter';
    }

    return $version;
}

function detectRepositorySlug(): string
{
    $remote = trim(runCapture('git config --get remote.origin.url'));
    if ($remote === '') {
        return '';
    }

    if (preg_match('/github\\.com[:\\/](?<slug>[^\\s]+?)(?:\\.git)?$/i', $remote, $match) === 1) {
        $slug = trim((string) ($match['slug'] ?? ''), '/');
        return strtolower($slug);
    }

    return '';
}

/**
 * @return list<array{version:string,body:string}>
 */
function parseReleaseSections(string $changelog): array
{
    $sections = [];
    $lines = explode("\n", normalizeText($changelog));
    $currentVersion = null;
    $currentBody = [];

    foreach ($lines as $line) {
        $version = extractReleaseVersion($line);
        if ($version !== null) {
            if ($currentVersion !== null) {
                $sections[] = [
                    'version' => ltrim($currentVersion, 'vV'),
                    'body' => trim(implode("\n", $currentBody)),
                ];
            }
            $currentVersion = $version;
            $currentBody = [];
            continue;
        }

        if ($currentVersion !== null) {
            $currentBody[] = $line;
        }
    }

    if ($currentVersion !== null) {
        $sections[] = [
            'version' => ltrim($currentVersion, 'vV'),
            'body' => trim(implode("\n", $currentBody)),
        ];
    }

    return $sections;
}

function extractReleaseVersion(string $line): ?string
{
    $trimmed = trim($line);
    if (preg_match('/^##\s+\[([^\]]+)\][^\n]*$/', $trimmed, $match) === 1) {
        $value = trim((string) ($match[1] ?? ''));
        return $value !== '' ? $value : null;
    }

    if (preg_match('/^\*\*\[([^\]]+)\][^*]*\*\*$/', $trimmed, $match) === 1) {
        $value = trim((string) ($match[1] ?? ''));
        return $value !== '' ? $value : null;
    }

    return null;
}

function passthruWithExitCode(string $command): int
{
    passthru($command, $exitCode);
    return (int) $exitCode;
}

function runQuiet(string $command): int
{
    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptors, $pipes);
    if (!is_resource($process)) {
        return 1;
    }

    if (isset($pipes[1]) && is_resource($pipes[1])) {
        stream_get_contents($pipes[1]);
        fclose($pipes[1]);
    }
    if (isset($pipes[2]) && is_resource($pipes[2])) {
        stream_get_contents($pipes[2]);
        fclose($pipes[2]);
    }

    return (int) proc_close($process);
}

function runCapture(string $command): string
{
    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptors, $pipes);
    if (!is_resource($process)) {
        return '';
    }

    $stdout = '';
    if (isset($pipes[1]) && is_resource($pipes[1])) {
        $stdout = (string) stream_get_contents($pipes[1]);
        fclose($pipes[1]);
    }
    if (isset($pipes[2]) && is_resource($pipes[2])) {
        stream_get_contents($pipes[2]);
        fclose($pipes[2]);
    }

    $exitCode = (int) proc_close($process);
    if ($exitCode !== 0) {
        return '';
    }

    return trim($stdout);
}
