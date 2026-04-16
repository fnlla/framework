<?php

declare(strict_types=1);

return [
    'paper' => env('PDF_PAPER', 'A4'),
    'orientation' => env('PDF_ORIENTATION', 'portrait'),
    'default_font' => env('PDF_DEFAULT_FONT', 'DejaVu Sans'),
    'remote_enabled' => (bool) env('PDF_REMOTE_ENABLED', false),
    'download_name' => env('PDF_DOWNLOAD_NAME', 'document.pdf'),
];
