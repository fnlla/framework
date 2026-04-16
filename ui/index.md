# Finella UI

Finella UI is a no-build CSS framework and Elements library for shipping product UIs fast.
It includes a grid system, utilities, form styles, and ready-to-use layouts.
For the static HTML version of these docs, open `ui/documentation/`.

**Quick links**
- [Browse the Elements library](../elements/)
- [Open Finella UI site](../elements/)
- [View UI changelog](../CHANGELOG.md)

## Install
```bash
composer require finella/ui
```

## Publish assets and templates
```bash
php bin/finella ui:publish --app=.
```
This copies:
- `public/assets/ui.css`
- `resources/views/ui/*`

## Publish Elements (optional)
```bash
php bin/finella ui:elements:publish --app=.
```
This copies the gallery into `public/ui-elements/`.
Elements are production-ready building blocks - copy the parts you need into your app and customise locally.

## Auth providers (Sign in / Sign up)
The Sign-in Element supports provider buttons via a lightweight, framework-agnostic hook.

### Frontend hook
Each provider button uses `data-auth-provider`:
```html
<button data-auth-provider="google">Continue with Google</button>
<button data-auth-provider="apple">Continue with Apple</button>
<button data-auth-provider="microsoft">Continue with Microsoft</button>
<button data-auth-provider="github">Continue with GitHub</button>
```

The Element JS redirects to:
```
/auth/{provider}
```

You can change the base path by setting:
```html
<html data-auth-base="/auth">
```

Limit providers with:
```html
<html data-auth-providers="google,apple">
```
Only the listed providers will remain visible.

Hide icon-only rows with:
```html
<html data-auth-icons="false">
```

### Backend wiring (Finella Auth)
If you use Finella Auth, register the built-in routes once:
```php
use Finella\Auth\AuthRoutes;

AuthRoutes::register($router, [
    'prefix' => '/auth',
    'middleware' => ['web'],
]);
```

Then configure provider credentials in your app as required by your OAuth/OIDC setup.
The UI buttons simply redirect to `/auth/{provider}` so the backend can start the OAuth flow.

### Remove providers (email-only)
To keep email-only sign-in/sign-up, remove the provider button group:
```html
<!-- Remove this block for email-only auth -->
<div class="auth-provider-grid">...</div>
```

Optional icon-only row:
```html
<div class="auth-provider-row auth-provider-row--icons">...</div>
```

## Carousel controls
The Sign-in and Sign-up panels include an optional carousel.

### Skip behaviour
`Skip` stops the carousel and focuses the primary input (email or name).
You can control the focus target per panel:
```html
<div class="auth-panel" data-auth-carousel="signin" data-auth-skip-focus="signin-email">
```

### Static mode (no animation)
If you want a static panel, set:
```html
<div class="auth-panel" data-auth-carousel="signin" data-auth-static="true">
```
This disables auto-rotation and hides the dots/controls.

## Theming
Override CSS variables in `:root` to adjust colours, radius, and typography.
Load your overrides after `ui.css` for safe customisation.
