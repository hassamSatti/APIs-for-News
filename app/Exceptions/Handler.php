<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;
use App\Traits\ApiResponseTrait;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    
    public function render($request, Throwable $exception)
    { 
        if ($request->is('api/*')) {
             
            if ($exception instanceof ValidationException) {
                return $this->errorResponse(
                    'Validation failed',
                    422,
                    $exception->errors()
                );
            }
 
            if ($exception instanceof NotFoundHttpException) {
                return $this->errorResponse(
                    'Not Found',
                    404
                );
            }
 
            if ($exception instanceof AuthenticationException) {
                return $this->errorResponse(
                    'Invalid or expired token. Please log in again.',
                    401
                );
            }
 
            if ($exception instanceof AccessDeniedHttpException) {
                return $this->errorResponse(
                    'Access denied',
                    403
                );
            } 
 
            if ($exception instanceof \Swift_TransportException) {
                return $this->errorResponse(
                    'Failed to send the email. Please check the email address or the mail server configuration.',
                    503,
                    ['error' => $exception->getMessage()]
                );
            }
            return $this->errorResponse(
                'Internal Server Error',
                500,
                ['error' => $exception->getMessage()]
            );
        }

        return parent::render($request, $exception);
    }

}
