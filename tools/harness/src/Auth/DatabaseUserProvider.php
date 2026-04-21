<?php
/**
 * fnlla
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace App\Auth;

use Fnlla\Auth\CredentialsUserProviderInterface;
use Fnlla\Auth\PasswordResetUserProviderInterface;
use Fnlla\Auth\RegistrationUserProviderInterface;
use Fnlla\Auth\UserProviderInterface;
use Fnlla\Database\ConnectionManager;
use Fnlla\Database\Query;

final class DatabaseUserProvider implements UserProviderInterface, CredentialsUserProviderInterface, RegistrationUserProviderInterface, PasswordResetUserProviderInterface
{
    public function __construct(private ConnectionManager $connections)
    {
    }

    public function retrieveById(string|int $id): mixed
    {
        return $this->query()->table('users')->where('id', $id)->first();
    }

    public function retrieveByToken(string $token): mixed
    {
        return null;
    }

    public function retrieveByCredentials(array $credentials): mixed
    {
        $email = $credentials['email'] ?? null;
        if (!is_string($email) || $email === '') {
            return null;
        }

        return $this->query()->table('users')->where('email', $email)->first();
    }

    public function validateCredentials(mixed $user, array $credentials): bool
    {
        if (!is_array($user) || !isset($user['password'])) {
            return false;
        }

        $password = $credentials['password'] ?? null;
        if (!is_string($password)) {
            return false;
        }

        return password_verify($password, (string) $user['password']);
    }

    public function createUser(array $data): mixed
    {
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        if (!is_string($email) || $email === '' || !is_string($password) || $password === '') {
            return null;
        }

        $now = gmdate('Y-m-d H:i:s');
        $payload = [
            'name' => $data['name'] ?? null,
            'email' => $email,
            'password' => $password,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $this->query()->table('users')->insert($payload);
        $id = $this->connections->connection()->lastInsertId();
        if ($id !== false && $id !== '') {
            return $this->retrieveById(is_numeric($id) ? (int) $id : $id);
        }

        return $payload;
    }

    public function updatePassword(mixed $user, string $passwordHash): void
    {
        $id = $this->extractId($user);
        if ($id === null) {
            return;
        }

        $this->query()->table('users')->where('id', $id)->update([
            'password' => $passwordHash,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    private function query(): Query
    {
        return new Query($this->connections->connection());
    }

    private function extractId(mixed $user): string|int|null
    {
        if (is_array($user) && isset($user['id'])) {
            return $user['id'];
        }
        if (is_object($user) && property_exists($user, 'id')) {
            return $user->id;
        }
        if (is_object($user) && method_exists($user, 'getAuthIdentifier')) {
            return $user->getAuthIdentifier();
        }
        return null;
    }
}
