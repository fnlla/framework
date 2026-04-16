<?php
$errorBag = $errors ?? (function_exists('errors') ? errors() : null);
if (!$errorBag || !method_exists($errorBag, 'all')) {
    return;
}
$messages = $errorBag->all();
if ($messages === []) {
    return;
}
?>

<div class="f-alert">
    <strong>Fix the following:</strong>
    <ul>
        <?php foreach ($messages as $message): ?>
            <li><?= htmlspecialchars((string) $message) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
