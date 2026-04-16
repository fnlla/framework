<?php

declare(strict_types=1);

$brand = isset($brand) ? (string) $brand : 'Finella Framework';
$active = isset($active) ? (string) $active : '';
$hideTopActions = isset($hideTopActions) ? (bool) $hideTopActions : false;
$docsActive = in_array($active, ['docs'], true);
$opsActive = in_array($active, ['status', 'health', 'ready'], true);

?>
<header class="topbar">
    <div class="brand"><?= htmlspecialchars($brand) ?></div>
    <?php if (!$hideTopActions): ?>
        <div class="top-actions">
            <a class="link<?= $active === 'home' ? ' is-active' : '' ?>" href="/">Home</a>
            <a class="link<?= $docsActive ? ' is-active' : '' ?>" href="/docs">Docs</a>
            <a class="link<?= $opsActive ? ' is-active' : '' ?>" href="/status">Status / Health</a>
            <a class="link<?= $active === 'cli' ? ' is-active' : '' ?>" href="/docs/developer-experience#cli">CLI</a>
            <button class="toggle" type="button" id="themeToggle" aria-pressed="false">Dark mode</button>
        </div>
    <?php endif; ?>
</header>
