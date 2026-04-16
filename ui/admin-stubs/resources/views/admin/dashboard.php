<?php

declare(strict_types=1);

$title = $title ?? 'Dashboard';

?>
<section class="admin-grid">
    <div class="admin-card">
        <h2>Health</h2>
        <p>System is ready. Review queues, logs, and scheduled tasks.</p>
    </div>
    <div class="admin-card">
        <h2>Pending tasks</h2>
        <p>Queue depth: <strong>0</strong></p>
        <p>Scheduled jobs: <strong>0</strong></p>
    </div>
    <div class="admin-card">
        <h2>Recent activity</h2>
        <p>No recent audit events.</p>
    </div>
</section>
