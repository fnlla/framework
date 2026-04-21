<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Monitoring;

use Finella\Http\Response;

final class MonitoringController
{
    public function __construct(private MonitoringManager $monitoring)
    {
    }

    public function show(): Response
    {
        return Response::json($this->monitoring->metrics());
    }
}
