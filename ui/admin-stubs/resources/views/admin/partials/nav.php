<?php

declare(strict_types=1);

$brand = $brand ?? 'Admin';
$navItems = isset($navItems) && is_array($navItems) ? $navItems : [];

$allowedItems = [];
foreach ($navItems as $item) {
    if (!is_array($item)) {
        continue;
    }

    $allowed = true;
    $spec = $item['can'] ?? '';
    if (is_string($spec) && $spec !== '') {
        $parts = explode(':', $spec, 2);
        $ability = $parts[0] ?? '';
        $target = $parts[1] ?? null;
        if ($ability !== '') {
            $allowed = $target !== null ? can($ability, $target) : can($ability);
        }
    }

    if ($allowed) {
        $allowedItems[] = $item;
    }
}

?>
<div class="admin-brand">
    <a href="<?= htmlspecialchars(url('/admin'), ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars((string) $brand, ENT_QUOTES, 'UTF-8') ?>
    </a>
</div>
<nav class="admin-nav">
    <ul>
        <?php foreach ($allowedItems as $item): ?>
            <?php
            $label = (string) ($item['label'] ?? 'Item');
            $url = (string) ($item['url'] ?? '#');
            $icon = (string) ($item['icon'] ?? 'dot');
            ?>
            <li>
                <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>">
                    <span class="admin-icon"><?= htmlspecialchars(strtoupper(substr($icon, 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
                    <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
