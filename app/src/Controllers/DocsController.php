<?php

declare(strict_types=1);

namespace App\Controllers;

use Finella\Docs\DocsFinder;
use Finella\Docs\DocsMarkdownRenderer;
use Finella\Http\Request;
use Finella\Http\Response;

final class DocsController
{
    public function __construct(
        private DocsFinder $finder,
        private DocsMarkdownRenderer $markdown
    ) {
    }

    public function index(): Response
    {
        return $this->render('index');
    }

    public function show(Request $request): Response
    {
        $slug = (string) $request->getAttribute('slug', '');
        if ($slug === '') {
            return $this->render('index');
        }

        return $this->render($slug);
    }

    private function render(string $slug): Response
    {
        $path = $this->finder->resolve($slug);
        if ($path === null || !is_file($path)) {
            return Response::html('Doc not found', 404);
        }

        $contents = (string) file_get_contents($path);
        $title = $this->titleFromMarkdown($contents, $slug);
        $docs = $this->finder->listDocs();
        $docHtml = $this->markdown->toHtml($contents);

        return view('docs/show', [
            'title' => $title,
            'slug' => $slug,
            'doc' => $contents,
            'docHtml' => $docHtml,
            'docs' => $docs,
        ]);
    }

    private function titleFromMarkdown(string $contents, string $fallback): string
    {
        $lines = preg_split('/\r?\n/', $contents) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '# ')) {
                return trim(substr($line, 2));
            }
        }
        return $fallback;
    }

}
