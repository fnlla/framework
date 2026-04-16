<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AppStatusService;
use Finella\Http\Request;
use Finella\Http\Response;

final class HealthController
{
    public function __construct(private ?object $status = null)
    {
        if ($this->status === null && class_exists(AppStatusService::class)) {
            $this->status = new AppStatusService();
        }
    }

    public function show(Request $request): Response
    {
        $snapshot = [
            'status' => 'ok',
            'service' => 'finella',
        ];

        if ($this->status !== null && method_exists($this->status, 'snapshot')) {
            $snapshot = $this->status->snapshot();
        }

        $root = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 3);
        $viewPath = $root . '/resources/views/ops/health.php';
        if (!$this->wantsJson($request) && is_file($viewPath)) {
            return view('ops/health', [
                'snapshot' => $snapshot,
            ]);
        }

        return Response::json($snapshot);
    }

    private function wantsJson(Request $request): bool
    {
        $format = strtolower((string) $request->input('format', ''));
        return $format === 'json' || $request->wantsJson();
    }
}