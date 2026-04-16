<?php

$title = 'Admin login';
$active = 'admin';
$error = isset($error) ? (string) $error : null;
$notice = isset($notice) ? (string) $notice : null;
$email = isset($email) ? (string) $email : '';
$redirect = isset($redirect) ? (string) $redirect : '/admin';
$configured = isset($configured) ? (bool) $configured : true;
?>

<div class="f-stack">
    <div class="f-card">
        <h3 class="f-card-title">Admin login</h3>
        <p class="f-muted">Secure access to analytics, audit trail, and runtime settings.</p>

        <?php if ($notice): ?>
            <div class="f-alert" style="margin-top: 12px;">
                <?= htmlspecialchars($notice) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="f-alert" style="border-left-color: var(--f-color-danger); margin-top: 12px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/admin/login" style="margin-top: 16px;">
            <?= function_exists('csrf_field') ? csrf_field() : '' ?>
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

            <label class="f-label" for="admin-email">Email</label>
            <input
                id="admin-email"
                class="f-input"
                type="email"
                name="email"
                autocomplete="username"
                value="<?= htmlspecialchars($email) ?>"
                placeholder="admin@example.com"
                <?= $configured ? 'required' : '' ?>
            >

            <div style="height: 12px;"></div>

            <label class="f-label" for="admin-password">Password</label>
            <input
                id="admin-password"
                class="f-input"
                type="password"
                name="password"
                autocomplete="current-password"
                placeholder="••••••••"
                <?= $configured ? 'required' : '' ?>
            >

            <div style="height: 16px;"></div>

            <button class="f-btn f-btn-primary" type="submit">Sign in</button>
        </form>
    </div>
</div>
