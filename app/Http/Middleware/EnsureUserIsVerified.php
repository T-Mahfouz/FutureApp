<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    /**
     * Handle an incoming request.
     * Block unverified users from accessing protected routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('api');

        if ($user && !$user->is_verified) {
            return response()->json([
                'status' => 403,
                'message' => 'Your account is not verified. Please verify your phone number first.',
            ], 403);
        }

        return $next($request);
    }
}
