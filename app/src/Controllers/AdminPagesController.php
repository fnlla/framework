<?php

declare(strict_types=1);

namespace App\Controllers;

use Finella\Http\Response;

final class AdminPagesController
{
    public function index(): Response
    {
        return view('admin/index', layout: 'layouts/ui');
    }

    public function analytics(): Response
    {
        return view('admin/analytics', layout: 'layouts/ui');
    }

    public function audit(): Response
    {
        return view('admin/audit', layout: 'layouts/ui');
    }

    public function settings(): Response
    {
        return view('admin/settings', layout: 'layouts/ui');
    }
}
