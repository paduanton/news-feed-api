<?php

namespace App\Http\Controllers\API;

use App\Services\AuthenticationService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\OAuthAccessTokens;
use App\Http\Resources\UsersResource;

class UsersController extends Controller
{
    protected $authService, $verifyEmailController;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
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
