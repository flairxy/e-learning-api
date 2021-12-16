<?php

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

$app->withFacades();
$app->withFacades(true, [
    "Illuminate\Support\Facades\Notification" => "Notification",
]);
$app->alias("mailer", \Illuminate\Contracts\Mail\Mailer::class);

$app->withEloquent();
/*
 * Allow overriding the storage path in production using an environment variable.
 */
$app->useStoragePath($_ENV['APP_STORAGE'] ?? $app->storagePath());
$app->configure('app');
$app->configure('view');
$app->configure('mail');
$app->configure('database');
$app->configure('filesystems');
$app->configure('logging');
$app->configure('swagger-lume');

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

$app->middleware([
    App\Http\Middleware\TrustProxies::class,
//    App\Http\Middleware\CloudfrontProxies::class,
    App\Http\Middleware\ExampleMiddleware::class,
    App\Http\Middleware\CorsMiddleware::class
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);
$app->register(\Illuminate\Mail\MailServiceProvider::class);
$app->register(Aws\Laravel\AwsServiceProvider::class);
$app->register(\SwaggerLume\ServiceProvider::class);
$app->register(Illuminate\Notifications\NotificationServiceProvider::class);

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
