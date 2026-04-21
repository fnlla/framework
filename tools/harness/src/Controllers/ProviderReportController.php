<?php
/**
 * fnlla
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace App\Controllers;

use Fnlla\Http\Response;
use Fnlla\Support\ProviderReport;

final class ProviderReportController
{
    public function show(): Response
    {
        $debugValue = getenv('APP_DEBUG');
        $debug = is_string($debugValue) && in_array(strtolower(trim($debugValue)), ['1', 'true', 'yes', 'on'], true);
        $env = strtolower((string) getenv('APP_ENV'));
        if (!$debug && !in_array($env, ['local', 'dev', 'development'], true)) {
            return Response::text('Not Found', 404);
        }

        $app = app();
        $report = $app->make(ProviderReport::class);
        $text = $report instanceof ProviderReport ? $report->toText() : 'Provider report unavailable.';
        return Response::text($text);
    }
}
