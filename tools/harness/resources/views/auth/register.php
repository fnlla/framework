<?php

$errors = $errors ?? [];
$old = $old ?? [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 40px; background: #f8fafc; color: #0f172a; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; max-width: 520px; }
        label { display: block; margin: 12px 0 6px; }
        input { width: 100%; padding: 10px; border: 1px solid #cbd5f5; border-radius: 6px; }
        .btn { margin-top: 16px; padding: 10px 16px; border-radius: 8px; background: #0f766e; color: #fff; border: none; }
        .error { color: #b91c1c; margin-top: 6px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Create account</h1>
        <form method="post" action="/auth/register">
            <?= csrf_field() ?>
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="<?= htmlspecialchars((string) ($old['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['email'][0], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
            <?php if (!empty($errors['password'])): ?>
                <div class="error"><?= htmlspecialchars((string) $errors['password'][0], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>

            <button class="btn" type="submit">Register</button>
        </form>
        <p><a href="/auth/login">Already have an account?</a></p>
    </div>
</body>
</html>
