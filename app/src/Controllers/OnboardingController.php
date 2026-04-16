<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace App\Controllers;

use Finella\Auth\AuthManager;
use Finella\Core\ConfigRepository;
use Finella\Database\ConnectionManager;
use Finella\Database\Query;
use Finella\Http\Request;
use Finella\Http\Response;
use Finella\Rbac\RbacManager;
use Finella\Support\ValidationException;

final class OnboardingController
{
    public function __construct(
        private AuthManager $auth,
        private ConfigRepository $config,
        private ConnectionManager $connections,
        private RbacManager $rbac
    ) {
    }

    public function show(): Response
    {
        if (!$this->enabled()) {
            return Response::text('Not Found', 404);
        }

        if ($this->hasUsers()) {
            return Response::redirect('/auth/login');
        }

        return view('onboarding/index');
    }

    public function submit(Request $request): Response
    {
        if (!$this->enabled()) {
            return Response::text('Not Found', 404);
        }

        try {
            $data = $request->validate([
                'name' => 'required|string|min:2|max:120',
                'email' => 'required|email',
                'password' => 'required|string|min:8',
            ]);
        } catch (ValidationException $e) {
            return view('onboarding/index', [
                'errors' => $e->errors(),
                'old' => $e->oldInput(),
            ])->withStatus($e->status());
        }

        $confirm = $request->input('password_confirmation');
        if (is_string($confirm) && $confirm !== $data['password']) {
            return view('onboarding/index', [
                'errors' => ['password' => ['Password confirmation does not match.']],
                'old' => ['name' => $data['name'], 'email' => $data['email']],
            ])->withStatus(422);
        }

        $user = $this->auth->register($data);
        if ($user === null) {
            return Response::text('Unable to create user', 500);
        }

        $this->auth->login($user, false);
        $this->assignRole($user);

        $redirect = (string) $this->config->get('onboarding.redirect', '/');
        return Response::redirect($redirect);
    }

    private function enabled(): bool
    {
        return (bool) $this->config->get('onboarding.enabled', true);
    }

    private function hasUsers(): bool
    {
        try {
            $query = new Query($this->connections->connection());
            $row = $query->table('users')->limit(1)->get();
            return $row !== [];
        } catch (\Throwable) {
            return false;
        }
    }

    private function assignRole(mixed $user): void
    {
        $role = (string) $this->config->get('onboarding.role', 'owner');
        $permissions = $this->config->get('onboarding.permissions', []);
        if (!is_array($permissions)) {
            $permissions = [];
        }

        $this->rbac->ensureSchema();
        $userId = $this->auth->id();
        if ($userId !== null) {
            $this->rbac->assignRole($userId, $role);
        }
        foreach ($permissions as $permission) {
            if (is_string($permission) && $permission !== '') {
                $this->rbac->grantPermissionToRole($role, $permission);
            }
        }
    }
}

