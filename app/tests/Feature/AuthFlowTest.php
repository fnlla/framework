<?php

declare(strict_types=1);

use Finella\Auth\PasswordResetManager;
use PHPUnit\Framework\Assert;
use Tests\Feature\Support\FeatureTestCase;

final class AuthFlowTest extends FeatureTestCase
{
    public function testAuthLoginPageIsAvailable(): void
    {
        $this->get('/auth/login')->assertStatus(200);
    }

    public function testAuthRegisterPageIsAvailable(): void
    {
        $this->get('/auth/register')->assertStatus(200);
    }

    public function testAuthForgotPasswordPageIsAvailable(): void
    {
        $this->get('/auth/password/forgot')->assertStatus(200);
    }

    public function testAuthResetPageIsAvailable(): void
    {
        $this->get('/auth/password/reset/token-123')->assertStatus(200);
    }

    public function testLoginValidationReturns422WhenRequiredFieldsAreMissing(): void
    {
        $this->post('/auth/login', [])->assertStatus(422);
    }

    public function testLoginValidationReturnsJsonErrorsWhenRequested(): void
    {
        $response = $this->postJson('/auth/login', [])->assertStatus(422);
        $json = $response->json();

        Assert::assertArrayHasKey('errors', $json);
        Assert::assertArrayHasKey('email', $json['errors']);
        Assert::assertArrayHasKey('password', $json['errors']);
    }

    public function testRegisterValidationRequiresEmail(): void
    {
        $this->post('/auth/register', [
            'name' => 'Alice',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertStatus(422);
    }

    public function testRegisterValidationRequiresMinimumPasswordLength(): void
    {
        $this->post('/auth/register', [
            'name' => 'Alice',
            'email' => $this->uniqueEmail(),
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertStatus(422);
    }

    public function testRegisterRejectsMismatchedPasswordConfirmation(): void
    {
        $this->post('/auth/register', [
            'name' => 'Alice',
            'email' => $this->uniqueEmail(),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!x',
        ])->assertStatus(422);
    }

    public function testRegisterCreatesUserAndRedirects(): void
    {
        $email = $this->uniqueEmail();

        $this->post('/auth/register', [
            'name' => 'Alice',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect('/');

        $user = $this->findUserByEmail($email);
        Assert::assertIsArray($user);
        Assert::assertSame($email, $user['email'] ?? null);
    }

    public function testGuestMiddlewareRedirectsAuthenticatedUserAwayFromLoginPage(): void
    {
        $user = $this->insertUser($this->uniqueEmail());

        $this->actingAs($user);
        $this->get('/auth/login')->assertRedirect('/');
    }

    public function testLoginRejectsInvalidCredentials(): void
    {
        $this->insertUser('user@example.test', 'Password123!');

        $this->post('/auth/login', [
            'email' => 'user@example.test',
            'password' => 'WrongPassword!',
        ])->assertStatus(422);
    }

    public function testLoginAuthenticatesValidCredentialsAndRedirects(): void
    {
        $this->insertUser('user@example.test', 'Password123!');

        $this->post('/auth/login', [
            'email' => 'user@example.test',
            'password' => 'Password123!',
        ])->assertRedirect('/');
    }

    public function testLogoutRequiresAuthentication(): void
    {
        $this->post('/auth/logout')->assertStatus(401);
    }

    public function testLogoutClearsAuthenticatedSession(): void
    {
        $user = $this->insertUser($this->uniqueEmail());
        $this->actingAs($user);

        $this->post('/auth/logout')->assertRedirect('/login');
        $this->get('/auth/login')->assertStatus(200);
    }

    public function testPasswordEmailValidationRejectsInvalidEmail(): void
    {
        $this->post('/auth/password/email', [
            'email' => 'not-an-email',
        ])->assertStatus(422);
    }

    public function testPasswordEmailReturnsGenericResponseForUnknownUser(): void
    {
        $this->post('/auth/password/email', [
            'email' => 'missing@example.test',
        ])->assertStatus(200);
    }

    public function testPasswordEmailJsonIncludesTokenInDebugModeForExistingUser(): void
    {
        $this->setEnvValue('APP_DEBUG', '1');
        $email = 'reset-json@example.test';
        $this->insertUser($email, 'Password123!');

        $response = $this->postJson('/auth/password/email', [
            'email' => $email,
        ])->assertStatus(200);

        $json = $response->json();
        Assert::assertArrayHasKey('token', $json);
        Assert::assertIsString($json['token']);
        Assert::assertNotSame('', $json['token']);
    }

    public function testPasswordResetRejectsUnknownUser(): void
    {
        $this->post('/auth/password/reset', [
            'email' => 'missing@example.test',
            'token' => 'nope',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertStatus(422);
    }

    public function testPasswordResetRejectsInvalidTokenForExistingUser(): void
    {
        $email = 'reset-invalid@example.test';
        $this->insertUser($email, 'Password123!');

        $this->post('/auth/password/reset', [
            'email' => $email,
            'token' => 'invalid-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertStatus(422);
    }

    public function testPasswordResetAcceptsIssuedTokenAndUpdatesPassword(): void
    {
        $email = 'reset-ok@example.test';
        $this->insertUser($email, 'Password123!');

        $manager = $this->app()->make(PasswordResetManager::class);
        Assert::assertInstanceOf(PasswordResetManager::class, $manager);
        $user = $this->findUserByEmail($email);
        Assert::assertIsArray($user);
        $token = $manager->createToken($user);

        $this->post('/auth/password/reset', [
            'email' => $email,
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertRedirect('/login');

        $this->post('/auth/login', [
            'email' => $email,
            'password' => 'NewPassword123!',
        ])->assertRedirect('/');
    }

    private function uniqueEmail(): string
    {
        return 'user-' . bin2hex(random_bytes(4)) . '@example.test';
    }
}
