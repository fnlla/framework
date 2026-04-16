<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AppReadinessService;
use Finella\Http\Request;
use Finella\Http\Response;
use Finella\View\View;

final class ReadinessController
{
    public function __construct(private ?AppReadinessService $readiness = null)
    {
        $this->readiness = $this->readiness ?? new AppReadinessService();
    }

    public function show(Request $request): Response
    {
        $snapshot = $this->readiness->snapshot();
        $status = ($snapshot['status'] ?? '') === 'ok' ? 200 : 503;
        if ($this->wantsJson($request)) {
            return Response::json($snapshot, $status);
        }

        $html = View::render(app(), 'ops/ready', [
            'snapshot' => $snapshot,
        ]);
        return Response::html($html, $status);
    }

    private function wantsJson(Request $request): bool
    {
        $format = strtolower((string) $request->input('format', ''));
        return $format === 'json' || $request->wantsJson();
    }
}
