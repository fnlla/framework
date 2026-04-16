<?php

declare(strict_types=1);

namespace App\Controllers;

use Finella\Http\Response;
use Finella\View\View;

final class ErrorController
{
    public function notFound(): Response
    {
        $html = View::render(app(), 'errors/404', [
            'status' => 404,
            'title' => 'Not Found',
            'message' => 'The page you are looking for does not exist.',
        ]);

        if ($html === '') {
            return Response::html('404 Not Found', 404);
        }

        return Response::html($html, 404);
    }
}
