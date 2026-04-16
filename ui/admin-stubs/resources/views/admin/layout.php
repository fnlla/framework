<?php

declare(strict_types=1);

$config = $app->config();
$brand = (string) $config->get('admin.brand', 'Admin');
$title = $title ?? $brand;
$nav = $config->get('admin.nav', []);
if (!is_array($nav)) {
    $nav = [];
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('admin/admin.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="admin">
<div class="admin-shell">
    <aside class="admin-side">
        <?php $navItems = $nav; ?>
        <?php include __DIR__ . '/partials/nav.php'; ?>
    </aside>
    <main class="admin-main">
        <header class="admin-header">
            <div>
                <p class="admin-kicker">Admin</p>
                <h1><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
            <div class="admin-actions">
                <a class="admin-button" href="<?= htmlspecialchars(url('/'), ENT_QUOTES, 'UTF-8') ?>">View site</a>
            </div>
        </header>
        <section class="admin-content">
            <?= $content ?? '' ?>
        </section>
    </main>
</div>
</body>
</html>
