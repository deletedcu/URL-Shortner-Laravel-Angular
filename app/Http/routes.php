<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::get('/', 'AngularController@serveApp');
    Route::get('/unsupported-browser', 'AngularController@unsupported');
    Route::get('user/verify/{verificationCode}', ['uses' => 'Auth\AuthController@verifyUserEmail']);
    Route::get('auth/{provider}', ['uses' => 'Auth\AuthController@redirectToProvider']);
    Route::get('auth/{provider}/callback', ['uses' => 'Auth\AuthController@handleProviderCallback']);
    Route::get('/api/authenticate/user', 'Auth\AuthController@getAuthenticatedUser');

    Route::get('/short/{token}', 'UserController@visitUrl');
});

$api->group(['middleware' => ['api']], function ($api) {
    $api->controller('auth', 'Auth\AuthController');

    // Password Reset Routes...
    $api->post('auth/password/email', 'Auth\PasswordResetController@sendResetLinkEmail');
    $api->get('auth/password/verify', 'Auth\PasswordResetController@verify');
    $api->post('auth/password/reset', 'Auth\PasswordResetController@reset');
});

$api->group(['middleware' => ['api', 'api.auth']], function ($api) {
    $api->get('users/me', 'UserController@getMe');
    $api->put('users/me', 'UserController@putMe');

    //Custom API endpoints for short urls management
    $api->get('users/urls', 'UserController@getUrls');
    $api->get('users/{id}/urls', 'UserController@getUrlsShow');
    $api->post('users/urls', 'UserController@createUrl');
    $api->get('urls/show/{id}', 'UserController@getUrl');
    $api->put('urls/show', 'UserController@updateUrl');
    $api->delete('urls/{id}', 'UserController@deleteUrl');
});

$api->group(['middleware' => ['api', 'api.auth', 'role:role.admin']], function ($api) {
    $api->controller('users', 'UserController');
    $api->post('users', 'UserController@create');
});
