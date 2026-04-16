<?php

declare(strict_types=1);

$root = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2);
$root = rtrim($root, '/\\');
$storage = $root . '/storage/docs';
$monorepoDocs = dirname($root) . '/documentation/src';
if (!is_dir($monorepoDocs)) {
    $monorepoDocs = dirname($root, 2) . '/documentation/src';
}
$extra = is_dir($monorepoDocs) ? [$monorepoDocs] : [];

return [
    'paths' => [
        'manual' => env('DOCS_MANUAL_PATH', $storage . '/manual'),
        'generated' => env('DOCS_GENERATED_PATH', $storage . '/generated'),
        'published' => env('DOCS_PUBLISHED_PATH', $root . '/resources/docs'),
    ],
    'paths_extra' => $extra,
];

