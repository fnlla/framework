<?php

declare(strict_types=1);

$errors = errors();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>fnlla Form</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 40px; }
        .card { max-width: 640px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; }
        label { display: block; margin: 12px 0 6px; }
        input { width: 100%; padding: 10px 12px; border: 1px solid #cbd5f5; border-radius: 8px; }
        .error { color: #b91c1c; font-size: 14px; margin-top: 4px; }
        .errors { background: #fee2e2; border: 1px solid #fecaca; color: #7f1d1d; padding: 10px 12px; border-radius: 8px; margin-bottom: 16px; }
        button { margin-top: 16px; padding: 10px 14px; border: 0; border-radius: 8px; background: #0f172a; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Demo Form</h1>

        <?php if (count($errors) > 0): ?>
            <div class="errors">
                <strong>Fix the errors below.</strong>
            </div>
        <?php endif; ?>

        <form method="post" action="/form">
            <?php if (function_exists('csrf_field')) { echo csrf_field(); } ?>

            <label for="name">Name</label>
            <input id="name" name="name" value="<?= htmlspecialchars((string) old('name', ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($errors->has('name')): ?>
                <div class="error"><?= htmlspecialchars($errors->first('name'), ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <label for="email">Email</label>
            <input id="email" name="email" value="<?= htmlspecialchars((string) old('email', ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($errors->has('email')): ?>
                <div class="error"><?= htmlspecialchars($errors->first('email'), ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
