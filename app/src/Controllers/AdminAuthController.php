<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AdminAuthService;
use Finella\Http\Request;
use Finella\Http\Response;

final class AdminAuthController
{
    public function loginForm(Request $request): Response
    {
        $service = new AdminAuthService();
        $configured = $service->isConfigured();
        $redirect = $service->peekIntended();

        $payload = $request->getParsedBody();
        $data = is_array($payload) ? $payload : [];
        $query = $request->getQueryParams();
        if ($redirect === '') {
            $redirect = trim((string) ($data['redirect'] ?? ''));
        }
        if ($redirect === '') {
            $redirect = trim((string) ($query['redirect'] ?? ''));
        }

        return view('admin/login', [
            'error' => null,
            'notice' => $configured ? null : 'Admin login is not configured. Set ADMIN_LOGIN_EMAIL and ADMIN_LOGIN_PASSWORD_HASH in .env.',
            'email' => '',
            'redirect' => $service->sanitizeRedirect($redirect),
            'configured' => $configured,
        ], 'layouts/ui');
    }

    public function loginSubmit(Request $request): Response
    {
        $service = new AdminAuthService();

        $payload = $request->getParsedBody();
        $data = is_array($payload) ? $payload : [];
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $redirect = trim((string) ($data['redirect'] ?? ''));

        $result = $service->attempt($email, $password, $request);
        if (($result['ok'] ?? false) !== true) {
            return view('admin/login', [
                'error' => (string) ($result['error'] ?? 'Invalid admin credentials.'),
                'notice' => null,
                'email' => $email,
                'redirect' => $service->sanitizeRedirect($redirect),
                'configured' => $service->isConfigured(),
            ], 'layouts/ui');
        }

        $target = $service->consumeIntended();
        if ($redirect !== '') {
            $target = $redirect;
        }
        $target = $service->sanitizeRedirect($target);

        return Response::redirect($target);
    }

    public function logout(): Response
    {
        $service = new AdminAuthService();
        $service->logout();

        return Response::redirect('/admin/login');
    }
}
