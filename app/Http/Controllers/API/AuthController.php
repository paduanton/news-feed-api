<?php

namespace App\Http\Controllers\API;

use App\Services\AuthenticationService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\OAuthAccessTokens;
use App\Models\Users;

class AuthController extends Controller
{
    protected $authService, $verifyEmailController, $userModel;

    public function __construct(
        AuthenticationService $authService,
        Users $userModel
    ) {
        $this->authService = $authService;
        $this->userModel = $userModel;
    }

    public function signup(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'username' => 'required|unique:users',
            'email' => 'email|required|unique:users',
            'password' => 'required|string|min:6',
            'remember_me' => 'nullable|boolean',
        ]);

        $remember = $request['remember_me'];

        $request['password'] = $this->authService->hashPassword(
            $request['password']
        );

        $user = $this->userModel->createUser($request->all());

        if ($user) {
            Auth::login($user, $remember);

            return $this->authService->createUserAuthResource($user);
        }

        return response()->json(
            [
                'message' =>
                    "It wasn't possible to sign user up, please try again!",
            ],
            400
        );
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|required',
            'password' => 'required|string',
            'remember_me' => 'nullable|boolean',
        ]);

        $remember = $request['remember_me'];

        $user = $this->userModel->getUserByEmail($request['email']);

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials, $remember)) {
            return response()->json(
                [
                    'error' => 'Unauthorized',
                    'message' => 'Invalid credentials',
                ],
                401
            );
        }

        $this->userModel->updateUser(
            [
                'password' => $this->authService->rehashPasswordIfNeeded(
                    $user->password
                ),
            ],
            $user->id
        );

        return $this->authService->createUserAuthResource($user);
    }

    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();
        $accessTokenId = $request->user()->token()->id;
        $accessTokenModel = OAuthAccessTokens::findOrFail($accessTokenId);

        $revokeAccessToken = $accessToken->revoke();

        if ($revokeAccessToken) {
            $this->authService->revokeRefreshToken(
                $accessTokenModel->refreshToken->token
            );

            return response()->json(['message' => 'Logout successfully'], 200);
        }

        return response()->json(
            ['message' => "It wasn't possible to logout, please try again"],
            409
        );
    }
}
