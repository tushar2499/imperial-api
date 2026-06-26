<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {

                if ($e instanceof UnauthorizedHttpException) {
                    return $this->unauthenticatedResponse($e->getMessage() ?: 'Unauthenticated');
                }

                if ($e instanceof TokenExpiredException) {
                    return $this->unauthenticatedResponse('Token has expired');
                }

                if ($e instanceof JWTException) {
                    return $this->unauthenticatedResponse('Token is invalid or could not be processed');
                }

                if ($e instanceof AuthenticationException) {
                    return $this->unauthenticatedResponse('Token is invalid or expired');
                }

                if ($e instanceof ValidationException) {
                    $errors = [];
                    foreach ($e->errors() as $field => $messages) {
                        $errors[$field] = $messages[0];
                    }

                    return $this->validationFailedResponse($errors, $e->getMessage());
                }

                if ($e instanceof ModelNotFoundException) {
                    return $this->notFoundResponse('The requested resource was not found');
                }

                if (
                    $e instanceof AuthorizationException
                    || $e instanceof AccessDeniedHttpException
                ) {
                    return $this->forbiddenResponse($e->getMessage());
                }

                if ($e instanceof NotFoundHttpException) {
                    $message = $e->getMessage() ?: 'The requested page was not found';

                    return $this->notFoundResponse($message);
                }

                return $this->somethingWentWrongResponse($e->getMessage());
            }
        });
    }
}
