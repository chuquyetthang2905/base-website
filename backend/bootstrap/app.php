<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\RoleMiddleware;
use App\Providers\RepositoryServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        RepositoryServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Ensure every API request is always handled as JSON.
        // This prevents HTML error pages from reaching the Vue frontend.
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        // Register 'role' as a named middleware alias.
        // Allows route definitions like: Route::middleware('role:admin')
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /**
         * Centralized Exception Handler
         *
         * All exceptions thrown anywhere in the application are caught here
         * and transformed into our standard JSON response contract.
         * This prevents any raw HTML, stack traces, or inconsistent formats
         * from ever reaching the Vue frontend.
         */

        // 422 — Validation errors from FormRequest classes
        // Laravel throws this automatically when a FormRequest fails.
        // We reformat to our contract: { success, message, data, errors }
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'data'    => null,
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // 401 — Unauthenticated (missing or invalid token)
        // Laravel throws this when auth middleware rejects the request.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'data'    => null,
                    'errors'  => null,
                ], 401);
            }
        });

        // 404 — Route not found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested resource was not found',
                    'data'    => null,
                    'errors'  => null,
                ], 404);
            }
        });

        // 405 — Method not allowed (e.g. GET on a POST-only route)
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed',
                    'data'    => null,
                    'errors'  => null,
                ], 405);
            }
        });

        // Catch-all for any other HttpException (403, 429, 500, etc.)
        // HttpException carries the status code from whoever threw it.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'An error occurred',
                    'data'    => null,
                    'errors'  => null,
                ], $e->getStatusCode());
            }
        });

        // Catch-all for unexpected exceptions (runtime errors, etc.)
        // Never expose the real message in production — it may leak sensitive info.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => app()->isProduction() ? 'Server error' : $e->getMessage(),
                    'data'    => null,
                    'errors'  => null,
                ], 500);
            }
        });
    })->create();
