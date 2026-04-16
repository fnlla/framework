<?php

declare(strict_types=1);

require __DIR__ . '/../../_shared/tests/bootstrap.php';

use Finella\Seo\SeoManager;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: $msg\n");
        exit(1);
    }
}

$seo = new SeoManager();
$seo->title('TechAyo')->description('Services');
$html = $seo->render();

ok(str_contains($html, '<title>'), 'SEO title rendered');

echo "SEO smoke tests OK\n";
