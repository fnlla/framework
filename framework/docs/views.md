**VIEWS**

**RENDERING**
Use the `view()` helper or `View::render()`.
```php
return view('pages/home', ['name' => 'Finella']);
```

**VIEWS_PATH**
The framework reads `views_path` from `config/app.php`. If not set, it falls back to `APP_ROOT/resources/views` or `getcwd()/resources/views`.

**LAYOUTS**
You can pass an optional layout:
```php
$html = \Finella\View\View::render($app, 'home', ['name' => 'Finella'], 'layouts/main');
```

**DATA**
Data is extracted into the view scope, so keys become variables.

**SECURITY**
Always escape untrusted output. Use `htmlspecialchars()` or dedicated view helpers to avoid XSS.
