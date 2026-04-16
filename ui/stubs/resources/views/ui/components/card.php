<?php
$title = $title ?? '';
$body = $body ?? '';
$footer = $footer ?? '';
?>

<div class="f-card">
    <?php if ($title !== ''): ?>
        <h3 class="f-card-title"><?= htmlspecialchars((string) $title) ?></h3>
    <?php endif; ?>
    <div><?= $body ?></div>
    <?php if ($footer !== ''): ?>
        <div style="margin-top: 12px;" class="f-muted"><?= $footer ?></div>
    <?php endif; ?>
</div>
