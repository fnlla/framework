<?php

$errors = $errors ?? [];
$old = $old ?? [];
$status = $status ?? null;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 40px; background: #f8fafc; color: #0f172a; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; max-width: 520px; }
        label { display: block; margin: 12px 0 6px; }
        input { width: 100%; padding: 10px; border: 1px solid #cbd5f5; border-radius: 6px; }
        .btn { margin-top: 16px; padding: 10px 16px; border-radius: 8px; background: #0f766e; color: #fff; border: none; }
        .error { color: #b91c1c; margin-top: 6px; }
        .status { color: #0f766e; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Forgot password</h1>
        <?php if (is_string($status)): ?>
            <div class="status"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" action="/auth/password/email">
            <?= csrf_field() ?>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['email'][0], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <button class="btn" type="submit">Send reset link</button>
        </form>
        <p><a href="/auth/login">Back to login</a></p>
    </div>
</body>
</html>
