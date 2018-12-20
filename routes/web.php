<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => env('API_PREFIX', '/')], function () use ($router) {
    $router->group(
        ['prefix' => 'auth', 'middleware' => 'cors'],
        function () use ($router) {
            $router->post('login/', 'Auth\AuthController@authenticate');

            $router->post('forgot-password/', 'Auth\AuthController@forgotPassword');

            $router->post('check-reset-password-token/', 'Auth\AuthController@checkResetPasswordToken');

            $router->post('reset-password/', 'Auth\AuthController@resetPassword');

            $router->get('logout/', 'Auth\AuthController@logout');
            $router->get('refresh-token/', 'Auth\AuthController@refresh');
        });

    $router->group(
        ['prefix' => '/', 'middleware' => 'cors'], function () use ($router) {
        $router->get('pages/', 'Unsecure\PageController@menuPages');
        $router->get('pages/{slug}', 'Unsecure\PageController@page');
        $router->get('news/', 'Unsecure\NewsController@list');
        $router->get('news/{slug}', 'Unsecure\NewsController@news');
        $router->get('slider-images/', 'Unsecure\SliderImagesController@list');
    });

    $router->group(
        ['prefix' => 'secure', 'middleware' => ['cors', 'auth:api']], function () use ($router) {

    });
});

$router->optional('*', function () {
    return response();
});
