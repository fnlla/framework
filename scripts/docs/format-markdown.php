<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$args = $_SERVER['argv'] ?? [];
array_shift($args);

$checkOnly = false;
$paths = [];
$profile = 'project';
$scope = 'all';

for ($i = 0, $count = count($args); $i < $count; $i++) {
    $arg = $args[$i];
    if ($arg === '--root' && isset($args[$i + 1])) {
        $root = rtrim((string) $args[++$i], "/\\");
        continue;
    }
    if ($arg === '--path' && isset($args[$i + 1])) {
        $paths[] = (string) $args[++$i];
        continue;
    }
    if ($arg === '--profile' && isset($args[$i + 1])) {
        $profile = strtolower(trim((string) $args[++$i]));
        continue;
    }
    if ($arg === '--scope' && isset($args[$i + 1])) {
        $scope = strtolower(trim((string) $args[++$i]));
        continue;
    }
    if ($arg === '--check') {
        $checkOnly = true;
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        printUsage();
        exit(0);
    }
}

$validProfiles = ['project', 'release'];
if (!in_array($profile, $validProfiles, true)) {
    fwrite(STDERR, "Invalid --profile value. Allowed: " . implode(', ', $validProfiles) . "\n");
    exit(1);
}

$validScopes = ['all', 'github'];
if (!in_array($scope, $validScopes, true)) {
    fwrite(STDERR, "Invalid --scope value. Allowed: " . implode(', ', $validScopes) . "\n");
    exit(1);
}

$rootPath = realpath($root);
if ($rootPath === false || !is_dir($rootPath)) {
    fwrite(STDERR, "Invalid root path: {$root}\n");
    exit(1);
}

$targets = $paths !== [] ? collectExplicitPaths($rootPath, $paths, $scope) : collectTargets($rootPath, $scope);
if ($targets === []) {
    fwrite(STDERR, "No matching markdown files found.\n");
    exit(1);
}

$changed = [];
foreach ($targets as $file) {
    $content = file_get_contents($file);
    if (!is_string($content)) {
        continue;
    }

    $effectiveProfile = resolveProfileForFile($file, $profile);
    $formatted = $effectiveProfile === 'release' ? formatReleaseStyle($content) : formatProjectStyle($content);
    if ($formatted !== $content) {
        $changed[] = $file;
        if (!$checkOnly) {
            file_put_contents($file, $formatted);
        }
    }
}

if ($checkOnly) {
    if ($changed !== []) {
        fwrite(STDERR, "Markdown formatting needed:\n");
        foreach ($changed as $file) {
            fwrite(STDERR, ' - ' . normalizePath($file, $rootPath) . "\n");
        }
        exit(1);
    }
    echo "Markdown formatting OK.\n";
    exit(0);
}

echo 'Formatted ' . count($changed) . " file(s).\n";
exit(0);

function printUsage(): void
{
    $usage = <<<TXT
Usage:
  php scripts/docs/format-markdown.php [options]

Options:
  --root <path>        Root directory to scan (default: repo root)
  --path <file/dir>    Explicit path to format (repeatable)
  --scope <all|github> Target set: all markdown or GitHub-facing docs
  --profile <name>     Formatting profile: project (default), release
  --check              Check mode (no changes, exit 1 if formatting needed)
  --help, -h           Show this help

TXT;
    echo $usage;
}

function collectExplicitPaths(string $root, array $paths, string $scope): array
{
    $files = [];
    foreach ($paths as $path) {
        $full = realpath($path);
        if ($full === false) {
            $full = realpath($root . DIRECTORY_SEPARATOR . ltrim($path, "/\\"));
        }
        if ($full === false) {
            continue;
        }
        if (is_dir($full)) {
            $files = array_merge($files, collectTargets($full, $scope));
            continue;
        }
        if (!is_file($full)) {
            continue;
        }
        $filename = strtoupper((string) pathinfo($full, PATHINFO_BASENAME));
        if ($scope === 'github') {
            $include = ['README.MD', 'SECURITY.MD', 'CODE_OF_CONDUCT.MD', 'CONTRIBUTING.MD', 'LICENSE.MD'];
            if (!in_array($filename, $include, true)) {
                continue;
            }
        } elseif (!str_ends_with(strtolower($filename), '.md')) {
            continue;
        }

        $files[] = $full;
    }

    return array_values(array_unique($files));
}

function collectTargets(string $root, string $scope): array
{
    $skipRoots = [
        realpath($root . '/.git'),
        realpath($root . '/.artifacts'),
        realpath($root . '/vendor'),
        realpath($root . '/framework/vendor'),
        realpath($root . '/app/vendor'),
        realpath($root . '/tools/harness/vendor'),
        realpath($root . '/node_modules'),
        realpath($root . '/.composer-cache'),
        realpath($root . '/.composer-home'),
        realpath($root . '/app/resources/docs'),
        realpath($root . '/app/storage/docs'),
        realpath($root . '/documentation/build'),
    ];
    $skipRoots = array_filter($skipRoots, static fn ($path): bool => is_string($path));

    $githubNames = ['README.MD', 'SECURITY.MD', 'CODE_OF_CONDUCT.MD', 'CONTRIBUTING.MD', 'LICENSE.MD'];

    $targets = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $path = $fileInfo->getPathname();
        $real = realpath($path) ?: $path;

        foreach ($skipRoots as $skip) {
            $normalizedReal = str_replace('\\', '/', $real);
            $normalizedSkip = str_replace('\\', '/', $skip);
            if (str_starts_with($normalizedReal, $normalizedSkip . '/')) {
                continue 2;
            }
        }

        $filename = strtoupper($fileInfo->getFilename());
        if ($scope === 'github') {
            if (!in_array($filename, $githubNames, true)) {
                continue;
            }
        } else {
            if (!str_ends_with(strtolower($fileInfo->getFilename()), '.md')) {
                continue;
            }
        }

        $targets[] = $real;
    }

    return array_values(array_unique($targets));
}

function formatProjectStyle(string $content): string
{
    $content = normalizeText($content);
    $lines = explode("\n", $content);
    $out = [];
    $inCode = false;
    $inFrontMatter = false;
    $previousBlank = false;

    foreach ($lines as $index => $line) {
        $line = rtrim($line, " \t");
        $trimmed = trim($line);

        if ($index === 0 && $trimmed === '---') {
            $inFrontMatter = true;
            $out[] = $line;
            $previousBlank = false;
            continue;
        }
        if ($inFrontMatter) {
            $out[] = $line;
            if ($trimmed === '---') {
                $inFrontMatter = false;
            }
            $previousBlank = ($trimmed === '');
            continue;
        }

        if (str_starts_with($trimmed, '```')) {
            $inCode = !$inCode;
            $out[] = $line;
            $previousBlank = false;
            continue;
        }

        if ($inCode) {
            $out[] = $line;
            $previousBlank = false;
            continue;
        }

        if (preg_match('/^(\s*)(#{1,6})\s*(\S.*)$/', $line, $match) === 1) {
            $line = $match[1] . $match[2] . ' ' . $match[3];
            $trimmed = trim($line);
        }

        if ($trimmed === '') {
            if (!$previousBlank) {
                $out[] = '';
                $previousBlank = true;
            }
            continue;
        }

        $out[] = $line;
        $previousBlank = false;
    }

    while ($out !== [] && trim((string) end($out)) === '') {
        array_pop($out);
    }

    return implode("\n", $out) . "\n";
}

function formatReleaseStyle(string $content): string
{
    $content = normalizeText($content);
    $lines = explode("\n", $content);
    $out = [];
    $inCode = false;
    $inFrontMatter = false;

    foreach ($lines as $index => $line) {
        $trimmed = trim($line);

        if ($index === 0 && $trimmed === '---') {
            $inFrontMatter = true;
            $out[] = $line;
            continue;
        }
        if ($inFrontMatter) {
            $out[] = $line;
            if ($trimmed === '---') {
                $inFrontMatter = false;
            }
            continue;
        }

        if (str_starts_with($trimmed, '```')) {
            $inCode = !$inCode;
            $out[] = $line;
            continue;
        }

        if ($inCode) {
            $out[] = $line;
            continue;
        }

        if ($trimmed === '') {
            if ($out !== [] && trim((string) end($out)) !== '') {
                $out[] = '';
            }
            continue;
        }

        if (preg_match('/^(\s*)#{1,6}\s+(.+)$/', $line, $match) === 1) {
            $indent = $match[1];
            $title = uppercaseOutsideBackticks(trim((string) $match[2]));
            if ($out !== [] && trim((string) end($out)) !== '') {
                $out[] = '';
            }
            $out[] = $indent . '**' . $title . '**';
            continue;
        }

        if (preg_match('/^(\s*)\*\*([^*]+)\*\*\s*$/', $line, $match) === 1) {
            $indent = $match[1];
            $label = uppercaseOutsideBackticks(trim((string) $match[2]));
            if ($out !== [] && trim((string) end($out)) !== '') {
                $out[] = '';
            }
            $out[] = $indent . '**' . $label . '**';
            continue;
        }

        if (preg_match('/^(\s*)\d+[.)]\s+(.*)$/', $line, $match) === 1) {
            $indent = $match[1];
            $item = $match[2];
            $out[] = $indent . '**-** ' . $item;
            continue;
        }

        if (preg_match('/^(\s*)([-*])\s+(.*)$/', $line, $match) === 1) {
            $indent = $match[1];
            $item = $match[3];
            $out[] = $indent . '**-** ' . $item;
            continue;
        }

        if (preg_match('/^(\s*)\*\*-\*\*\s+(.*)$/', $line, $match) === 1) {
            $indent = $match[1];
            $item = $match[2];
            $out[] = $indent . '**-** ' . $item;
            continue;
        }

        if (preg_match('/^(\s*)\\\\-\s+(.*)$/', $line, $match) === 1) {
            $indent = $match[1];
            $item = $match[2];
            $out[] = $indent . '**-** ' . $item;
            continue;
        }

        $out[] = $line;
    }

    while ($out !== [] && trim((string) end($out)) === '') {
        array_pop($out);
    }

    return implode("\n", $out) . "\n";
}

function normalizeText(string $content): string
{
    if (str_starts_with($content, "\xEF\xBB\xBF")) {
        $content = substr($content, 3);
    }
    return str_replace(["\r\n", "\r"], "\n", $content);
}

function uppercaseOutsideBackticks(string $value): string
{
    $parts = explode('`', $value);
    for ($i = 0, $count = count($parts); $i < $count; $i += 2) {
        $parts[$i] = function_exists('mb_strtoupper')
            ? mb_strtoupper($parts[$i], 'UTF-8')
            : strtoupper($parts[$i]);
    }

    return trim(implode('`', $parts));
}

function normalizePath(string $path, string $root): string
{
    $path = str_replace('\\', '/', $path);
    $root = rtrim(str_replace('\\', '/', $root), '/');
    if (str_starts_with($path, $root . '/')) {
        return substr($path, strlen($root) + 1);
    }
    return $path;
}

function resolveProfileForFile(string $file, string $requestedProfile): string
{
    if ($requestedProfile !== 'project') {
        return $requestedProfile;
    }

    $normalized = strtolower(str_replace('\\', '/', $file));
    if (preg_match('~(?:^|/)documentation/(?:src|app)/.+\.md$~', $normalized) === 1) {
        return 'release';
    }

    return $requestedProfile;
}
