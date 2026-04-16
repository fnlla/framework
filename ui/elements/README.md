**FINELLA UI ELEMENTS**

Each element ships with its own `style.css` (and optional `script.js`) so layout-specific tweaks stay local.
Prefer editing the per-element files and keep `assets/elements.css` for shared, global utilities only.

To re-sync shared assets after updates:
`php scripts/dev/sync-element-assets.php`
