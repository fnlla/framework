<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Ui\Commands;

use Finella\Console\CommandInterface;
use Finella\Console\ConsoleIO;

final class UiElementsPublishCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'ui:elements:publish';
    }

    public function getDescription(): string
    {
        return 'Publish Finella UI element pages into the app (public/ui-elements).';
    }

    public function run(array $args, array $options, ConsoleIO $io, string $root): int
    {
        $appRoot = $options['app'] ?? $options['a'] ?? $root;
        $force = isset($options['force']) || isset($options['f']);
        $dryRun = isset($options['dry-run']) || isset($options['dry_run']) || isset($options['n']);
        $target = $options['target'] ?? $options['t'] ?? 'public/ui-elements';
        $source = $options['source'] ?? $options['s'] ?? null;
        $only = $this->parseList($options['only'] ?? null);
        $exclude = $this->parseList($options['exclude'] ?? null);

        if (!is_string($appRoot) || trim($appRoot) === '') {
            $io->error('Missing --app=PATH.');
            return 1;
        }

        $appRoot = rtrim($appRoot, '/\\');
        if (!is_dir($appRoot)) {
            $io->error('App path not found: ' . $appRoot);
            return 1;
        }

        $sourcePath = $this->resolveSource($source);
        if ($sourcePath === null) {
            $io->error('UI elements not found. Use --source=PATH or ensure ui/elements exists.');
            return 1;
        }

        $targetPath = $appRoot . '/' . ltrim((string) $target, '/\\');
        if (!is_dir($targetPath) && !$dryRun) {
            mkdir($targetPath, 0777, true);
        }

        $files = $this->collectFiles($sourcePath);
        if ($only !== [] || $exclude !== []) {
            $files = array_values(array_filter(
                $files,
                fn (string $relative): bool => $this->shouldInclude($relative, $only, $exclude)
            ));
        }
        $copied = 0;
        $skipped = 0;

        foreach ($files as $relative) {
            $from = $sourcePath . '/' . $relative;
            $to = $targetPath . '/' . $relative;

            if (is_file($to) && !$force) {
                $skipped++;
                $io->line('SKIP ' . $relative);
                continue;
            }

            $dir = dirname($to);
            if (!is_dir($dir) && !$dryRun) {
                mkdir($dir, 0777, true);
            }

            if (!$dryRun) {
                if (!copy($from, $to)) {
                    $io->error('Failed to copy ' . $relative);
                    return 1;
                }
            }

            $copied++;
            $io->line('COPY ' . $relative);
        }

        if (($only !== [] || $exclude !== []) && is_file($sourcePath . '/index.html')) {
            $indexHtml = file_get_contents($sourcePath . '/index.html');
            if ($indexHtml !== false) {
                $liteIndex = $this->buildLiteIndex($indexHtml, $only, $exclude);
                if ($liteIndex !== '') {
                    $targetIndex = $targetPath . '/index.html';
                    if (!$dryRun) {
                        file_put_contents($targetIndex, $liteIndex);
                    }
                    $io->line('WRITE index.html (filtered)');
                }
            }
        }

        $io->line('Done. Copied ' . $copied . ', skipped ' . $skipped . '.');
        return 0;
    }

    private function resolveSource(mixed $source): ?string
    {
        if (is_string($source) && trim($source) !== '') {
            $path = rtrim($source, '/\\');
            return is_dir($path) ? $path : null;
        }

        $candidates = [
            // Package-local source (preferred).
            dirname(__DIR__, 2) . '/elements',
        ];

        foreach ($candidates as $path) {
            if (is_dir($path)) {
                return rtrim($path, '/\\');
            }
        }

        return null;
    }

    private function collectFiles(string $base): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $path = str_replace('\\', '/', $fileInfo->getPathname());
            $relative = ltrim(substr($path, strlen(str_replace('\\', '/', $base))), '/');
            if ($relative === '') {
                continue;
            }
            $files[] = $relative;
        }

        sort($files);
        return $files;
    }

    private function parseList(mixed $value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', $value));
        $parts = array_filter($parts, fn (string $item): bool => $item !== '');
        $parts = array_unique($parts);
        sort($parts);

        return array_values($parts);
    }

    private function shouldInclude(string $relative, array $only, array $exclude): bool
    {
        if (str_starts_with($relative, 'assets/')) {
            return true;
        }

        if ($relative === 'index.html') {
            return true;
        }

        if (!str_starts_with($relative, 'elements/')) {
            return true;
        }

        $segments = explode('/', $relative);
        $element = $segments[1] ?? '';

        if ($element === '') {
            return true;
        }

        if ($only !== [] && !in_array($element, $only, true)) {
            return false;
        }

        if ($exclude !== [] && in_array($element, $exclude, true)) {
            return false;
        }

        return true;
    }

    private function buildLiteIndex(string $html, array $only, array $exclude): string
    {
        if ($only === [] && $exclude === []) {
            return $html;
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($dom);
        $cards = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " fx-card ")]');

        if ($cards !== false) {
            foreach (iterator_to_array($cards) as $card) {
                if (!$card instanceof \DOMElement) {
                    continue;
                }

                $link = $xpath->query('.//a[@href]', $card)->item(0);
                if (!$link instanceof \DOMElement) {
                    continue;
                }

                $href = $link->getAttribute('href');
                $element = $this->extractElementFromHref($href);
                if ($element === null) {
                    continue;
                }

                if ($only !== [] && !in_array($element, $only, true)) {
                    $card->parentNode?->removeChild($card);
                    continue;
                }

                if ($exclude !== [] && in_array($element, $exclude, true)) {
                    $card->parentNode?->removeChild($card);
                }
            }
        }

        $grid = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " fx-card-grid ")]')->item(0);
        if ($grid instanceof \DOMElement) {
            $remaining = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " fx-card ")]', $grid);
            if ($remaining !== false && $remaining->length === 0) {
                $empty = $dom->createElement('div');
                $empty->setAttribute('class', 'fx-card fx-empty');

                $title = $dom->createElement('h3', 'No elements selected');
                $copy = $dom->createElement(
                    'p',
                    'Your publish filter removed all elements. Try --only=login,pricing or remove --exclude.'
                );
                $copy->setAttribute('class', 'fx-muted');

                $link = $dom->createElement('a', 'Open Finella UI docs');
                $link->setAttribute('href', '/ui/documentation/');
                $link->setAttribute('class', 'f-btn f-btn-outline');

                $empty->appendChild($title);
                $empty->appendChild($copy);
                $empty->appendChild($link);
                $grid->appendChild($empty);
            }
        }

        $output = $dom->saveHTML();
        if ($output === false) {
            return $html;
        }

        if (!str_starts_with(ltrim($output), '<!doctype')) {
            $output = "<!doctype html>\n" . $output;
        }

        return $output;
    }

    private function extractElementFromHref(string $href): ?string
    {
        if (preg_match('#^elements/([^/]+)/index\\.html$#', $href, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
