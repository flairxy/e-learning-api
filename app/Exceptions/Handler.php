<?php

namespace App\Exceptions;

use App\Traits\HandlesResponse;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{

    use HandlesResponse;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        SuspiciousOperationException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Throwable $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $exception)
    {

        if ($exception instanceof SuspiciousOperationException) {
            return $this->error("Not found", Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
            $message = $exception->getMessage() ?: Response::$statusTexts[$code];
            return $this->error($message, $code);
        }

        if ($exception instanceof ModelNotFoundException) {
            $message = strtolower(class_basename($exception->getModel()));
            return $this->error("{$message} not found", Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->error($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->error($exception->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        if ($exception instanceof ValidationException) {
            $messages = $exception->validator->errors()->getMessages();
            return $this->error($messages, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (env('APP_DEBUG')) {
            return $this->error($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            return $this->error('Unexpected error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
