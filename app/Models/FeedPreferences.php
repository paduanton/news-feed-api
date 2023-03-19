<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class FeedPreferences extends Model
{
    use SoftDeletes;

    protected $table = 'feed_preferences';

    protected $fillable = ['users_id', 'content', 'type'];

    protected $hidden = ['deleted_at'];

    public function getFeedPreferencesById($id)
    {
        return FeedPreferences::findOrFail($id);
    }

    public function getFeedPreferencesByType($type)
    {
        return FeedPreferences::where('type', $type)->get();
    }

    public function deleteFeedPreference($id)
    {
        return FeedPreferences::where('id', $id)->delete();
    }

    public function createFeedPreference($feedPreferenceData)
    {
        return FeedPreferences::create($feedPreferenceData);
    }

    public function users()
    {
        return $this->belongsTo(Users::class);
    }
}
