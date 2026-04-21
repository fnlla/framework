<?php

declare(strict_types=1);

$root = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 3);
$partialsDir = $root . '/resources/views/partials';
$hasTopbar = is_file($partialsDir . '/topbar.php');
$hasFooter = is_file($partialsDir . '/footer.php');
$hasCss = is_file($root . '/public/css/app.css');
$hasJs = is_file($root . '/public/js/app.js');

$env = (string) getenv('APP_ENV');
$env = $env !== '' ? $env : 'prod';
$php = PHP_VERSION;
$Fnlla = \Fnlla\Core\Application::VERSION;
$appName = (string) env('APP_NAME', 'Product Application');

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($appName) ?></title>
    <?php if ($hasCss): ?>
        <link rel="stylesheet" href="/css/app.css">
    <?php endif; ?>
    <?php if ($hasJs): ?>
        <script src="/js/app.js" defer></script>
    <?php endif; ?>
</head>
<body>
<main class="wrap">
    <?php if ($hasTopbar): ?>
        <?php $brand = 'fnlla Framework'; $active = 'home'; ?>
        <?php include $partialsDir . '/topbar.php'; ?>
    <?php endif; ?>

    <section class="hero">
        <div>
            <h1 class="title"><?= htmlspecialchars($appName) ?></h1>
            <p class="subtitle">
                This is the user-facing application. Replace this view with your product UI.
            </p>
            <div class="pill-row">
                <span class="pill">fnlla v<?= htmlspecialchars($Fnlla) ?></span>
                <span class="pill">PHP <?= htmlspecialchars($php) ?></span>
                <span class="pill">ENV <?= htmlspecialchars($env) ?></span>
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Product entry point</h2>
        <p class="subtitle">
            Build your application pages here.
        </p>
    </section>

    <?php if ($hasFooter): ?>
        <?php include $partialsDir . '/footer.php'; ?>
    <?php endif; ?>
</main>
</body>
</html>