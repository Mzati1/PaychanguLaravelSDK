<?php

namespace Mzati\PaychanguSDK\Exceptions;

use Exception;

class PaychanguException extends Exception
{
    /**
     * Create a new Paychangu exception instance.
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(\Illuminate\Http\Request $request): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => true,
                'message' => $this->getMessage(),
            ], $this->getCode() ?: 400);
        }

        return response()->view('errors.payment', [
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 400);
    }
}
