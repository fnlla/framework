<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$registryPath = $root . DIRECTORY_SEPARATOR . 'documentation' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'operations.md';

if (!is_file($registryPath)) {
    fwrite(STDERR, "Missing deprecations registry: {$registryPath}\n");
    exit(1);
}

$registryContent = (string) file_get_contents($registryPath);
$registryIds = [];
$registryBlocks = [];
[$registryIds, $registryBlocks] = parseRegistryEntries($registryContent);

$migrationHeadings = [];
if (preg_match_all('/^(?:##\s+Migration\s+|\*\*MIGRATION\s+)(DEP-\d{4}-\d{2})(?:\*\*)?$/mi', $registryContent, $migrationMatches) > 0) {
    foreach ($migrationMatches[1] as $migrationId) {
        $migrationHeadings[$migrationId] = true;
    }
}

$targets = [];
$targets[] = $root . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'src';
$packagesDir = $root . DIRECTORY_SEPARATOR . 'packages';
if (is_dir($packagesDir)) {
    foreach (glob($packagesDir . DIRECTORY_SEPARATOR . '*') as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        $base = basename($dir);
        if ($base === '_package-template') {
            continue;
        }
        $targets[] = $dir . DIRECTORY_SEPARATOR . 'src';
    }
}

$foundIds = [];
$missingTag = [];

foreach ($targets as $target) {
    if (!is_dir($target)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        if (strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        if (!is_string($content) || !str_contains($content, '@deprecated')) {
            continue;
        }

        $lines = preg_split('/\r?\n/', $content) ?: [];
        foreach ($lines as $line) {
            if (strpos($line, '@deprecated') === false) {
                continue;
            }

            if (preg_match('/@deprecated\s+\[(DEP-\d{4}-\d{2})\]/', $line, $match) === 1) {
                $id = '[' . $match[1] . ']';
                $foundIds[$id] = true;
                continue;
            }

            $missingTag[] = $file->getPathname() . ': ' . trim($line);
        }
    }
}

$errors = false;

if ($missingTag !== []) {
    fwrite(STDERR, "Deprecated tags without registry ID:\n");
    foreach ($missingTag as $line) {
        fwrite(STDERR, ' - ' . $line . "\n");
    }
    $errors = true;
}

$missingInRegistry = array_diff_key($foundIds, $registryIds);
if ($missingInRegistry !== []) {
    fwrite(STDERR, "Deprecated IDs not listed in documentation/src/operations.md:\n");
    foreach (array_keys($missingInRegistry) as $id) {
        fwrite(STDERR, ' - ' . $id . "\n");
    }
    $errors = true;
}

$missingInCode = array_diff_key($registryIds, $foundIds);
if ($missingInCode !== []) {
    fwrite(STDERR, "Registry IDs not found in codebase:\n");
    foreach (array_keys($missingInCode) as $id) {
        fwrite(STDERR, ' - ' . $id . "\n");
    }
    $errors = true;
}

if ($errors) {
    exit(1);
}

$invalidRegistry = [];
foreach ($registryBlocks as $id => $block) {
    if (!preg_match('/Replacement:\s*/', $block)) {
        $invalidRegistry[] = $id . ' missing Replacement';
    }
    if (!preg_match('/Removal:\s*/', $block)) {
        $invalidRegistry[] = $id . ' missing Removal';
    }
    if (!preg_match('/Migration:\s*/', $block)) {
        $invalidRegistry[] = $id . ' missing Migration';
        continue;
    }

    $plainId = trim($id, '[]');
    if (!isset($migrationHeadings[$plainId])) {
        $invalidRegistry[] = $id . ' migration section missing: ' . $plainId;
    }
}

if ($invalidRegistry !== []) {
    fwrite(STDERR, "Invalid entries in documentation/src/operations.md:\n");
    foreach ($invalidRegistry as $line) {
        fwrite(STDERR, ' - ' . $line . "\n");
    }
    exit(1);
}

echo "Deprecations registry OK.\n";

function parseRegistryEntries(string $content): array
{
    $lines = preg_split('/\r?\n/', $content) ?: [];
    $ids = [];
    $blocks = [];
    $currentId = null;
    $currentLines = [];

    foreach ($lines as $line) {
        $maybeId = extractRegistryId($line);
        if ($maybeId !== null) {
            if ($currentId !== null) {
                $blocks[$currentId] = implode("\n", $currentLines);
            }
            $currentId = '[' . $maybeId . ']';
            $ids[$currentId] = true;
            $currentLines = [];
            continue;
        }

        if ($currentId === null) {
            continue;
        }

        $currentLines[] = $line;
    }

    if ($currentId !== null) {
        $blocks[$currentId] = implode("\n", $currentLines);
    }

    return [$ids, $blocks];
}

function extractRegistryId(string $line): ?string
{
    if (preg_match('/^\s*###\s+\[(DEP-\d{4}-\d{2})\]/', $line, $match) === 1) {
        return $match[1];
    }

    if (preg_match('/^\s*\*\*\[(DEP-\d{4}-\d{2})\][^*]*\*\*\s*$/', $line, $match) === 1) {
        return $match[1];
    }

    return null;
}

