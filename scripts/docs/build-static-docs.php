<?php

declare(strict_types=1);

/**
 * Build static HTML docs from documentation/src into documentation/build.
 *
 * This generator intentionally does not depend on fnlla (finella) UI assets.
 */

$root = dirname(__DIR__, 2);
$srcDir = $root . '/documentation/src';
$buildDir = $root . '/documentation/build';
$pagesDir = $buildDir . '/pages';

require_once $root . '/packages/docs/src/DocsMarkdownParser.php';
require_once $root . '/packages/docs/src/DocsMarkdownRenderer.php';

if (!is_dir($srcDir)) {
    fwrite(STDERR, "Source directory not found: {$srcDir}\n");
    exit(1);
}

ensureDir($buildDir);
ensureDir($pagesDir);

$renderer = new \Finella\Docs\DocsMarkdownRenderer();

$navigation = [
    ['slug' => 'index', 'label' => 'Index', 'source' => $srcDir . '/index.md', 'target' => $buildDir . '/index.html', 'href' => 'index.html', 'css' => '../assets/documentation.css'],
    ['slug' => 'getting-started', 'label' => 'Getting Started', 'source' => $srcDir . '/getting-started.md', 'target' => $pagesDir . '/getting-started.html', 'href' => 'pages/getting-started.html', 'css' => '../../assets/documentation.css'],
    ['slug' => 'framework', 'label' => 'Framework', 'source' => $srcDir . '/framework.md', 'target' => $pagesDir . '/framework.html', 'href' => 'pages/framework.html', 'css' => '../../assets/documentation.css'],
    ['slug' => 'ai-integrations', 'label' => 'AI & Integrations', 'source' => $srcDir . '/ai-integrations.md', 'target' => $pagesDir . '/ai-integrations.html', 'href' => 'pages/ai-integrations.html', 'css' => '../../assets/documentation.css'],
    ['slug' => 'operations', 'label' => 'Operations & Governance', 'source' => $srcDir . '/operations.md', 'target' => $pagesDir . '/operations.html', 'href' => 'pages/operations.html', 'css' => '../../assets/documentation.css'],
    ['slug' => 'packages', 'label' => 'Packages', 'source' => $srcDir . '/packages.md', 'target' => $pagesDir . '/packages.html', 'href' => 'pages/packages.html', 'css' => '../../assets/documentation.css'],
    ['slug' => 'developer-experience', 'label' => 'Developer Experience', 'source' => $srcDir . '/developer-experience.md', 'target' => $pagesDir . '/developer-experience.html', 'href' => 'pages/developer-experience.html', 'css' => '../../assets/documentation.css'],
];

$built = 0;
foreach ($navigation as $page) {
    $source = $page['source'];
    $target = $page['target'];

    if (!is_file($source)) {
        fwrite(STDERR, "Skipping missing source: {$source}\n");
        continue;
    }

    $markdown = (string) file_get_contents($source);
    $title = extractTitle($markdown, (string) $page['label']);
    $html = $renderer->toHtml($markdown);

    $document = renderDocument(
        $title,
        $html,
        (string) $page['slug'],
        $navigation,
        (string) $page['css']
    );

    ensureDir(dirname($target));
    file_put_contents($target, $document);
    $built++;
}

echo "Static docs built: {$built} file(s)\n";

function ensureDir(string $dir): void
{
    if (is_dir($dir)) {
        return;
    }
    if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
        throw new RuntimeException("Unable to create directory: {$dir}");
    }
}

function extractTitle(string $markdown, string $fallback): string
{
    $lines = preg_split('/\r\n|\n|\r/', $markdown);
    if ($lines === false) {
        return $fallback;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        if (preg_match('/^#{1,6}\s+(.+)$/', $line, $m) === 1) {
            return trim((string) $m[1]);
        }
        if (preg_match('/^\*\*([^*]+)\*\*$/', $line, $m) === 1) {
            return trim((string) $m[1]);
        }
    }

    return $fallback;
}

/**
 * @param array<int, array{slug: string, label: string, source: string, target: string, href: string, css: string}> $navigation
 */
function renderDocument(string $title, string $content, string $activeSlug, array $navigation, string $cssHref): string
{
    $navItems = [];
    foreach ($navigation as $item) {
        $active = $item['slug'] === $activeSlug ? ' is-active' : '';
        $href = $activeSlug === 'index'
            ? $item['href']
            : ($item['slug'] === 'index' ? '../index.html' : basename((string) $item['href']));
        $navItems[] = '<li><a class="fx-docs-link' . $active . '" href="' . e($href) . '">' . e($item['label']) . '</a></li>';
    }

    $year = date('Y');
    $safeTitle = e($title);

    return <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$safeTitle} - Finella Docs</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{$cssHref}">
</head>
<body class="fx-body fx-docs-body">
  <header class="fx-topbar">
    <div class="f-container fx-topbar-inner">
      <div class="fx-brand">
        <div class="fx-brand-mark">Finella</div>
        <span class="fx-brand-pill">Documentation</span>
      </div>
      <div class="fx-topbar-actions"></div>
    </div>
  </header>

  <main class="fx-docs-layout">
    <aside class="fx-docs-sidebar">
      <div class="fx-docs-sidebar-title">Documentation</div>
      <ul class="fx-docs-nav">{$navItems[0]}{$navItems[1]}{$navItems[2]}{$navItems[3]}{$navItems[4]}{$navItems[5]}{$navItems[6]}</ul>
      <div class="fx-docs-sidebar-note">
        Source of truth: <code>documentation/src</code>.
      </div>
    </aside>
    <article class="fx-docs-content">
      <div class="fx-docs-eyebrow">Finella</div>
      <h1>{$safeTitle}</h1>
      <div class="fx-docs-markdown">
        {$content}
      </div>
    </article>
  </main>

  <footer class="fx-docs-footer">
    <div class="f-container">
      <span>&copy; {$year} Finella</span>
      <span>Generated from <code>documentation/src</code>.</span>
    </div>
  </footer>
</body>
</html>
HTML;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

