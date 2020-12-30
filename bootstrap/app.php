<?php

use Laravel\Lumen\Http\ResponseFactory;

require_once __DIR__ . '/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');
$app->instance('path.storage', app()->basePath() . DIRECTORY_SEPARATOR . 'storage');
$app->configure('auth');
$app->configure('logging');
$app->configure('database');
$app->configure('sluggable');
$app->configure('services');
$app->configure('mail');
$app->configure('app');
$app->configure('filesystems');
$app->configure('queue');
$app->configure('snappy');


$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton('Illuminate\Contracts\Routing\ResponseFactory', function ($app)
{
    return new ResponseFactory($app['Illuminate\Contracts\View\Factory'], $app['Illuminate\Routing\Redirector']);
});

$app->singleton(
    Illuminate\Contracts\Filesystem\Factory::class,
    function ($app) {
        return new Illuminate\Filesystem\FilesystemManager($app);
    }
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'cors' => App\Http\Middleware\Cors::class,
    'perm' => App\Http\Middleware\Permission::class,
    'token_auth' => App\Http\Middleware\TokenAuth::class,
    'optimizeImages' => \Spatie\LaravelImageOptimizer\Middlewares\OptimizeImages::class
]);


/*
|--------------------------------------------------------------------------
| Register Services Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Services providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(App\Providers\AwsS3ServiceProvider::class);
$app->register(Sentry\Laravel\ServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(App\Providers\CatchAllOptionsRequestsProvider::class);
$app->register(AlbertCht\InvisibleReCaptcha\InvisibleReCaptchaServiceProvider::class);
$app->register(\Illuminate\Queue\QueueServiceProvider::class);
$app->register(Intervention\Image\ImageServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
//class_alias('Barryvdh\Snappy\Facades\SnappyPdf', 'PDF');
$app->register(Barryvdh\Snappy\LumenServiceProvider::class);
$app->alias('mail.manager', Illuminate\Mail\MailManager::class);
$app->alias('mail.manager', Illuminate\Contracts\Mail\Factory::class);

$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);
$app->alias('Image', Intervention\Image\Facades\Image::class);

if(!class_exists('PDF')) {
    class_alias(Barryvdh\Snappy\Facades\SnappyPdf::class, 'PDF');
    class_alias(Barryvdh\Snappy\Facades\SnappyImage::class, 'SnappyImage');
}

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

return $app;
