<?php

declare(strict_types=1);

$title = isset($title) ? (string) $title : 'Server Error';
$message = isset($message) ? (string) $message : 'Something went wrong on our side.';
$status = isset($status) ? (int) $status : 500;
$errorId = isset($error_id) ? (string) $error_id : '';
$exception = isset($exception) ? (string) $exception : '';
$file = isset($file) ? (string) $file : '';
$line = isset($line) ? (string) $line : '';
$trace = isset($trace) && is_array($trace) ? $trace : [];
$debug = env('APP_DEBUG', false);
$brand = 'Finella Framework';
$active = '';
$excerpt = '';

if ($debug && $file !== '' && is_file($file) && $line > 0) {
    $lines = @file($file, FILE_IGNORE_NEW_LINES);
    if (is_array($lines)) {
        $start = max(1, $line - 6);
        $end = min(count($lines), $line + 6);
        $buffer = [];
        for ($i = $start; $i <= $end; $i++) {
            $prefix = $i === $line ? '>> ' : '   ';
            $buffer[] = $prefix . str_pad((string) $i, 4, ' ', STR_PAD_LEFT) . ' | ' . $lines[$i - 1];
        }
        $excerpt = implode("\n", $buffer);
    }
}

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
            <?php if ($errorId !== ''): ?>
                <div class="pill-row">
                    <span class="pill">Error ID <?= htmlspecialchars($errorId) ?></span>
                </div>
            <?php endif; ?>
        </div>
        <div class="panel panel-full">
            <h2>What you can do</h2>
            <ul>
                <li>Retry the request in a moment</li>
                <li>Check logs for the error id above</li>
                <li>Contact the team if the issue persists</li>
            </ul>
            <?php if ($debug && $exception !== ''): ?>
                <div class="spacer"></div>
                <h3>Debug details</h3>
                <p class="subtitle"><?= htmlspecialchars($exception) ?></p>
                <p class="subtitle"><?= htmlspecialchars($file) ?>:<?= htmlspecialchars((string) $line) ?></p>
                <?php if ($excerpt !== ''): ?>
                    <pre style="background:#0f172a;color:#e2e8f0;padding:16px;border-radius:12px;overflow:auto;font-size:12px;line-height:1.5;"><?= htmlspecialchars($excerpt) ?></pre>
                <?php endif; ?>
                <?php if ($trace !== []): ?>
                    <pre><?= htmlspecialchars(implode("\n", $trace)) ?></pre>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</main>
</body>
</html>
