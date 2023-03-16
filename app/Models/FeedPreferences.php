<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class FeedPreferences extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'feed_preferences';

    protected $fillable = ['content', 'type'];

    protected $hidden = ['deleted_at'];

    public function users()
    {
        return $this->belongsTo(Users::class);
    }
}
