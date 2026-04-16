<?php

declare(strict_types=1);

$args = $argv;
array_shift($args);

$basePath = null;
$currentPath = null;

for ($i = 0; $i < count($args); $i++) {
    if ($args[$i] === '--base' && isset($args[$i + 1])) {
        $basePath = $args[$i + 1];
        $i++;
        continue;
    }
    if ($args[$i] === '--current' && isset($args[$i + 1])) {
        $currentPath = $args[$i + 1];
        $i++;
        continue;
    }
}

if ($basePath === null || $currentPath === null) {
    fwrite(STDERR, "Usage: php scripts/ci/public-api-check-breaking.php --base <path> --current <path>\n");
    exit(1);
}

if (!is_file($basePath)) {
    fwrite(STDERR, "Base snapshot not found: {$basePath}\n");
    exit(0);
}

if (!is_file($currentPath)) {
    fwrite(STDERR, "Current snapshot not found: {$currentPath}\n");
    exit(1);
}

$base = json_decode((string) file_get_contents($basePath), true);
$current = json_decode((string) file_get_contents($currentPath), true);

if (!is_array($base) || !is_array($current)) {
    fwrite(STDERR, "Invalid snapshot JSON.\n");
    exit(1);
}

$baseClasses = $base['classes'] ?? [];
$currentClasses = $current['classes'] ?? [];

$breaking = [];

foreach ($baseClasses as $className => $info) {
    if (!array_key_exists($className, $currentClasses)) {
        $breaking[] = "Removed class: {$className}";
        continue;
    }

    $baseMethods = $info['methods'] ?? [];
    $currentMethods = $currentClasses[$className]['methods'] ?? [];

    $currentMethodSet = array_fill_keys($currentMethods, true);
    foreach ($baseMethods as $method) {
        if (!isset($currentMethodSet[$method])) {
            $breaking[] = "Removed method: {$className}::{$method}()";
        }
    }
}

if ($breaking !== []) {
    fwrite(STDERR, "Breaking public API changes detected:\n");
    foreach ($breaking as $line) {
        fwrite(STDERR, "- {$line}\n");
    }
    exit(1);
}

echo "No breaking public API changes detected.\n";

