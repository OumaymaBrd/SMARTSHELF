<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    public function createToken(User $user, string $tokenName = 'auth-token')
    {
        return $user->createToken($tokenName)->plainTextToken;
    }
    
    public function revokeTokens(User $user)
    {
        return $user->tokens()->delete();
    }
}