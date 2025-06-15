<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            'prefix' => 'api',
            'file' => __DIR__.'/../routes/api.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('migration')
                ->group(base_path('routes/migrations.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->group('api', [
        //     \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
        //     \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
            
        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            $retryAfter = $e->getHeaders()['Retry-After'] ?? null;
            $rateLimitLimit = $e->getHeaders()['X-RateLimit-Limit'] ?? null;
            
            // For API requests (JSON expected)
            if ($request->expectsJson() || $request->is('api/*')) {

                return jsonResponse(429, 
                'You have exceeded the allowed number of requests. Please slow down and try again later.',
                    [
                        'Retry-After' => $retryAfter,
                        'X-RateLimit-Limit' => $rateLimitLimit,
                        'X-RateLimit-Remaining' => 0,
                        'X-RateLimit-Reset' => now()->addSeconds($retryAfter ?? 60)->timestamp,
                    ]
                );
            }

            // For web requests, redirect back with error message
            return redirect()->back()
                ->withErrors([
                    'throttle' => 'Too many requests!'
                ])
                ->withInput()
                ->with('throttle_error', true);
        });
        
    })->create();

    