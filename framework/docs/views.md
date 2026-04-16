# Views

## Rendering
Use the `view()` helper or `View::render()`.
```php
return view('pages/home', ['name' => 'Finella']);
```

## views_path
The framework reads `views_path` from `config/app.php`. If not set, it falls back to `APP_ROOT/resources/views` or `getcwd()/resources/views`.

## Layouts
You can pass an optional layout:
```php
$html = \Finella\View\View::render($app, 'home', ['name' => 'Finella'], 'layouts/main');
```

## Data
Data is extracted into the view scope, so keys become variables.

## Security
Always escape untrusted output. Use `htmlspecialchars()` or dedicated view helpers to avoid XSS.
