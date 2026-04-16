<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AppReadinessService;
use App\Services\AppStatusService;
use Finella\Http\Request;
use Finella\Http\Response;

final class StatusController
{
    public function show(Request $request): Response
    {
        $status = (new AppStatusService())->snapshot();
        $readiness = (new AppReadinessService())->snapshot();
        $payload = [
            'status' => $status,
            'health' => $status,
            'readiness' => $readiness,
        ];
        if ($this->wantsJson($request)) {
            return Response::json($payload);
        }

        return view('ops/status', [
            'status' => $status,
            'health' => $status,
            'readiness' => $readiness,
        ]);
    }

    private function wantsJson(Request $request): bool
    {
        $format = strtolower((string) $request->input('format', ''));
        return $format === 'json' || $request->wantsJson();
    }
}
