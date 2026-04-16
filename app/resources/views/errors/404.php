<?php

declare(strict_types=1);

$title = isset($title) ? (string) $title : 'Not Found';
$message = isset($message) ? (string) $message : 'The page you are looking for does not exist.';
$status = isset($status) ? (int) $status : 404;
$brand = 'Finella Framework';
$active = '';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js" defer></script>
</head>
<body>
<main class="wrap">
    <?php include __DIR__ . '/../partials/topbar.php'; ?>

    <section class="hero">
        <div>
            <h1 class="title"><?= htmlspecialchars((string) $status) ?> &middot; <?= htmlspecialchars($title) ?></h1>
            <p class="subtitle"><?= htmlspecialchars($message) ?></p>
            <div class="pill-row">
                <span class="pill">Return to <a class="link" href="/">Home</a></span>
                <span class="pill">Browse <a class="link" href="/docs">Docs</a></span>
            </div>
        </div>
        <div class="panel panel-full">
            <h2>What you can do</h2>
            <ul>
                <li>Check the URL for typos</li>
                <li>Use the docs menu to navigate</li>
                <li>Go back to the homepage</li>
            </ul>
        </div>
    </section>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</main>
</body>
</html>

