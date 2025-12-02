<?php

use App\Http\Middleware\InvalidateSessionMiddleware;
use App\Services\Utilities\ResponseService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'guest' => InvalidateSessionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return ResponseService::notFound();
            }
        });

        $exceptions->renderable(function ( AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return ResponseService::unauthenticated(
                    message: __('Unauthenticated.'),
                    errors: ['authentication' => [__('Invalid credentials. Please try again.')]]
                );
            }
        });

        $exceptions->renderable(function (AccessDeniedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return ResponseService::unauthorized(
                    message: __('You do not have permission to access this resource.'),
                );
            }
        });

        $exceptions->renderable(function (ValidationException $e, $request) {
            return ResponseService::failedValidationResponse(
                message: __('The given data was invalid.'),
                errors: $e->errors()
            );
            
        });
    })->create();
