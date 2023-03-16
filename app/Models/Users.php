<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Users extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'name', 'username', 'email', 'password'
    ];

    protected $hidden = [
        'password', 'remember_token', 'deleted_at'
    ];

    public function accessTokens()
    {
        return $this->hasMany(OAuthAccessTokens::class, 'user_id');
    }

    public function feedPreferences()
    {
        return $this->hasMany(FeedPreferences::class, 'users_id');
    }


    public function passwordResets()
    {
        return $this->hasMany(PasswordResets::class, 'email', 'email');
    }
}
