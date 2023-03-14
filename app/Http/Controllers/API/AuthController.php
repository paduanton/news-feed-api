<?php

namespace App\Http\Controllers\API;

use App\Services\AuthenticationService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Model\OAuthRefreshTokens;
use App\Model\OAuthAccessTokens;
use Carbon\Carbon;
use App\Model\Users;
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
        $age = $this->authService->getUserAgeLimitDate();

        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed|string|min:6',
            'birthday' => "nullable|date_format:Y/m/d|before:{$age}|after:1920-01-01",
            'remember_me' => 'nullable|boolean',
        ]);

        $remember = $request['remember_me'];
        $request['username'] = $this->authService->createUsername($request['name']);
        $request['password'] = $this->authService->hashPassword($request['password']);

        $user = Users::create($request->all());
        Auth::login($user, $remember);

        if ($user) {
            $this->authService->sendWelcomedMail($user);
            $this->verifyEmailController->verify($request, $user->id);

            return $this->authService->createUserAuthResource($user);
        }

        return response()->json([
            'message' => "couldn't sign user up"
        ], 400);
    }


    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|required_without:username',
            'username' => 'string|required_without:email',
            'password' => 'required|string',
            'remember_me' => 'nullable|boolean'
        ]);

        $remember = $request['remember_me'];
        $login = isset($request['username']) ? 'username' : 'email';

        Users::where($login, $request[$login])->firstOrFail();

        $credentials = request([$login, 'password']);
        if (!Auth::attempt($credentials, $remember)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => "Invalid credentials",
            ], 401);
        }

        $user = $request->user();
        $user->update(['password' => $this->authService->rehashPasswordIfNeeded($user->password)]);

        return $this->authService->createUserAuthResource($user);
    }

    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();
        $accessTokenId = $request->user()->token()->id;
        $accessTokenModel = OAuthAccessTokens::findOrFail($accessTokenId);

        $revokeAccessToken = $accessToken->revoke();

        if ($revokeAccessToken) {
            $this->authService->revokeRefreshToken($accessTokenModel->refresh_token->token);

            return response()->json([
                'message' => 'Logout successfully'
            ], 200);
        }

        return response()->json([
            'message' => "It wasn't possible to logout, please try again"
        ], 409);
    }
}
