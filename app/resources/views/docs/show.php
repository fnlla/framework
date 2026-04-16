<?php

declare(strict_types=1);

$title = isset($title) ? (string) $title : 'Docs';
$doc = isset($doc) ? (string) $doc : '';
$docHtml = isset($docHtml) ? (string) $docHtml : '';
$docs = isset($docs) && is_array($docs) ? $docs : [];
$slug = isset($slug) ? (string) $slug : '';
$php = PHP_VERSION;
$finella = \Finella\Core\Application::VERSION;
$docs = array_values(array_filter($docs, static function (array $item): bool {
    $slug = (string) ($item['slug'] ?? '');
    return $slug !== 'cli';
}));
$active = $slug === 'cli' ? 'cli' : 'docs';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/highlight-monokai.min.css">
    <script src="/js/highlight.min.js" defer></script>
    <script src="/js/app.js" defer></script>
</head>
<body>
<main class="wrap">
    <?php $brand = 'Finella Framework'; ?>
    <?php include __DIR__ . '/../partials/topbar.php'; ?>

    <section class="hero">
        <div>
            <h1 class="title"><?= htmlspecialchars($title) ?></h1>
            <p class="subtitle">
                Local documentation for your app and the Finella packages.
            </p>
        </div>
    </section>

    <?php if ($slug === 'cli'): ?>
        <section class="panel">
            <div class="doc-markdown"><?= $docHtml !== '' ? $docHtml : '<pre class=\"doc\">' . htmlspecialchars($doc) . '</pre>' ?></div>
        </section>
    <?php else: ?>
        <section class="docs-layout">
            <aside class="panel docs-menu">
                <h2>Docs library</h2>
                <?php if ($docs === []): ?>
                    <p class="subtitle">No docs found.</p>
                <?php else: ?>
                    <input class="docs-search" type="search" placeholder="Filter docs..." aria-label="Filter docs" data-docs-filter>
                    <ul class="docs-list" data-docs-list>
                        <?php foreach ($docs as $item): ?>
                            <?php $isActive = $slug === (string) $item['slug']; ?>
                            <li>
                                <a class="link<?= $isActive ? ' is-active' : '' ?>" href="/docs/<?= htmlspecialchars((string) $item['slug']) ?>" data-doc-title="<?= htmlspecialchars((string) $item['title']) ?>">
                                    <?= htmlspecialchars((string) $item['title']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <div class="docs-help">
                    <h3>Help</h3>
                    <ul>
                        <li>Docs load from <code>storage/docs/manual</code>, <code>storage/docs/generated</code>, then <code>resources/docs</code></li>
                        <li>Add Markdown files directly to <code>storage/docs/manual</code> or <code>resources/docs</code></li>
                        <li>Publish compiled docs into <code>resources/docs</code> with <code>php bin/finella docs:generate --publish</code></li>
                        <li>Open pages with <code>/docs/{slug}</code></li>
                    </ul>
                </div>
            </aside>
            <article class="panel docs-content">
                <div class="doc-markdown"><?= $docHtml !== '' ? $docHtml : '<pre class=\"doc\">' . htmlspecialchars($doc) . '</pre>' ?></div>
            </article>
        </section>
    <?php endif; ?>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</main>
</body>
</html>
