<?php

declare(strict_types=1);

$brand = isset($brand) ? (string) $brand : (string) env('APP_NAME', 'Finella');
$title = isset($title) ? (string) $title : $brand;
$active = isset($active) ? (string) $active : '';

$envValue = strtolower((string) env('APP_ENV', 'prod'));
$debugValue = strtolower((string) env('APP_DEBUG', '0'));
$isDev = in_array($envValue, ['local', 'dev', 'development', 'test'], true) || in_array($debugValue, ['1', 'true', 'yes'], true);
$adminEnabled = (bool) env('ADMIN_ENABLED', false);
$adminDevEnabled = (bool) env('ADMIN_DEV_ENABLED', false);
$showAdmin = $adminEnabled || ($isDev && $adminDevEnabled);
$adminAuth = class_exists(\App\Services\AdminAuthService::class) ? new \App\Services\AdminAuthService() : null;
$adminLoggedIn = $adminAuth ? $adminAuth->isAuthenticated() : false;
$adminLoginRequired = $adminAuth ? $adminAuth->requiresLogin() : false;

$nav = $nav ?? [
    ['label' => 'Home', 'href' => '/', 'key' => 'home'],
    ['label' => 'Docs', 'href' => '/docs', 'key' => 'docs'],
    ['label' => 'Status', 'href' => '/status', 'key' => 'status'],
];

if ($showAdmin && (!$adminLoginRequired || $adminLoggedIn)) {
    $nav[] = ['label' => 'Admin', 'href' => '/admin', 'key' => 'admin'];
}

$topbarActions = [];
if ($showAdmin && $adminLoginRequired) {
    if ($adminLoggedIn) {
        $topbarActions[] = ['label' => 'Sign out', 'href' => '/admin/logout', 'method' => 'post'];
    } else {
        $topbarActions[] = ['label' => 'Admin login', 'href' => '/admin/login', 'key' => 'admin-login'];
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="<?= asset('assets/ui.css') ?>">
</head>
<body>
<div class="f-container" style="padding-top: 12px; padding-bottom: 48px;">
    <header class="f-topbar">
        <div class="f-topbar-brand"><?= htmlspecialchars($brand) ?></div>
        <div class="f-topbar-right">
            <nav class="f-topbar-links">
                <?php foreach ($nav as $item): ?>
                    <?php
                        $label = (string) ($item['label'] ?? 'Link');
                        $href = (string) ($item['href'] ?? '/');
                        $key = (string) ($item['key'] ?? '');
                        $isActive = $key !== '' && $key === $active;
                    ?>
                    <a class="f-topbar-link<?= $isActive ? ' f-topbar-link-active' : '' ?>" href="<?= htmlspecialchars($href) ?>">
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ($topbarActions !== []): ?>
                <div class="f-topbar-actions">
                    <?php foreach ($topbarActions as $action): ?>
                        <?php
                            $label = (string) ($action['label'] ?? 'Action');
                            $href = (string) ($action['href'] ?? '/');
                            $method = strtolower((string) ($action['method'] ?? 'get'));
                        ?>
                        <?php if ($method === 'post'): ?>
                            <form method="post" action="<?= htmlspecialchars($href) ?>">
                                <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                                <button class="f-link-button" type="submit"><?= htmlspecialchars($label) ?></button>
                            </form>
                        <?php else: ?>
                            <a class="f-topbar-link" href="<?= htmlspecialchars($href) ?>">
                                <?= htmlspecialchars($label) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <?= $content ?? '' ?>
</div>
</body>
</html>
