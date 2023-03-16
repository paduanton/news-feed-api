<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthAccessTokens extends Model
{
    protected $table = 'oauth_access_tokens';

    protected $fillable = [
        'user_id', 'client_id', 'scopes', 'revoked', 'expires_at'
    ];

    protected $casts = [
        'id' => 'string'
    ];


    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function refreshToken()
    {
        return $this->hasOne(OAuthRefreshTokens::class, 'access_token_id');
    }
}
