<?php
$name = $name ?? '';
$label = $label ?? ($name !== '' ? ucfirst($name) : 'Field');
$type = $type ?? 'text';
$value = $value ?? (function_exists('old') ? old($name) : '');
$errorBag = $errors ?? (function_exists('errors') ? errors() : null);
$error = $errorBag && method_exists($errorBag, 'first') ? $errorBag->first($name) : '';
$inputId = $id ?? $name;
?>

<div class="f-form-group">
    <label class="f-label" for="<?= htmlspecialchars((string) $inputId) ?>"><?= htmlspecialchars((string) $label) ?></label>
    <input
        class="f-input <?= $error !== '' ? 'f-input-error' : '' ?>"
        type="<?= htmlspecialchars((string) $type) ?>"
        name="<?= htmlspecialchars((string) $name) ?>"
        id="<?= htmlspecialchars((string) $inputId) ?>"
        value="<?= htmlspecialchars((string) $value) ?>"
    >
    <?php if ($error !== ''): ?>
        <div class="f-error"><?= htmlspecialchars((string) $error) ?></div>
    <?php endif; ?>
</div>
