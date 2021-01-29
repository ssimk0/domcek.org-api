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
    // AUTH
    $router->group(
        ['prefix' => 'auth', 'middleware' => 'cors'],
        function () use ($router) {
            $router->post('login/', 'Auth\AuthController@authenticate');
            $router->post('forgot-password/', 'Auth\AuthController@forgotPassword');
            $router->post('reset-password/', 'Auth\AuthController@resetPassword');
            $router->get('logout/', 'Auth\AuthController@logout');
            $router->get('refresh-token/', 'Auth\AuthController@refresh');
            $router->post('register-user', 'Auth\AuthController@registerUser');
            $router->put('verify-email', 'Auth\AuthController@verifyEmail');
            $router->post('verify-email', 'Auth\AuthController@sendVerificationEmail');
        }
    );
    // PAGE UNSECURE
    $router->group(
        ['prefix' => '/', 'middleware' => 'cors'],
        function () use ($router) {
            $router->post('media/upload', 'Unsecure\MediaController@upload');
            $router->delete('media/upload', 'Unsecure\MediaController@delete');
            $router->get('pages', 'Unsecure\PageController@menuPages');
            $router->get('pages/{slug}', 'Unsecure\PageController@page');
            $router->get('news', 'Unsecure\NewsController@list');
            $router->get('news/{slug}', [
            'uses' => 'Unsecure\NewsController@news',
            'as' => 'news.detail',
        ]);
            $router->get('slider-images', 'Unsecure\SliderImagesController@list');
            $router->get('events', 'Secure\EventController@availableEvents');
        }
    );

    // REGISTRATION
    $router->group(['prefix' => '/registration', 'middleware' => ['cors', 'token_auth:registration']], function () use ($router) {
        $router->get('events/participants/all-details/sync', 'Secure\ParticipantController@detailedRegistrationList');
        $router->get('events/participants/sync', 'Secure\ParticipantController@registrationList');
        $router->put('events/participants/sync', 'Secure\ParticipantController@sync');
        $router->post('backup', 'Secure\BackupController@upload');
    });

    // PAGE SECURE
    $router->group(
        ['prefix' => 'secure', 'middleware' => ['cors', 'auth:api']],
        function () use ($router) {

            // EDITOR
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

            // ADMIN
            $router->group(['prefix' => '/admin', 'middleware' => 'perm:admin'], function () use ($router) {
                $router->post('events', 'Secure\EventController@create');
                $router->get('events', 'Secure\EventController@list');
                $router->put('events/{id}', 'Secure\EventController@edit');
                $router->delete('events/{id}', 'Secure\EventController@delete');
                $router->get('events/{id}', 'Secure\EventController@detail');
                $router->get('events/{eventId}/volunteers', 'Secure\VolunteerController@list');
                $router->post('events/{eventId}/payments', 'Secure\PaymentController@uploadTransferLog');
                $router->post('events/{eventId}/nameplates', 'Secure\ParticipantController@generateNameplates');
                $router->get('events/{eventId}/stats', 'Secure\EventController@statsFile');

                $router->put('volunteers/{id}', 'Secure\VolunteerController@edit');
                $router->get('volunteers/{id}', 'Secure\VolunteerController@detail');

                $router->put('volunteers/{id}', 'Secure\VolunteerController@edit');
                $router->get('volunteers/{id}', 'Secure\VolunteerController@detail');

                $router->get('events/{eventId}/participants', 'Secure\ParticipantController@list');
                $router->get('events/{eventId}/groups', 'Secure\GroupController@eventGroups');
                $router->put('events/{eventId}/groups', 'Secure\GroupController@generateGroups');
                $router->put('events/{eventId}/groups/animator', 'Secure\GroupController@AssignAnimator');
                $router->put('events/{eventId}/participants/{participantId}', 'Secure\ParticipantController@edit');
                $router->get('events/{eventId}/participants/{userId}', 'Secure\ParticipantController@adminDetail');
                $router->put('events/{eventId}/participants/{userId}/unsubscribe', 'Secure\ParticipantController@adminUnsubscribe');
                $router->put('events/{eventId}/participants/{userId}/subscribe', 'Secure\ParticipantController@adminSubscribe');

                $router->get('users', 'Secure\UserController@list');
                $router->put('users/{userId}', 'Secure\UserController@editUserAdmin');
                $router->get('users/{userId}', 'Secure\UserController@adminUserDetail');
                $router->put('users/{userId}/reset-password', 'Secure\UserController@resetPassword');
            });

            // USER
            $router->get('user', 'Secure\UserController@userDetail');
            $router->put('user', 'Secure\UserController@updateProfile');
            $router->get('volunteers-types', 'Secure\VolunteerController@types');

            $router->put('user/change-password', 'Secure\UserController@changePassword');
            $router->get('user/events', 'Secure\ParticipantController@userEvents');
            $router->post('user/events/{eventId}', 'Secure\ParticipantController@register');
            $router->put('user/events/{eventId}', 'Secure\ParticipantController@userEdit');
            $router->put('user/events/{eventId}/unsubscribe', 'Secure\ParticipantController@unsubscribe');
            $router->put('user/events/{eventId}/subscribe', 'Secure\ParticipantController@subscribe');
            $router->get('user/events/{eventId}/qr', 'Secure\ParticipantController@eventQRCode');
        }
    );
});
