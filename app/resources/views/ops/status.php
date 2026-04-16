<?php

declare(strict_types=1);

$status = isset($status) && is_array($status) ? $status : [];
$health = isset($health) && is_array($health) ? $health : [];
$readiness = isset($readiness) && is_array($readiness) ? $readiness : [];

$statusState = (string) ($status['status'] ?? 'unknown');
$service = (string) ($status['service'] ?? '');
$env = (string) ($status['env'] ?? '');
$version = (string) ($status['version'] ?? '');
$php = (string) ($status['php'] ?? PHP_VERSION);
$finella = (string) ($status['finella'] ?? '');
$host = (string) ($status['host'] ?? '');
$timezone = (string) ($status['timezone'] ?? '');
$sapi = (string) ($status['php_sapi'] ?? PHP_SAPI);
$os = (string) ($status['os'] ?? '');
$memoryUsage = (int) ($status['memory_usage'] ?? 0);
$memoryPeak = (int) ($status['memory_peak'] ?? 0);
$memoryLimit = (string) ($status['memory_limit'] ?? '');
$uploadMax = (string) ($status['upload_max_filesize'] ?? '');
$postMax = (string) ($status['post_max_size'] ?? '');
$time = (string) ($status['time'] ?? '');
$timeHuman = $time;
if ($time !== '') {
    $parsed = strtotime($time);
    if ($parsed !== false) {
        $timeHuman = gmdate('M j, Y H:i', $parsed) . ' UTC';
    }
}

$healthState = (string) ($health['status'] ?? 'unknown');
$healthTime = (string) ($health['time'] ?? $time);
$healthTimeHuman = $healthTime;
if ($healthTime !== '') {
    $parsedHealth = strtotime($healthTime);
    if ($parsedHealth !== false) {
        $healthTimeHuman = gmdate('M j, Y H:i', $parsedHealth) . ' UTC';
    }
}

$readyState = (string) ($readiness['status'] ?? 'unknown');
$readyTime = (string) ($readiness['time'] ?? '');
$readyTimeHuman = $readyTime;
if ($readyTime !== '') {
    $parsedReady = strtotime($readyTime);
    if ($parsedReady !== false) {
        $readyTimeHuman = gmdate('M j, Y H:i', $parsedReady) . ' UTC';
    }
}
$checks = isset($readiness['checks']) && is_array($readiness['checks']) ? $readiness['checks'] : [];

$active = 'status';

$label = static function (string $name): string {
    return ucfirst(str_replace('_', ' ', $name));
};

$summary = ['ok' => 0, 'fail' => 0, 'skipped' => 0, 'unknown' => 0];
foreach ($checks as $check) {
    $state = (string) ($check['status'] ?? 'unknown');
    if (!array_key_exists($state, $summary)) {
        $state = 'unknown';
    }
    $summary[$state]++;
}

$formatBytes = static function (int $bytes): string {
    if ($bytes <= 0) {
        return '0 B';
    }
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $pow = (int) floor(log($bytes, 1024));
    $pow = min($pow, count($units) - 1);
    $value = $bytes / (1024 ** $pow);
    return number_format($value, 2) . ' ' . $units[$pow];
};

$dependencies = [];
foreach (['database' => 'Database', 'cache' => 'Cache', 'queue' => 'Queue'] as $key => $labelText) {
    $check = $checks[$key] ?? null;
    if (is_array($check)) {
        $dependencies[] = [
            'name' => $labelText,
            'status' => (string) ($check['status'] ?? 'unknown'),
            'detail' => (string) ($check['detail'] ?? ''),
        ];
    }
}

$rootPath = defined('APP_ROOT') ? APP_ROOT : getcwd();
$storagePath = rtrim((string) $rootPath, '/\\') . '/storage';
$logsPath = $storagePath . '/logs';
$storageWritable = is_dir($storagePath) ? is_writable($storagePath) : false;
$logsWritable = is_dir($logsPath) ? is_writable($logsPath) : false;
$diskTotal = @disk_total_space((string) $rootPath);
$diskFree = @disk_free_space((string) $rootPath);
$diskTotal = $diskTotal === false ? 0 : (int) $diskTotal;
$diskFree = $diskFree === false ? 0 : (int) $diskFree;

$extensions = get_loaded_extensions();
sort($extensions);
$extensionCount = count($extensions);
$keyExtensions = [
    'zip' => extension_loaded('zip'),
    'imap' => extension_loaded('imap'),
    'redis' => extension_loaded('redis'),
];

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status / Health</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js" defer></script>
</head>
<body>
<main class="wrap">
    <?php $brand = 'Finella Framework'; ?>
    <?php include __DIR__ . '/../partials/topbar.php'; ?>

    <section class="hero">
        <div>
            <h1 class="title">Status / Health</h1>
            <p class="subtitle">All runtime signals in one place.</p>
            <div class="pill-row pills-inline">
                <span class="pill">Service <?= htmlspecialchars($service) ?></span>
                <span class="pill">Env <?= htmlspecialchars($env) ?></span>
                <span class="pill">Version <?= htmlspecialchars($version) ?></span>
                <span class="pill">Time <?= htmlspecialchars($timeHuman) ?></span>
            </div>
        </div>
        <div class="panel">
            <h2>Summary</h2>
            <div class="status-row">
                <span class="status-label">Status</span>
                <span class="status-badge <?= htmlspecialchars($statusState) ?>"><?= htmlspecialchars($statusState) ?></span>
            </div>
            <div class="status-row">
                <span class="status-label">Health</span>
                <span class="status-badge <?= htmlspecialchars($healthState) ?>"><?= htmlspecialchars($healthState) ?></span>
            </div>
            <div class="status-row">
                <span class="status-label">Ready</span>
                <span class="status-badge <?= htmlspecialchars($readyState) ?>"><?= htmlspecialchars($readyState) ?></span>
            </div>
            <?php if ($checks !== []): ?>
                <div class="status-row">
                    <span class="status-label">Checks</span>
                    <span class="status-meta"><?= htmlspecialchars((string) $summary['ok']) ?> ok · <?= htmlspecialchars((string) $summary['fail']) ?> fail · <?= htmlspecialchars((string) $summary['skipped']) ?> skipped</span>
                </div>
            <?php endif; ?>
            <div class="status-actions">
                <a class="btn secondary" href="/status?format=json">View JSON</a>
                <p class="subtitle">Raw JSON output for monitoring or API checks.</p>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="tabs" data-tabs>
            <button class="tab is-active" type="button" data-tab="status">Status</button>
            <button class="tab" type="button" data-tab="health">Health</button>
            <button class="tab" type="button" data-tab="ready">Readiness</button>
            <button class="tab" type="button" data-tab="dependencies">Dependencies</button>
            <button class="tab" type="button" data-tab="runtime">Runtime</button>
            <button class="tab" type="button" data-tab="storage">Storage</button>
        </div>

        <div class="tab-panels">
            <div class="tab-panel is-active" data-panel="status">
                <h2>Status details</h2>
                <ul class="status-list">
                    <li class="status-item">
                        <span>Service name</span>
                        <span class="status-meta"><?= htmlspecialchars($service) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Environment</span>
                        <span class="status-meta"><?= htmlspecialchars($env) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Version</span>
                        <span class="status-meta"><?= htmlspecialchars($version) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Finella</span>
                        <span class="status-meta"><?= htmlspecialchars($finella) ?></span>
                    </li>
                    <li class="status-item">
                        <span>PHP</span>
                        <span class="status-meta"><?= htmlspecialchars($php) ?></span>
                    </li>
                    <li class="status-item">
                        <span>PHP SAPI</span>
                        <span class="status-meta"><?= htmlspecialchars($sapi) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Host</span>
                        <span class="status-meta"><?= htmlspecialchars($host) ?></span>
                    </li>
                    <li class="status-item">
                        <span>OS</span>
                        <span class="status-meta"><?= htmlspecialchars($os) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Timezone</span>
                        <span class="status-meta"><?= htmlspecialchars($timezone) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Timestamp</span>
                        <span class="status-meta"><?= htmlspecialchars($timeHuman) ?></span>
                    </li>
                </ul>
                <h3>Memory</h3>
                <ul class="status-list">
                    <li class="status-item">
                        <span>Current usage</span>
                        <span class="status-meta"><?= htmlspecialchars($formatBytes($memoryUsage)) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Peak usage</span>
                        <span class="status-meta"><?= htmlspecialchars($formatBytes($memoryPeak)) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Memory limit</span>
                        <span class="status-meta"><?= htmlspecialchars($memoryLimit) ?></span>
                    </li>
                </ul>
                <h3>Request limits</h3>
                <ul class="status-list">
                    <li class="status-item">
                        <span>Upload max filesize</span>
                        <span class="status-meta"><?= htmlspecialchars($uploadMax) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Post max size</span>
                        <span class="status-meta"><?= htmlspecialchars($postMax) ?></span>
                    </li>
                </ul>
            </div>

            <div class="tab-panel" data-panel="health">
                <h2>Health details</h2>
                <ul class="status-list">
                    <li class="status-item">
                        <span>Health status</span>
                        <span class="status-meta"><?= htmlspecialchars($healthState) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Timestamp</span>
                        <span class="status-meta"><?= htmlspecialchars($healthTimeHuman) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Endpoint</span>
                        <span class="status-meta">/health</span>
                    </li>
                </ul>
            </div>

            <div class="tab-panel" data-panel="ready">
                <h2>Readiness checks</h2>
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
                    <?php if ($readyTime !== ''): ?>
                        <p class="subtitle status-subtitle">Updated at <?= htmlspecialchars($readyTimeHuman) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="tab-panel" data-panel="dependencies">
                <h2>Dependencies</h2>
                <?php if ($dependencies === []): ?>
                    <p class="subtitle">No dependency checks available. Configure readiness checks to populate this view.</p>
                <?php else: ?>
                    <ul class="status-list">
                        <?php foreach ($dependencies as $dep): ?>
                            <li class="status-item">
                                <span><?= htmlspecialchars($dep['name']) ?></span>
                                <div class="status-meta"><?= htmlspecialchars($dep['detail']) ?></div>
                                <span class="status-badge <?= htmlspecialchars($dep['status']) ?>"><?= htmlspecialchars($dep['status']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="tab-panel" data-panel="runtime">
                <h2>Runtime</h2>
                <ul class="status-list">
                    <li class="status-item">
                        <span>PHP</span>
                        <span class="status-meta"><?= htmlspecialchars($php) ?></span>
                    </li>
                    <li class="status-item">
                        <span>PHP SAPI</span>
                        <span class="status-meta"><?= htmlspecialchars($sapi) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Timezone</span>
                        <span class="status-meta"><?= htmlspecialchars($timezone) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Memory limit</span>
                        <span class="status-meta"><?= htmlspecialchars($memoryLimit) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Upload max filesize</span>
                        <span class="status-meta"><?= htmlspecialchars($uploadMax) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Post max size</span>
                        <span class="status-meta"><?= htmlspecialchars($postMax) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Extensions loaded</span>
                        <span class="status-meta"><?= htmlspecialchars((string) $extensionCount) ?></span>
                    </li>
                </ul>
                <div class="spacer"></div>
                <h3>Key extensions</h3>
                <ul class="status-list">
                    <?php foreach ($keyExtensions as $ext => $loaded): ?>
                        <li class="status-item">
                            <span><?= htmlspecialchars($ext) ?></span>
                            <span class="status-meta"><?= $loaded ? 'loaded' : 'missing' ?></span>
                            <span class="status-badge <?= $loaded ? 'ok' : 'fail' ?>"><?= $loaded ? 'ok' : 'fail' ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="tab-panel" data-panel="storage">
                <h2>Storage</h2>
                <ul class="status-list">
                    <li class="status-item">
                        <span>App root</span>
                        <span class="status-meta"><?= htmlspecialchars((string) $rootPath) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Disk total</span>
                        <span class="status-meta"><?= htmlspecialchars($formatBytes($diskTotal)) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Disk free</span>
                        <span class="status-meta"><?= htmlspecialchars($formatBytes($diskFree)) ?></span>
                    </li>
                    <li class="status-item">
                        <span>Storage writable</span>
                        <span class="status-meta"><?= $storageWritable ? 'yes' : 'no' ?></span>
                        <span class="status-badge <?= $storageWritable ? 'ok' : 'fail' ?>"><?= $storageWritable ? 'ok' : 'fail' ?></span>
                    </li>
                    <li class="status-item">
                        <span>Logs writable</span>
                        <span class="status-meta"><?= $logsWritable ? 'yes' : 'no' ?></span>
                        <span class="status-badge <?= $logsWritable ? 'ok' : 'fail' ?>"><?= $logsWritable ? 'ok' : 'fail' ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</main>
</body>
</html>
