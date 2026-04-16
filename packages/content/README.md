**FINELLA/CONTENT**

Content repository helpers for Finella. Loads JSON or Markdown files from a
content directory with optional front matter.

**INSTALLATION**
```bash
composer require finella/content
```
The package registers `ContentServiceProvider` via auto-discovery.

**CONFIGURATION**
```php
return [
    'path' => 'content',
];
```

**USAGE**
```php
use Finella\Content\ContentRepository;

$repo = $app->make(ContentRepository::class);
$item = $repo->get('services/development');
if ($item) {
    $title = $item->get('title');
    $body = $item->body();
}
```

Markdown front matter can be JSON or `key: value` pairs:
```md
---
{"title": "AI Services"}
---
Your content here.
```

**TESTING**
```bash
php tests/smoke.php
```
