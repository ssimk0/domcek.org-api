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

$router->group(['prefix' => env('API_PREFIX', '/'), 'middleware' => 'cors'], function () use ($router) {
    $router->group(
        ['prefix' => 'auth', 'middleware' => 'cors'],
        function () use ($router) {
            $router->post('login/', 'Auth\AuthController@authenticate');

            $router->post('forgot-password/', 'Auth\AuthController@forgotPassword');

            $router->post('reset-password/', 'Auth\AuthController@resetPassword');

            $router->get('logout/', 'Auth\AuthController@logout');
            $router->get('refresh-token/', 'Auth\AuthController@refresh');

            $router->post('register-user', 'Auth\AuthController@registerUser');
        });

    $router->group(
        ['prefix' => '/', 'middleware' => 'cors'], function () use ($router) {
        $router->group(['prefix' => '/', 'middleware' => 'optimizeImages'], function () use ($router) {
            $router->post('media/upload', 'Unsecure\MediaController@upload');
        });
        $router->get('pages', 'Unsecure\PageController@menuPages');
        $router->get('pages/{slug}', 'Unsecure\PageController@page');
        $router->get('news', 'Unsecure\NewsController@list');
        $router->get('news/{slug}', [
            'uses' => 'Unsecure\NewsController@news',
            'as' => 'news.detail'
        ]);
        $router->get('slider-images', 'Unsecure\SliderImagesController@list');
    });

    $router->group(
        ['prefix' => 'secure', 'middleware' => ['cors', 'auth:api']], function () use ($router) {

        $router->group(['prefix' => '/', 'middleware' => 'perm:editor'], function () use ($router) {
            $router->get('news', 'Secure\NewsController@listUnpublished');
            $router->post('news', 'Secure\NewsController@create');
            $router->put('news/{slug}', 'Secure\NewsController@edit');
            $router->get('news/{slug}', 'Secure\NewsController@unpublished');

            $router->post('pages', 'Secure\PageController@create');
            $router->put('pages/{slug}', 'Secure\PageController@edit');
            $router->get('pages/{slug}', 'Secure\PageController@detail');

            $router->post('slider-images', 'Secure\SliderImagesController@create');
            $router->get('slider-images', 'Secure\SliderImagesController@list');
            $router->put('slider-images/{id}', 'Secure\SliderImagesController@edit');
            $router->delete('slider-images/{id}', 'Secure\SliderImagesController@delete');
        });

        $router->group(['prefix' => '/admin', 'middleware' => 'perm:admin'], function () use ($router) {
            $router->post('event', 'Secure\EventController@create');
            $router->get('event', 'Secure\EventController@list');
            $router->put('event/{id}', 'Secure\EventController@edit');
            $router->delete('event/{id}', 'Secure\EventController@delete');
            $router->get('event/{id}', 'Secure\EventController@detail');
            $router->get('event/{eventId}/volunteer', 'Secure\VolunteerController@list');

            $router->get('volunteer/types', 'Secure\VolunteerController@types');
            $router->put('volunteer/{id}', 'Secure\VolunteerController@edit');
            $router->get('volunteer/{id}', 'Secure\VolunteerController@detail');
        });

        $router->get('user', 'Secure\UserController@userDetail');
        $router->put('user', 'Secure\UserController@updateProfile');
        $router->put('user/change-password', 'Secure\UserController@changePassword');
    });
});
