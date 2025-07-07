<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    /**
     * Issue a new authentication token for the given user
     *
     * Creates a new personal access token for the user that can be used
     * for API authentication.
     *
     * @param  User  $user  The user to issue the token for
     * @return string The plain text token string
     */
    public function issueToken(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }

    /**
     * Register a new user
     *
     * Creates a new user account with the provided data.
     *
     * @param  array  $data  The user registration data (name, email, password, etc.)
     * @return User The newly created user instance
     */
    public function register(array $data): User
    {
        $user = User::create($data);

        return $user;
    }

    /**
     * Logout the user by deleting their current access token
     *
     * Revokes the user's current authentication token, effectively
     * logging them out from the current session.
     *
     * @param  User  $user  The user to logout
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
