<?php

declare(strict_types=1);

$snapshot = isset($snapshot) && is_array($snapshot) ? $snapshot : [];
$status = (string) ($snapshot['status'] ?? 'unknown');
$service = (string) ($snapshot['service'] ?? '');
$env = (string) ($snapshot['env'] ?? '');
$version = (string) ($snapshot['version'] ?? '');
$time = (string) ($snapshot['time'] ?? '');
$timeHuman = $time;
if ($time !== '') {
    $parsed = strtotime($time);
    if ($parsed !== false) {
        $timeHuman = gmdate('M j, Y H:i', $parsed) . ' UTC';
    }
}
$active = 'health';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Health</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js" defer></script>
</head>
<body>
<main class="wrap">
    <?php $brand = 'Finella Framework'; ?>
    <?php include __DIR__ . '/../partials/topbar.php'; ?>

    <section class="hero">
        <div>
            <h1 class="title">Health Check</h1>
            <p class="subtitle">Minimal liveness indicator for the current deployment.</p>
            <div class="pill-row">
                <span class="pill">Service <?= htmlspecialchars($service) ?></span>
                <span class="pill">Env <?= htmlspecialchars($env) ?></span>
                <span class="pill">Version <?= htmlspecialchars($version) ?></span>
            </div>
        </div>
        <div class="panel">
            <h2>State</h2>
            <div class="status-row">
                <span class="status-label">Status</span>
                <span class="status-badge <?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></span>
            </div>
            <p class="subtitle">Time <?= htmlspecialchars($timeHuman) ?></p>
        </div>
    </section>

    <section class="panel">
        <h2>Usage</h2>
        <ul>
            <li>Use this endpoint for simple liveness checks.</li>
            <li>Prefer <code>/ready</code> for dependency checks.</li>
            <li>Add <code>?format=json</code> for JSON output.</li>
        </ul>
    </section>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</main>
</body>
</html>
