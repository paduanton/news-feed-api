<?php

namespace App\Services\Interfaces;

use App\Models\Users;
use Carbon\Carbon;

interface AuthenticationInterface
{
    public static function getUniqueHash(int $size = 32);
    public function hashPassword(string $password);
    public function createRefreshToken(string $accessTokenId, Carbon $accessTokenExpiresAt);
    public function createUserAuthResource(Users $user);
    public function rehashPasswordIfNeeded(string $hashedPassword);
    public function revokeRefreshToken(string $token);
}
