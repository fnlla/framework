<?php

/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Core;

use Finella\Core\Application;
use Finella\Http\Response;
use Finella\View\View;

/**
 * @api
 */
class Controller
{
    public function __construct(protected Application $app)
    {
    }

    protected function view(string $template, array $data = [], ?string $layout = null, int $status = 200): Response
    {
        return Response::html(View::render($this->app, $template, $data, $layout), $status);
    }
}






