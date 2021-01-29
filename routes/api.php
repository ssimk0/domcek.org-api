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

use App\Http\Controllers\Unsecure\NewsController;
use App\Http\Controllers\Unsecure\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware([])->group(function () {
    // AUTH
    Route::group(
        ['prefix' => 'auth'],
        function () {
            Route::post('login/', ['AuthController', 'authenticate']);
            Route::post('forgot-password/', ['AuthController', 'forgotPassword']);
            Route::post('reset-password/', ['AuthController', 'resetPassword']);
            Route::get('logout/', ['AuthController', 'logout']);
            Route::get('refresh-token/', ['AuthController', 'refresh']);
            Route::post('register-user', ['AuthController', 'registerUser']);
            Route::put('verify-email', ['AuthController', 'verifyEmail']);
            Route::post('verify-email', ['AuthController', 'sendVerificationEmail']);
        }
    );
    // PAGE UNSECURE
    Route::group(
        ['prefix' => '/'],
        function () {
            Route::post('media/upload', ['App\Http\Controllers\Unsecure\MediaController', 'upload']);
            Route::delete('media/upload', ['App\Http\Controllers\Unsecure\MediaController', 'delete']);
            Route::get('pages', [PageController::class, 'menuPages']);
            Route::get('pages/{slug}', ['App\Http\Controllers\Unsecure\PageController', 'page']);
            Route::get('news', [NewsController::class, 'list']);
            Route::get('news/{slug}', [
                'App\Http\Controllers\Unsecure\NewsController', 'news',
            ])->name('news.detail');
            Route::get('slider-images', ['App\Http\Controllers\Unsecure\SliderImagesController', 'list']);
            Route::get('events', ['App\Http\Controllers\Secure\EventController', 'availableEvents']);
        }
    );

    // REGISTRATION
    Route::group(['prefix' => '/registration', 'middleware' => ['cors', 'token_auth:registration']], function () {
        Route::get('events/participants/all-details/sync', ['App\Http\Controllers\Secure\ParticipantController', 'detailedRegistrationList']);
        Route::get('events/participants/sync', ['App\Http\Controllers\Secure\ParticipantController', 'registrationList']);
        Route::put('events/participants/sync', ['App\Http\Controllers\Secure\ParticipantController', 'sync']);
        Route::post('backup', ['App\Http\Controllers\Secure\BackupController', 'upload']);
    });

    // PAGE SECURE
    Route::group(
        ['prefix' => 'secure', 'middleware' => ['auth:api']],
        function () {

            // EDITOR
            Route::group(['prefix' => '/', 'middleware' => 'perm:editor'], function () {
                Route::get('news', ['App\Http\Controllers\Secure\NewsController', 'listUnpublished']);
                Route::post('news', ['App\Http\Controllers\Secure\NewsController', 'create']);
                Route::put('news/{slug}', ['App\Http\Controllers\Secure\NewsController', 'edit']);
                Route::get('news/{slug}', ['App\Http\Controllers\Secure\NewsController', 'unpublished']);

                Route::post('pages', ['App\Http\Controllers\Secure\PageController', 'create']);
                Route::put('pages/{slug}', ['App\Http\Controllers\Secure\PageController', 'edit']);
                Route::get('pages/{slug}', ['App\Http\Controllers\Secure\PageController', 'detail']);

                Route::post('slider-images', ['App\Http\Controllers\Secure\SliderImagesController', 'create']);
                Route::get('slider-images', ['App\Http\Controllers\Secure\SliderImagesController', 'list']);
                Route::put('slider-images/{id}', ['App\Http\Controllers\Secure\SliderImagesController', 'edit']);
                Route::delete('slider-images/{id}', ['App\Http\Controllers\Secure\SliderImagesController', 'delete']);
            });

            // ADMIN
            Route::middleware('perm:admin')->prefix('/admin')->group(function () {
                Route::post('events', ['App\Http\Controllers\Secure\EventController', 'create']);
                Route::get('events', ['App\Http\Controllers\Secure\EventController', 'list']);
                Route::put('events/{id}', ['App\Http\Controllers\Secure\EventController', 'edit']);
                Route::delete('events/{id}', ['App\Http\Controllers\Secure\EventController', 'delete']);
                Route::get('events/{id}', ['App\Http\Controllers\Secure\EventController', 'detail']);
                Route::get('events/{eventId}/volunteers', ['App\Http\Controllers\Secure\VolunteerController', 'list']);
                Route::post('events/{eventId}/payments', ['App\Http\Controllers\Secure\PaymentController', 'uploadTransferLog']);
                Route::post('events/{eventId}/nameplates', ['App\Http\Controllers\Secure\ParticipantController', 'generateNameplates']);
                Route::get('events/{eventId}/stats', ['App\Http\Controllers\Secure\EventController', 'statsFile']);

                Route::put('volunteers/{id}', ['App\Http\Controllers\Secure\VolunteerController', 'edit']);
                Route::get('volunteers/{id}', ['App\Http\Controllers\Secure\VolunteerController', 'detail']);

                Route::put('volunteers/{id}', ['App\Http\Controllers\Secure\VolunteerController', 'edit']);
                Route::get('volunteers/{id}', ['App\Http\Controllers\Secure\VolunteerController', 'detail']);

                Route::get('events/{eventId}/participants', ['App\Http\Controllers\Secure\ParticipantController', 'list']);
                Route::get('events/{eventId}/groups', ['App\Http\Controllers\Secure\GroupController', 'eventGroups']);
                Route::put('events/{eventId}/groups', ['App\Http\Controllers\Secure\GroupController', 'generateGroups']);
                Route::put('events/{eventId}/groups/animator', ['App\Http\Controllers\Secure\GroupController', 'AssignAnimator']);
                Route::put('events/{eventId}/participants/{participantId}', ['App\Http\Controllers\Secure\ParticipantController', 'edit']);
                Route::get('events/{eventId}/participants/{userId}', ['App\Http\Controllers\Secure\ParticipantController', 'adminDetail']);
                Route::put('events/{eventId}/participants/{userId}/unsubscribe', ['App\Http\Controllers\Secure\ParticipantController', 'adminUnsubscribe']);
                Route::put('events/{eventId}/participants/{userId}/subscribe', ['App\Http\Controllers\Secure\ParticipantController', 'adminSubscribe']);

                Route::get('users', ['App\Http\Controllers\Secure\UserController', 'list']);
                Route::put('users/{userId}', ['App\Http\Controllers\Secure\UserController', 'editUserAdmin']);
                Route::get('users/{userId}', ['App\Http\Controllers\Secure\UserController', 'adminUserDetail']);
                Route::put('users/{userId}/reset-password', ['App\Http\Controllers\Secure\UserController', 'resetPassword']);
            });

            // USER
            Route::get('user', ['App\Http\Controllers\Secure\UserController', 'userDetail']);
            Route::put('user', ['App\Http\Controllers\Secure\UserController', 'updateProfile']);
            Route::get('volunteers-types', ['App\Http\Controllers\Secure\VolunteerController', 'types']);

            Route::put('user/change-password', ['App\Http\Controllers\Secure\UserController', 'changePassword']);
            Route::get('user/events', ['App\Http\Controllers\Secure\ParticipantController', 'userEvents']);
            Route::post('user/events/{eventId}', ['App\Http\Controllers\Secure\ParticipantController', 'register']);
            Route::put('user/events/{eventId}', ['App\Http\Controllers\Secure\ParticipantController', 'userEdit']);
            Route::put('user/events/{eventId}/unsubscribe', ['App\Http\Controllers\Secure\ParticipantController', 'unsubscribe']);
            Route::put('user/events/{eventId}/subscribe', ['App\Http\Controllers\Secure\ParticipantController', 'subscribe']);
            Route::get('user/events/{eventId}/qr', ['App\Http\Controllers\Secure\ParticipantController', 'eventQRCode']);
        }
    );
});
