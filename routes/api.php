<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->get('/', function () {
    return ['new-feed-api', now()->toDateTimeString(), config('app.env')];
});

Route::group(
    ['prefix' => '/v1', 'middleware' => 'throttle:60,1'],
    function () use ($router) {
        /* -----  Unauthenticated Routes  ----- */

        $router->get('/', function () {
            return [
                'news-feed-v1-api',
                now()->toDateTimeString(),
                config('app.env'),
            ];
        });

        $router->post('/login', 'API\AuthController@login');
        $router->post('/signup', 'API\AuthController@signup');

        Route::middleware('auth:api')->group(function () use ($router) {
            /* -----  Authenticated Routes  ----- */

            /*
            Authentication Routes
        */

            $router->post('/logout', 'API\AuthController@logout');

            /*
            Users Routes
        */

            $router->get('/users/{id}', 'API\UsersController@show');

            /*
            Article Routes
        */

            $router->get(
                '/articles/{keyword}/search',
                'API\ArticlesController@search'
            );

            /*
            Feed Preferences Routes
        */

            $router->post(
                '/users/{usersId}/feed_preferences',
                'API\FeedPreferencesController@store'
            );
            $router->get(
                '/users/{usersId}/feed_preferences',
                'API\FeedPreferencesController@getFeedPreferencesByUsersId'
            );
            $router->delete(
                '/feed_preferences/{id}',
                'API\FeedPreferencesController@destroy'
            );
        });
    }
);
