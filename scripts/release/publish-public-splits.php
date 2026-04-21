<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$manifestPath = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'release' . DIRECTORY_SEPARATOR . 'distribution-packages.json';

if (!is_file($manifestPath)) {
    fwrite(STDERR, "Missing distribution manifest: {$manifestPath}\n");
    exit(1);
}

$manifest = json_decode((string) file_get_contents($manifestPath), true);
if (!is_array($manifest)) {
    fwrite(STDERR, "Invalid distribution manifest JSON.\n");
    exit(1);
}

$publicCore = $manifest['public_core'] ?? [];
if (!is_array($publicCore) || $publicCore === []) {
    fwrite(STDERR, "Manifest must include non-empty public_core.\n");
    exit(1);
}

$org = 'fnlla';
$ref = 'main';
$tags = [];
$dryRun = false;
$syncMain = true;

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--help' || $arg === '-h') {
        echo "Publish Finella public split repositories from monorepo.\n\n";
        echo "Usage:\n";
        echo "  php scripts/release/publish-public-splits.php [--org=fnlla] [--ref=main] [--tags=v3.0.1,v3.0.2] [--no-main] [--dry-run]\n\n";
        echo "Examples:\n";
        echo "  php scripts/release/publish-public-splits.php --org=fnlla\n";
        echo "  php scripts/release/publish-public-splits.php --org=fnlla --tags=v3.0.1\n";
        echo "  php scripts/release/publish-public-splits.php --org=fnlla --tags=v3.0.0,v3.0.1 --dry-run\n";
        exit(0);
    }

    if ($arg === '--dry-run') {
        $dryRun = true;
        continue;
    }

    if ($arg === '--no-main') {
        $syncMain = false;
        continue;
    }

    if (str_starts_with($arg, '--org=')) {
        $org = trim(substr($arg, strlen('--org=')));
        continue;
    }

    if (str_starts_with($arg, '--ref=')) {
        $ref = trim(substr($arg, strlen('--ref=')));
        continue;
    }

    if (str_starts_with($arg, '--tags=')) {
        $rawTags = trim(substr($arg, strlen('--tags=')));
        if ($rawTags !== '') {
            $tags = array_values(array_filter(array_map('trim', explode(',', $rawTags)), static fn (string $tag): bool => $tag !== ''));
        }
        continue;
    }

    fwrite(STDERR, "Unknown argument: {$arg}\n");
    exit(1);
}

if ($org === '') {
    fwrite(STDERR, "--org cannot be empty.\n");
    exit(1);
}

if ($ref === '') {
    fwrite(STDERR, "--ref cannot be empty.\n");
    exit(1);
}

/**
 * @return array{code:int,stdout:string}
 */
function run(array $parts, bool $dryRun = false): array
{
    $command = implode(' ', array_map('escapeshellarg', $parts));
    if ($dryRun) {
        echo "[dry-run] {$command}\n";
        return ['code' => 0, 'stdout' => ''];
    }

    exec($command . ' 2>&1', $output, $exitCode);
    return ['code' => $exitCode, 'stdout' => trim(implode("\n", $output))];
}

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}

/**
 * @return array<int,array{package:string,slug:string,prefix:string,repo:string}>
 */
function resolvePublicTargets(array $publicCore): array
{
    $targets = [];
    foreach ($publicCore as $package) {
        $package = (string) $package;
        if (!str_starts_with($package, 'finella/')) {
            fail("Invalid package in public_core: {$package}");
        }

        $slug = substr($package, strlen('finella/'));
        if ($slug === '') {
            fail("Invalid package slug in public_core: {$package}");
        }

        $prefix = $slug === 'framework' ? 'framework' : 'packages/' . $slug;
        $repo = 'pkg-' . $slug;

        $targets[] = [
            'package' => $package,
            'slug' => $slug,
            'prefix' => $prefix,
            'repo' => $repo,
        ];
    }

    return $targets;
}

$targets = resolvePublicTargets($publicCore);

echo "Public split targets: " . count($targets) . "\n";
echo "Org: {$org}\n";
echo "Ref: {$ref}\n";
echo "Sync main: " . ($syncMain ? 'yes' : 'no') . "\n";
echo "Tags: " . ($tags === [] ? '(none)' : implode(', ', $tags)) . "\n\n";

if (!$dryRun) {
    $insideRepo = run(['git', 'rev-parse', '--is-inside-work-tree']);
    if ($insideRepo['code'] !== 0 || strtolower($insideRepo['stdout']) !== 'true') {
        fail('This script must run inside a Git repository.');
    }
}

if ($syncMain) {
    echo "Syncing main branches...\n";
    foreach ($targets as $target) {
        echo "- {$target['package']} -> {$org}/{$target['repo']} (prefix {$target['prefix']})\n";

        $split = run(['git', 'subtree', 'split', '--prefix=' . $target['prefix'], $ref], $dryRun);
        if ($split['code'] !== 0) {
            fail("Subtree split failed for {$target['package']} from {$ref}.\n{$split['stdout']}");
        }
        $sha = trim($split['stdout']);

        $checkComposer = run(['git', 'cat-file', '-e', $sha . ':composer.json'], $dryRun);
        if ($checkComposer['code'] !== 0) {
            fail("Split root for {$target['package']} does not contain composer.json ({$sha}).");
        }

        $push = run(
            ['git', 'push', "https://github.com/{$org}/{$target['repo']}.git", $sha . ':refs/heads/main', '--force'],
            $dryRun
        );
        if ($push['code'] !== 0) {
            fail("Push failed for {$target['package']} main branch.\n{$push['stdout']}");
        }
    }
    echo "\n";
}

if ($tags !== []) {
    echo "Syncing tags...\n";
    foreach ($tags as $tag) {
        echo "- Tag {$tag}\n";
        foreach ($targets as $target) {
            $split = run(['git', 'subtree', 'split', '--prefix=' . $target['prefix'], $tag], $dryRun);
            if ($split['code'] !== 0) {
                fail("Subtree split failed for {$target['package']} tag {$tag}.\n{$split['stdout']}");
            }
            $sha = trim($split['stdout']);

            $checkComposer = run(['git', 'cat-file', '-e', $sha . ':composer.json'], $dryRun);
            if ($checkComposer['code'] !== 0) {
                fail("Split root for {$target['package']} tag {$tag} does not contain composer.json ({$sha}).");
            }

            $push = run(
                ['git', 'push', "https://github.com/{$org}/{$target['repo']}.git", $sha . ':refs/tags/' . $tag, '--force'],
                $dryRun
            );
            if ($push['code'] !== 0) {
                fail("Tag push failed for {$target['package']} {$tag}.\n{$push['stdout']}");
            }
        }
    }
    echo "\n";
}

echo "Public split publish completed.\n";
