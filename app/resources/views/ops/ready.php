<?php

declare(strict_types=1);

$snapshot = isset($snapshot) && is_array($snapshot) ? $snapshot : [];
$status = (string) ($snapshot['status'] ?? 'unknown');
$time = (string) ($snapshot['time'] ?? '');
$timeHuman = $time;
if ($time !== '') {
    $parsed = strtotime($time);
    if ($parsed !== false) {
        $timeHuman = gmdate('M j, Y H:i', $parsed) . ' UTC';
    }
}
$checks = isset($snapshot['checks']) && is_array($snapshot['checks']) ? $snapshot['checks'] : [];
$active = 'ready';

$label = static function (string $name): string {
    return ucfirst(str_replace('_', ' ', $name));
};

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Readiness</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js" defer></script>
</head>
<body>
<main class="wrap">
    <?php $brand = 'Finella Framework'; ?>
    <?php include __DIR__ . '/../partials/topbar.php'; ?>

    <section class="hero">
        <div>
            <h1 class="title">Readiness</h1>
            <p class="subtitle">Dependency checks for databases, cache, and queues.</p>
            <div class="pill-row">
                <span class="pill">Status <?= htmlspecialchars($status) ?></span>
                <span class="pill">Time <?= htmlspecialchars($timeHuman) ?></span>
            </div>
        </div>
        <div class="panel">
            <h2>State</h2>
            <div class="status-row">
                <span class="status-label">Overall</span>
                <span class="status-badge <?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></span>
            </div>
            <p class="subtitle">Use <code>?format=json</code> for raw output.</p>
        </div>
    </section>

    <section class="panel">
        <h2>Checks</h2>
        <?php if ($checks === []): ?>
            <p class="subtitle">No readiness checks configured.</p>
        <?php else: ?>
            <ul class="status-list">
                <?php foreach ($checks as $name => $check): ?>
                    <?php $checkStatus = (string) ($check['status'] ?? 'unknown'); ?>
                    <li class="status-item">
                        <span><?= htmlspecialchars($label((string) $name)) ?></span>
                        <div class="status-meta">
                            <?= htmlspecialchars((string) ($check['detail'] ?? '')) ?>
                        </div>
                        <span class="status-badge <?= htmlspecialchars($checkStatus) ?>"><?= htmlspecialchars($checkStatus) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</main>
</body>
</html>
