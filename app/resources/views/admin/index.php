<?php

$title = 'Admin';
$active = 'admin';
?>

<div class="f-stack">
    <div class="f-card">
        <h3 class="f-card-title">Admin dashboard</h3>
        <p class="f-muted">Manage product settings, audit trail, and analytics.</p>
    </div>

    <div class="f-grid">
        <div class="f-col-12 f-md-col-4">
            <div class="f-card">
                <h3 class="f-card-title">Analytics</h3>
                <p class="f-muted">Metrics, funnels, and events.</p>
                <a class="f-btn f-btn-outline" href="/admin/analytics">Open</a>
            </div>
        </div>
        <div class="f-col-12 f-md-col-4">
            <div class="f-card">
                <h3 class="f-card-title">Audit trail</h3>
                <p class="f-muted">Security events and admin actions.</p>
                <a class="f-btn f-btn-outline" href="/admin/audit">Open</a>
            </div>
        </div>
        <div class="f-col-12 f-md-col-4">
            <div class="f-card">
                <h3 class="f-card-title">Settings</h3>
                <p class="f-muted">Feature flags and runtime settings.</p>
                <a class="f-btn f-btn-outline" href="/admin/settings">Open</a>
            </div>
        </div>
    </div>
</div>
