<?php
/**
 * Build static HTML docs for Finella UI.
 *
 * Source: /ui/index.md
 * Output: /ui/documentation/index.html
 */

declare(strict_types=1);

final class UiDocsBuilder
{
    private string $root;
    private string $sourcePath;
    private string $targetDir;
    private string $assetsDir;

    public function __construct(string $root)
    {
        $this->root = rtrim($root, '/\\');
        $this->sourcePath = $this->root . '/ui/index.md';
        $this->targetDir = $this->root . '/ui/documentation';
        $this->assetsDir = $this->targetDir . '/assets';
    }

    public function build(): void
    {
        if (!is_file($this->sourcePath)) {
            fwrite(STDERR, "Missing UI docs markdown at {$this->sourcePath}\n");
            exit(1);
        }

        $this->ensureDir($this->targetDir);
        $this->ensureDir($this->assetsDir);
        $this->syncDocsCss();

        $markdown = file_get_contents($this->sourcePath);
        if ($markdown === false) {
            fwrite(STDERR, "Unable to read {$this->sourcePath}\n");
            exit(1);
        }

        $markdown = $this->stripBom($markdown);
        $title = $this->extractTitle($markdown) ?: 'Finella UI';
        $body = $this->markdownToHtml($markdown);

        $html = $this->renderPage($title, $body);
        $outPath = $this->targetDir . '/index.html';
        file_put_contents($outPath, $html);
        fwrite(STDOUT, "Wrote {$outPath}\n");
    }

    private function renderPage(string $title, string $body): string
    {
        $safeTitle = $this->escapeAttr($title);
        $heading = $this->escapeHtml($title);

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$safeTitle} Documentation</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../elements/assets/ui.css">
  <link rel="stylesheet" href="../elements/assets/elements.css">
  <link rel="stylesheet" href="assets/documentation.css">
</head>
<body class="fx-body fx-docs-body">
  <header class="fx-topbar">
    <div class="f-container fx-topbar-inner">
      <div class="fx-brand">
        <div class="fx-brand-mark">Finella UI</div>
        <span class="fx-brand-pill">Documentation</span>
      </div>
      <nav class="fx-nav">
        <a class="fx-nav-link" href="../elements/">Home</a>
        <a class="fx-nav-link" href="../elements/#elements">Elements</a>
        <a class="fx-nav-link is-active" href="index.html">Documentation</a>
      </nav>
      <div class="fx-topbar-actions">
        <a class="f-btn f-btn-outline" href="../elements/#elements">Browse elements</a>
        <a class="f-btn f-btn-primary" href="../elements/">Finella UI</a>
      </div>
    </div>
  </header>

  <main class="fx-docs-layout">
    <aside class="fx-docs-sidebar">
      <div class="fx-docs-sidebar-title">Documentation</div>
      <ul class="fx-docs-nav"><li><a class="fx-docs-link is-active" href="index.html">{$heading}</a></li></ul>
      <div class="fx-docs-sidebar-note">
        Docs source lives in <code>ui/index.md</code>.
      </div>
    </aside>
    <article class="fx-docs-content">
      <div class="fx-docs-eyebrow">Finella UI</div>
      <h1>{$heading}</h1>
      <div class="fx-docs-markdown">
        {$body}
      </div>
    </article>
  </main>

  <footer class="fx-docs-footer">
    <div class="f-container">
      <span>© <span data-current-year></span> Finella</span>
      <span>Docs generated from markdown in <code>ui/index.md</code>.</span>
    </div>
  </footer>
  <script src="../elements/assets/elements.js"></script>
</body>
</html>
HTML;
    }

    private function markdownToHtml(string $markdown): string
    {
        $lines = preg_split('/\\r\\n|\\n|\\r/', $markdown);
        $html = '';
        $count = count($lines);
        $i = 0;

        while ($i < $count) {
            $line = $lines[$i];

            if (trim($line) === '') {
                $i++;
                continue;
            }

            if (preg_match('/^```\\s*([a-z0-9_-]+)?\\s*$/i', $line, $matches)) {
                $lang = $matches[1] ?? '';
                $codeLines = [];
                $i++;
                while ($i < $count && !preg_match('/^```\\s*$/', $lines[$i])) {
                    $codeLines[] = $lines[$i];
                    $i++;
                }
                $i++;
                $code = htmlspecialchars(implode("\n", $codeLines), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $class = $lang !== '' ? ' class="language-' . $this->escapeAttr($lang) . '"' : '';
                $html .= "<pre><code{$class}>{$code}</code></pre>";
                continue;
            }

            $nextLine = $lines[$i + 1] ?? '';
            if ($this->isTableStart($line, $nextLine)) {
                $tableLines = [$line, $nextLine];
                $i += 2;
                while ($i < $count && trim($lines[$i]) !== '' && strpos($lines[$i], '|') !== false) {
                    $tableLines[] = $lines[$i];
                    $i++;
                }
                $html .= $this->renderTable($tableLines);
                continue;
            }

            if (preg_match('/^(#{1,6})\\s+(.*)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $text = trim($matches[2]);
                $id = $this->slugify($text);
                $html .= '<h' . $level . ' id="' . $this->escapeAttr($id) . '">' . $this->inline($text) . '</h' . $level . '>';
                $i++;
                continue;
            }

            if (preg_match('/^\\s*([-*_])\\1\\1+\\s*$/', $line)) {
                $html .= '<hr>';
                $i++;
                continue;
            }

            if (preg_match('/^\\s*>\\s?(.*)$/', $line)) {
                $quoteLines = [];
                while ($i < $count && preg_match('/^\\s*>\\s?(.*)$/', $lines[$i], $m)) {
                    $quoteLines[] = $m[1];
                    $i++;
                }
                $content = $this->markdownToHtml(implode("\n", $quoteLines));
                $html .= '<blockquote>' . $content . '</blockquote>';
                continue;
            }

            if ($this->isListStart($line)) {
                [$listHtml, $nextIndex] = $this->renderList($lines, $i);
                $html .= $listHtml;
                $i = $nextIndex;
                continue;
            }

            $paragraphLines = [];
            while ($i < $count && trim($lines[$i]) !== '' && !$this->isBlockStart($lines[$i], $lines[$i + 1] ?? '')) {
                $paragraphLines[] = trim($lines[$i]);
                $i++;
            }
            $paragraphText = implode(' ', $paragraphLines);
            if ($paragraphText !== '') {
                $html .= '<p>' . $this->inline($paragraphText) . '</p>';
            }
        }

        return $html;
    }

    private function isBlockStart(string $line, string $nextLine): bool
    {
        $trimmed = trim($line);
        return $trimmed === ''
            || preg_match('/^(#{1,6})\\s+/', $line)
            || preg_match('/^```/', $line)
            || preg_match('/^\\s*>/', $line)
            || $this->isListStart($line)
            || $this->isTableStart($line, $nextLine)
            || preg_match('/^\\s*([-*_])\\1\\1+\\s*$/', $line);
    }

    private function isListStart(string $line): bool
    {
        return (bool) preg_match('/^\\s*([-+*])\\s+.+$/', $line)
            || (bool) preg_match('/^\\s*\\d+\\.\\s+.+$/', $line);
    }

    private function renderList(array $lines, int $startIndex): array
    {
        $isOrdered = (bool) preg_match('/^\\s*\\d+\\.\\s+/', $lines[$startIndex]);
        $tag = $isOrdered ? 'ol' : 'ul';
        $items = [];
        $i = $startIndex;
        $count = count($lines);

        while ($i < $count) {
            $line = $lines[$i];
            if ($isOrdered) {
                if (!preg_match('/^\\s*\\d+\\.\\s+(.+)$/', $line, $matches)) {
                    break;
                }
            } else {
                if (!preg_match('/^\\s*[-+*]\\s+(.+)$/', $line, $matches)) {
                    break;
                }
            }
            $items[] = '<li>' . $this->inline($matches[1]) . '</li>';
            $i++;
        }

        $html = "<{$tag}>" . implode('', $items) . "</{$tag}>";
        return [$html, $i];
    }

    private function isTableStart(string $line, string $nextLine): bool
    {
        if (strpos($line, '|') === false || strpos($nextLine, '|') === false) {
            return false;
        }
        return (bool) preg_match('/^\\s*\\|?\\s*:?-+:?\\s*(\\|\\s*:?-+:?\\s*)+\\|?\\s*$/', $nextLine);
    }

    private function renderTable(array $lines): string
    {
        $header = $this->splitTableRow($lines[0]);
        $bodyRows = array_slice($lines, 2);

        $headCells = array_map(fn (string $cell): string => '<th>' . $this->inline($cell) . '</th>', $header);
        $thead = '<thead><tr>' . implode('', $headCells) . '</tr></thead>';

        $rowsHtml = [];
        foreach ($bodyRows as $row) {
            if (trim($row) === '') {
                continue;
            }
            $cells = $this->splitTableRow($row);
            $cellsHtml = array_map(fn (string $cell): string => '<td>' . $this->inline($cell) . '</td>', $cells);
            $rowsHtml[] = '<tr>' . implode('', $cellsHtml) . '</tr>';
        }

        $tbody = '<tbody>' . implode('', $rowsHtml) . '</tbody>';
        return '<table>' . $thead . $tbody . '</table>';
    }

    private function splitTableRow(string $row): array
    {
        $row = trim($row);
        $row = trim($row, '|');
        $cells = array_map('trim', explode('|', $row));
        return $cells;
    }

    private function inline(string $text): string
    {
        $placeholders = [];
        $index = 0;

        $text = preg_replace_callback('/`([^`]+)`/', function (array $matches) use (&$placeholders, &$index): string {
            $key = '[[CODE_' . $index . ']]';
            $placeholders[$key] = '<code>' . $this->escapeHtml($matches[1]) . '</code>';
            $index++;
            return $key;
        }, $text);

        $text = preg_replace_callback('/!\\[([^\\]]*)\\]\\(([^\\)]+)\\)/', function (array $matches): string {
            $alt = $this->escapeAttr($matches[1]);
            $src = $this->escapeAttr($this->rewriteLink($matches[2]));
            return '<img src="' . $src . '" alt="' . $alt . '">';
        }, $text);

        $text = $this->escapeHtml($text);

        $text = preg_replace_callback('/\\[([^\\]]+)\\]\\(([^\\)]+)\\)/', function (array $matches): string {
            $label = $this->escapeHtml($matches[1]);
            $href = $this->escapeAttr($this->rewriteLink($matches[2]));
            return '<a href="' . $href . '">' . $label . '</a>';
        }, $text);

        $text = preg_replace('/\\*\\*([^*]+)\\*\\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\\*([^*]+)\\*/', '<em>$1</em>', $text);

        foreach ($placeholders as $key => $value) {
            $text = str_replace($key, $value, $text);
        }

        return $text;
    }

    private function rewriteLink(string $href): string
    {
        $href = trim($href);
        if (str_starts_with($href, '../')) {
            return $href;
        }
        if (str_ends_with($href, '.md')) {
            return substr($href, 0, -3) . '.html';
        }
        return $href;
    }

    private function extractTitle(string $markdown): ?string
    {
        $lines = preg_split('/\\r\\n|\\n|\\r/', $markdown);
        foreach ($lines as $line) {
            $line = $this->stripBom($line);
            if (preg_match('/^#{1,6}\\s+(.+)$/', $line, $matches)) {
                return trim($matches[1]);
            }
        }
        return null;
    }

    private function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\\s-]/', '', $text) ?? '';
        $text = trim(preg_replace('/\\s+/', '-', $text) ?? '', '-');
        return $text === '' ? 'section' : $text;
    }

    private function stripBom(string $text): string
    {
        if (str_starts_with($text, "\xEF\xBB\xBF")) {
            return substr($text, 3);
        }
        return $text;
    }

    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function escapeAttr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function ensureDir(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    private function syncDocsCss(): void
    {
        $source = $this->root . '/documentation/assets/documentation.css';
        $target = $this->assetsDir . '/documentation.css';
        if (!is_file($source)) {
            fwrite(STDERR, "Missing documentation.css at {$source}\n");
            return;
        }
        copy($source, $target);
    }
}

$root = dirname(__DIR__, 2);
$builder = new UiDocsBuilder($root);
$builder->build();
