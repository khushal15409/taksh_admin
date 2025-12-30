<?php
ini_set('memory_limit',-1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Load additional route files
            Route::middleware('web')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/admin.php'));

            Route::prefix('vendor-panel')
                ->middleware('web')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/vendor.php'));

            Route::prefix('api/v1')
                ->middleware('api')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/api/v1/api.php'));

            Route::prefix('api/v2')
                ->middleware('api')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/api/v2/api.php'));

            Route::prefix('admin')
                ->middleware('web')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/admin/routes.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        $middleware->append(\App\Http\Middleware\PreventRequestsDuringMaintenance::class);
        $middleware->append(\App\Http\Middleware\TrimStrings::class);
        
        // Web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\Localization::class,
        ]);
        
        // API middleware group
        $middleware->api(prepend: [
            'throttle:api',
        ]);
        
        // Alias middleware
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'vendor' => \App\Http\Middleware\VendorMiddleware::class,
            'vendor.api' => \App\Http\Middleware\VendorTokenIsValid::class,
            'dm.api' => \App\Http\Middleware\DmTokenIsValid::class,
            'module' => \App\Http\Middleware\ModulePermissionMiddleware::class,
            'installation-check' => \App\Http\Middleware\InstallationMiddleware::class,
            'actch' => \App\Http\Middleware\ActivationCheckMiddleware::class,
            'localization' => \App\Http\Middleware\LocalizationMiddleware::class,
            'module-check' => \App\Http\Middleware\ModuleCheckMiddleware::class,
            'current-module' => \App\Http\Middleware\CurrentModule::class,
            'apiGuestCheck' => \App\Http\Middleware\APIGuestMiddleware::class,
            'subscription' => \App\Http\Middleware\Subscription::class,
            'admin-rental-module' => \App\Http\Middleware\AdminRentalModuleCheckMiddleware::class,
            'provider-rental-module' => \App\Http\Middleware\ProviderRentalModuleCheckMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
