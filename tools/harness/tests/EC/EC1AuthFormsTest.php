<?php

declare(strict_types=1);

use Finella\Testing\TestCase;

final class EC1AuthFormsTest extends TestCase
{
    private array $users = [];
    private int $nextId = 1;

    public function setUp(): void
    {
        $this->csrfEnabled = true;
        parent::setUp();

        $users = &$this->users;
        $nextId = &$this->nextId;

        $this->app()->config()->set('auth.provider', [
            'by_id' => function ($id) use (&$users) {
                return $users[$id] ?? null;
            },
            'by_credentials' => function (array $creds) use (&$users) {
                foreach ($users as $user) {
                    if (($creds['email'] ?? null) === ($user->email ?? null)) {
                        return $user;
                    }
                }
                return null;
            },
            'validate' => fn ($user, array $creds) => ($creds['password'] ?? '') === 'secret',
            'create' => function (array $data) use (&$users, &$nextId) {
                $user = (object) array_merge(['id' => $nextId++], $data);
                $users[$user->id] = $user;
                return $user;
            },
            'update_password' => fn ($user, string $hash) => null,
        ]);
    }

    public function testRegisterValidationAndCsrf(): void
    {
        $this->withCsrf();

        $this->post('/_ec/register', [
            'name' => '',
            'email' => 'bad',
            'password' => 'x',
            'password_confirmation' => 'y',
        ], [
            'Referer' => '/_ec/register',
        ])
            ->assertRedirect('/_ec/register')
            ->assertSessionHasErrors(['name', 'email', 'password'])
            ->assertSessionHasOld(['email']);
    }

    public function testRegisterAndDashboardAccess(): void
    {
        $this->withCsrf();

        $this->post('/_ec/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.test',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ], [
            'Referer' => '/_ec/register',
        ])->assertRedirect('/_ec/dashboard');

        $this->get('/_ec/dashboard')->assertStatus(200)->assertJson(['ok' => true]);
    }
}
