<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? htmlspecialchars((string) $title) : 'Finella UI' ?></title>
    <link rel="stylesheet" href="<?= asset('assets/ui.css') ?>">
</head>
<body>
<div class="f-container f-stack" style="padding-top: 32px; padding-bottom: 32px;">
    <?= $content ?? '' ?>
</div>
</body>
</html>
