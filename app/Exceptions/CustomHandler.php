<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Throwable;

class CustomHandler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ThrottleRequestsException) {
            return response()->json([
                'status' => false,
                'message' => 'لقد قمت بعدد كبير من المحاولات. الرجاء المحاولة لاحقًا.',
                'retry_after' => $exception->getHeaders()['Retry-After'] ?? null,
            ], 429);
        }

        return parent::render($request, $exception);
    }
}
