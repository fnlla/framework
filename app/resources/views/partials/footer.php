<?php

declare(strict_types=1);

$brand = isset($brand) ? (string) $brand : 'Finella Framework';
$year = gmdate('Y');

?>
<footer class="site-footer">
    <div>
        <strong><?= htmlspecialchars($brand) ?></strong>
        <span class="footer-note">by TechAyo.</span>
    </div>
    <div>&copy; <?= htmlspecialchars($year) ?> TechAyo.co.uk</div>
</footer>
