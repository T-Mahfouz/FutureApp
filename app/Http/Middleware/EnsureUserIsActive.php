<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     * Block users whose `blocked` flag is set from accessing protected routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('api');

        if ($user && $user->blocked) {
            return response()->json([
                'status' => 403,
                'message' => 'Your account has been blocked.',
                'reason'  => $user->block_reason,
            ], 403);
        }

        return $next($request);
    }
}
