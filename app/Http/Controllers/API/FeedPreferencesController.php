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
    protected $userModel, $feedPreferences;

    public function __construct(
        Users $userModel,
        FeedPreferences $feedPreferences
    ) {
        $this->userModel = $userModel;
        $this->feedPreferences = $feedPreferences;
    }

    public function getFeedPreferencesByUsersId(Request $request, $usersId)
    {
        $user = $this->userModel->getUserById($usersId);
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

        $this->userModel->getUserById($usersId);

        if ($request['type'] === 'keyword') {
            $keywordFeedPreferences = $this->feedPreferences->getFeedPreferencesByType(
                $request['type']
            );


            if (!$keywordFeedPreferences->isEmpty()) {
                return response()->json(
                    [
                        'message' =>
                            'You already have a keyword on your preferences',
                    ],
                    400
                );
            }
        }

        $feedPreferenceData = [
            'users_id' => $usersId,
            'content' => $request['content'],
            'type' => $request['type'],
        ];

        $feedPreferences = $this->feedPreferences->createFeedPreference(
            $feedPreferenceData
        );

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
        $this->feedPreferences->getFeedPreferencesById($id);

        $delete = $this->feedPreferences->deleteFeedPreference($id);

        if ($delete) {
            return response()->json([], 204);
        }

        return response()->json(
            [
                'message' => 'could not delete feed preferences data',
            ],
            400
        );
    }
}
