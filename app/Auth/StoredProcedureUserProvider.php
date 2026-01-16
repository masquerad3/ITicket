<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Support\Facades\DB;

class StoredProcedureUserProvider implements UserProvider
{
    public function __construct(
        private readonly HasherContract $hasher,
        private readonly string $modelClass,
    ) {
    }

    private function makeUserFromRow(object $row): Authenticatable
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = new $this->modelClass();

        // Hydrate without triggering any Eloquent queries.
        $user->forceFill((array) $row);
        $user->exists = true;

        return $user;
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        $rows = DB::select('EXEC dbo.sp_read_user_by_id @user_id = ?', [$identifier]);
        $row = $rows[0] ?? null;

        return $row ? $this->makeUserFromRow($row) : null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        // "Remember me" is not used in this app.
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // No-op: remember tokens are not used.
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $email = $credentials['email'] ?? null;
        if (!is_string($email) || $email === '') {
            return null;
        }

        $rows = DB::select('EXEC dbo.sp_read_user_by_email @email = ?', [$email]);
        $row = $rows[0] ?? null;

        return $row ? $this->makeUserFromRow($row) : null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $plain = $credentials['password'] ?? null;
        if (!is_string($plain) || $plain === '') {
            return false;
        }

        return $this->hasher->check($plain, $user->getAuthPassword());
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Not needed; this project hashes passwords during registration/password update.
    }
}
