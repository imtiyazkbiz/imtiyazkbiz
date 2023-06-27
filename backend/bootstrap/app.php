<?php

require_once __DIR__.'/../vendor/autoload.php';

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

$app->withEloquent();
//$app->configure('cors');
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

$app->singleton('filesystem', function ($app) { 
    return $app->loadComponent('filesystems', 'Illuminate\Filesystem\FilesystemServiceProvider', 'filesystem');
 });
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
$app->configure('auth');

$app->middleware([
    'Nord\Lumen\Cors\CorsMiddleware',
    App\Http\Middleware\Cors::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'client' => \Laravel\Passport\Http\Middleware\CheckClientCredentials::class,
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
//$app->register(App\Providers\CatchAllOptionsRequestsProvider::class);
// $app->register(Spatie\Cors\CorsServiceProvider::class);
$app->register(Intervention\Image\ImageServiceProvider::class);
$app->register('Nord\Lumen\Cors\CorsServiceProvider');
$app->register(Maatwebsite\Excel\ExcelServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(ZanySoft\Zip\ZipServiceProvider::class);
//$app->withFacades(true, ['Maatwebsite\Excel\Facades\Excel' => 'Excel']);
$app->alias('Excel', Maatwebsite\Excel\Facades\Excel::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Barryvdh\DomPDF\ServiceProvider::class);
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);
$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
Dusterio\LumenPassport\LumenPassport::routes($app->router, ['prefix' => 'api/v1/oauth'] );
$app->configure('dompdf');
//$app->alias('PDF', Barryvdh\DomPDF\Facade::class);

$app->configure('mail');
$app->configure('constant');
$app->configure('wl');
$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);
$app->alias('Zip', ZanySoft\Zip\ZipFacade::class);
$app->alias('Image', Intervention\Image\Facades\Image::class);
// $app->register(App\Providers\EventServiceProvider::class);




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
