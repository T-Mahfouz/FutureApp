<?php

namespace App\Exceptions;

use Exception;
use Request;
use Throwable;

class CustomHandler extends Exception
{
    public function render(Request $request, Throwable $exception)
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
