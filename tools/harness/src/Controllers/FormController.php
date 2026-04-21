<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace App\Controllers;

use Finella\Http\Request;
use Finella\Http\Response;

final class FormController
{
    public function show(): Response
    {
        return view('forms/form');
    }

    public function submit(Request $request): Response
    {
        $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email',
        ]);

        return Response::redirect('/form');
    }
}

