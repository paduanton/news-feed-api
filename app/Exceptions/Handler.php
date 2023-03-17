<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function render($request, Throwable $exception)
    {

        if ($exception instanceof ModelNotFoundException) {
            $message = $exception->getMessage() ? $exception->getMessage() : 'There is no data';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'error' => 'Model not found in the server'
                ], 404);
            }

            return redirect('/v1/notfound');
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'message' => 'User unauthenticated',
            ], 401);
        }

        if ($exception instanceof NotFoundHttpException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Invalid route',
                ], 404);
            }

            return redirect('/v1/fallback');
        }

        return parent::render($request, $exception);
    }
}
