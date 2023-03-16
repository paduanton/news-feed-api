<?php

namespace App\Http\Controllers\API;

use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Resources\FeedPreferencesResource;
use App\Models\Users;
use App\Models\FeedPreferences;

class FeedPreferencesController extends Controller
{
    public function __construct()
    {
    }

    public function getFeedPreferencesByUsersId(Request $request, $usersId)
    {
        $user = Users::findOrFail($usersId);
        $userFeedPreferences = $user->feedPreferences;

        if ($userFeedPreferences->isEmpty()) {
            throw new ModelNotFoundException();
        }

        return FeedPreferencesResource::collection($userFeedPreferences);
    }

    public function store(Request $request, $usersId)
    {
        $this->validate($request, [
            'content' => 'required|string',
            'type' => [
                'required',
                'string',
                Rule::in(['category', 'author', 'source', 'keyword', 'date']),
            ],
        ]);

        Users::findOrFail($usersId);

        $feedPreferences = [
            'users_id' => $usersId,
            'content' => $request['content'],
            'type' => $request['type'],
        ];

        $feedPreferences = FeedPreferences::create($feedPreferences);

        if ($feedPreferences) {
            return new FeedPreferencesResource($feedPreferences);
        }

        return response()->json(
            [
                'message' => 'could not store data',
            ],
            400
        );
    }

    public function destroy($id)
    {
        FeedPreferences::findOrFail($id);

        $delete = FeedPreferences::where('id', $id)->delete();

        if ($delete) {
            return response()->json([], 204);
        }

        return response()->json([
            'message' => 'could not delete feed preferences data',
        ], 400);
    }
}
