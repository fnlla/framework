<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AppReadinessService;
use App\Services\AppStatusService;
use Finella\Http\Response;

final class HomeController
{
    public function index(): Response
    {
        $status = class_exists(AppStatusService::class)
            ? (new AppStatusService())->snapshot()
            : ['status' => 'ok'];
        $ready = class_exists(AppReadinessService::class)
            ? (new AppReadinessService())->snapshot()
            : ['status' => 'ok'];

        $layout = null;

        $payload = [
            'statusOk' => ($status['status'] ?? '') === 'ok',
            'readyOk' => ($ready['status'] ?? '') === 'ok',
            'healthOk' => ($status['status'] ?? '') === 'ok',
        ];

        return $layout === null
            ? view('pages/home', $payload)
            : view('pages/home', $payload, $layout);
    }
}
