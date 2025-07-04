<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    public function issueToken(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }

    public function register(array $data): User
    {
        $user = User::create($data);

        return $user;
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
