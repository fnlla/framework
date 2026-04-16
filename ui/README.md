**FINELLA/UI**

Finella UI is a lightweight, no-build CSS framework with a grid, utilities, and basic components.
No-build means there is no frontend toolchain required; the CSS ships prebuilt and you include it directly.
It ships with ready-to-use view stubs and a publish command so you can build full UIs without external tooling.

**INSTALLATION**
```bash
composer require finella/ui
```

**PUBLISH ASSETS AND TEMPLATES**
Register the command in `config/console/console.php`, then run:
```bash
php bin/finella ui:publish --app=.
```

This copies:
**-** `public/assets/ui.css`
**-** `resources/views/ui/*` (components + form helpers)

**USAGE**
Include the CSS in your layout:
```php
<link rel="stylesheet" href="<?= asset('assets/ui.css') ?>">
```

Publish admin presets:
```bash
php bin/finella ui:admin:publish --app=.
```
This copies:
**-** `config/admin.php`
**-** `resources/views/admin/*`
**-** `public/admin/admin.css`

Use the grid and components:
```php
<div class="f-container f-stack">
  <div class="f-grid">
    <div class="f-col-12 f-md-col-6">Left</div>
    <div class="f-col-12 f-md-col-6">Right</div>
  </div>
  <button class="f-btn f-btn-primary">Save</button>
</div>
```

**FORM HELPERS**
Use the provided form stubs:
```php
<?php $name = 'title'; $label = 'Title'; $type = 'text'; ?>
<?php include view_path('ui/forms/field'); ?>
```

**NOTES**
**-** Finella UI is intentionally small and can be themed via CSS variables in `:root`.
**-** You can safely extend the CSS file in your app to match your brand.
**-** Elements live in `ui/elements` inside the monorepo.
**-** Documentation lives in `ui/index.md` (markdown) and `ui/documentation` (static HTML).

**VERSIONING**
Finella UI ships on its own SemVer line. See `CHANGELOG.md` for UI-specific
release notes and breaking changes.

**DOCS**
Generate the static HTML docs:
```bash
php scripts/docs/build-ui-docs.php
```
