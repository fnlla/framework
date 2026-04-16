<?php

declare(strict_types=1);

$env = (string) getenv('APP_ENV');
$env = $env !== '' ? $env : 'prod';
$debug = getenv('APP_DEBUG') === '1' ? 'on' : 'off';
$php = PHP_VERSION;
$finella = \Finella\Core\Application::VERSION;
$statusOk = isset($statusOk) ? (bool) $statusOk : false;
$readyOk = isset($readyOk) ? (bool) $readyOk : false;
$healthOk = isset($healthOk) ? (bool) $healthOk : false;

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Finella Framework</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js" defer></script>
</head>
<body>
<main class="wrap">
    <?php $brand = 'Finella Framework'; $active = 'home'; ?>
    <?php include __DIR__ . '/partials/topbar.php'; ?>
    <section class="hero">
        <div>
            <h1 class="title">Welcome to<br>Finella Framework</h1>
            <p class="subtitle">
                A clean and pragmatic PHP framework built to deliver scalable projects.
            </p>
            <div class="pill-row">
                <span class="pill">Finella v<?= htmlspecialchars($finella) ?></span>
                <span class="pill">PHP <?= htmlspecialchars($php) ?></span>
                <span class="pill">ENV <?= htmlspecialchars($env) ?></span>
                <span class="pill">DEBUG <?= htmlspecialchars($debug) ?></span>
            </div>
        </div>
        <div class="panel hero-panel highlight">
            <h2>Complete onboarding to activate your starter</h2>
            <p class="subtitle">
                Onboarding sets up core settings so you can build and ship your product without extra ceremony.
            </p>
            <h3>What onboarding does</h3>
            <ul class="list-check">
                <li><strong>Captures project metadata</strong>: name, owner, and basic defaults.</li>
                <li><strong>Sets up core routes</strong>: docs, status, and readiness checks.</li>
                <li><strong>Keeps the app minimal</strong>: add optional modules only when you need them.</li>
            </ul>
        </div>
    </section>

    <section class="panel home-actions">
        <h2>Developer actions</h2>
        <div class="action-grid">
            <a class="action-card" href="/docs">
                <strong>Docs</strong>
                <span>Local docs + packages</span>
            </a>
            <a class="action-card" href="/status">
                <strong>Status</strong>
                <span>Service health overview</span>
                <span class="badge<?= $statusOk ? ' ok' : ' fail' ?>"><?= $statusOk ? 'OK' : 'FAIL' ?></span>
            </a>
            <a class="action-card" href="/status?tab=health">
                <strong>Health</strong>
                <span>Basic liveness</span>
                <span class="badge<?= $healthOk ? ' ok' : ' fail' ?>"><?= $healthOk ? 'OK' : 'FAIL' ?></span>
            </a>
            <a class="action-card" href="/status?tab=ready">
                <strong>Readiness</strong>
                <span>Readiness checks</span>
                <span class="badge<?= $readyOk ? ' ok' : ' fail' ?>"><?= $readyOk ? 'OK' : 'FAIL' ?></span>
            </a>
            <a class="action-card" href="/status?tab=dependencies">
                <strong>Dependencies</strong>
                <span>DB, cache, queue checks</span>
            </a>
            <a class="action-card" href="/status?tab=runtime">
                <strong>Runtime</strong>
                <span>PHP, limits, extensions</span>
            </a>
            <a class="action-card" href="/status?tab=storage">
                <strong>Storage</strong>
                <span>Disk &amp; writable paths</span>
            </a>
        </div>
        <p class="hint">Tip: run <code>php bin/finella docs:generate --publish</code> after edits.</p>
    </section>

    <section class="panel panel-full">
        <h2>Starter guide</h2>
        <h3>Where to start</h3>
        <ul>
            <li><strong>Routes</strong>: <code>routes/web.php</code></li>
            <li><strong>Controller</strong>: <code>app/src/Controllers/HomeController.php</code></li>
            <li><strong>Service</strong>: <code>app/src/Services/AppStatusService.php</code></li>
            <li><strong>Status endpoint</strong>: <code>/status</code></li>
            <li><strong>Readiness endpoint</strong>: <code>/ready</code></li>
            <li><strong>Views</strong>: <code>resources/views/</code></li>
        </ul>
        <div class="spacer"></div>
        <h3>Starter features</h3>
        <ul>
            <li>Documentation hub</li>
            <li>Status, health, and readiness checks</li>
            <li>Baseline security and ops defaults</li>
            <li>Optional admin and UI kits</li>
        </ul>
        <div class="spacer"></div>
        <h3>Next steps</h3>
        <ul>
            <li>Update <code>.env</code> with your app settings</li>
            <li>Review <code>config/</code> for feature toggles</li>
            <li>Replace this view with your homepage</li>
        </ul>
        <div class="spacer"></div>
        <h3>Start a new module</h3>
        <p class="subtitle">Use modules for bounded contexts or larger features.</p>
        <pre>app/src/Modules/Billing/
  Http/Controllers/
  Application/
  Domain/
  Infrastructure/
  routes.php</pre>
        <div class="spacer"></div>
        <h3>Observability toggle</h3>
        <p class="subtitle">Enable JSON logs and request ids for production.</p>
        <pre>LOG_FORMAT=json
LOG_REQUEST_ID=1</pre>
        <div class="spacer"></div>
        <h3>What's next (checklist)</h3>
        <ul>
            <li>Set <code>APP_NAME</code>, <code>APP_VERSION</code>, <code>APP_ENV</code></li>
            <li>Decide on cache/queue driver (file vs redis)</li>
            <li>Confirm health/readiness requirements</li>
            <li>Write your first module + controller</li>
        </ul>
    </section>

    <p class="footer">
        Need to disable HTTPS redirects in dev? Set
        <code>REDIRECTS_FORCE_HTTPS=0</code> in <code>.env</code>.
    </p>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</main>
</body>
</html>


