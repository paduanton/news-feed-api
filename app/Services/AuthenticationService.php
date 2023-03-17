<?php

namespace App\Services;

use Exception;
use App\Models\Users;
use Carbon\Carbon;
use App\Models\OAuthRefreshTokens;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserAuthResource;
use App\Services\Interfaces\AuthenticationInterface;

class AuthenticationService implements AuthenticationInterface
{
    public function __construct()
    {
    }

    public function hashPassword($password)
    {
        return Hash::make($password);
    }

    public function rehashPasswordIfNeeded($hashedPassword)
    {
        if (Hash::needsRehash($hashedPassword)) {
            $hashedPassword = $this->hashPassword($hashedPassword);
        }

        return $hashedPassword;
    }

    public static function getUniqueHash(int $size = 32)
    {
        return bin2hex(openssl_random_pseudo_bytes($size));
    }

    public function createRefreshToken($accessTokenId, $accessTokenExpiresAt)
    {
        try {
            $uniqueHash = $this->getUniqueHash();

            $refreshToken = new OAuthRefreshTokens();
            $refreshToken->id = $uniqueHash;
            $refreshToken->access_token_id = $accessTokenId;
            $refreshToken->token = $uniqueHash . '?' . Str::random(690);
            $refreshToken->revoked = false;
            $refreshToken->expires_at = $accessTokenExpiresAt->addMonth(1);

            $findById = OAuthRefreshTokens::find($refreshToken->id);

            while ($findById && strlen($uniqueHash) > 767) {
                $uniqueHash = $this->getUniqueHash();

                $refreshToken->id = $uniqueHash;
                $refreshToken->token = $uniqueHash . '?' . Str::random(690);

                $findById = OAuthRefreshTokens::find($refreshToken->id);
            }

            $refreshToken->save();
        } catch (Exception $exception) {
            return false;
        }

        return $refreshToken->token;
    }

    public function revokeRefreshToken($token)
    {
        $parseToken = explode("?", $token);
        $refreshTokenId = $parseToken[0];
        OAuthRefreshTokens::where('id', $refreshTokenId)->update(["revoked" => true]);
    }

    public function createUserAuthResource(Users $user)
    {
        $token = $user->createToken('Personal Access Token');
        $accessToken = $token->accessToken;

        $expiresAt = Carbon::parse($token->token->expires_at);
        $createdAt =  Carbon::parse($token->token->created_at);

        $user['auth_resource'] = [
            'token_type' => 'Bearer',
            'expires_in' => $expiresAt,
            'access_token' => $accessToken,
            'created_at' => $createdAt,
            'refresh_token' => $this->createRefreshToken($token->token->id, $expiresAt),
            'remember_token' => $user->remember_token
        ];

        return new UserAuthResource($user);
    }
}
