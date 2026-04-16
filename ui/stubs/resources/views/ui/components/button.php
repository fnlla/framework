<?php
$label = $label ?? 'Button';
$variant = $variant ?? 'primary';
$href = $href ?? null;
$type = $type ?? 'button';
$classes = 'f-btn ' . ($variant === 'outline' ? 'f-btn-outline' : 'f-btn-primary');
?>

<?php if (is_string($href) && $href !== ''): ?>
    <a class="<?= $classes ?>" href="<?= htmlspecialchars($href) ?>"><?= htmlspecialchars((string) $label) ?></a>
<?php else: ?>
    <button class="<?= $classes ?>" type="<?= htmlspecialchars((string) $type) ?>"><?= htmlspecialchars((string) $label) ?></button>
<?php endif; ?>
