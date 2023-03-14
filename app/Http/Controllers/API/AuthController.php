<?php

namespace App\Http\Controllers\API;

use App\Services\AuthenticationService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\OAuthAccessTokens;
use App\Models\Users;
use Exception;

class AuthController extends Controller
{
    protected $authService, $verifyEmailController;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
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

        $user = Users::create($request->all());
        Auth::login($user, $remember);

        if ($user) {
            return $this->authService->createUserAuthResource($user);
        }

        return response()->json(
            [
                'message' => "couldn't sign user up",
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
        $login = isset($request['username']) ? 'username' : 'email';

        Users::where($login, $request[$login])->firstOrFail();

        $credentials = request([$login, 'password']);
        if (!Auth::attempt($credentials, $remember)) {
            return response()->json(
                [
                    'error' => 'Unauthorized',
                    'message' => 'Invalid credentials',
                ],
                401
            );
        }

        $user = $request->user();
        $user->update([
            'password' => $this->authService->rehashPasswordIfNeeded(
                $user->password
            ),
        ]);

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
                $accessTokenModel->refresh_token->token
            );

            return response()->json(
                [
                    'message' => 'Logout successfully',
                ],
                200
            );
        }

        return response()->json(
            [
                'message' => "It wasn't possible to logout, please try again",
            ],
            409
        );
    }
}
