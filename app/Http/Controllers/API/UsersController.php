<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UsersResource;

class UsersController extends Controller
{

    public function __construct()
    {
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $authenticatedUserId = $user->id;

        if($authenticatedUserId != $id) {
            return response()->json(
                [
                    'message' =>
                        "It wasn't possible to retrieve user data!",
                ],
                401
            );
        }

        return new UsersResource($user);
    }
}
