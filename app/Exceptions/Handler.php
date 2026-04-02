<?php

namespace App\Exceptions;

use App\Http\Traits\Responser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use Responser;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            // convert validation errors to json response
            if ($e instanceof ValidationException) {
                $errors = $e->validator->errors()->first();
                return $this->errorMessage($errors, 422);
                // $errors = $e->errors();
                // return $this->errorResponse($errors, 422);
            }

            // when model nonexistent
            if ($e instanceof ModelNotFoundException) {
                $modelName = strtolower(class_basename($e->getModel()));
                return $this->errorMessage('Does not exists any' . $modelName . 'with the spicified identificator', 404);
            }

            if ($e instanceof AuthenticationException) {
                return $this->errorMessage('Unauthenticated', 401);
            }

            if ($e instanceof AuthorizationException) {
                return $this->errorMessage($e->getMessage(), 403);
            }

            // when write nonexistent URL
            if ($e instanceof NotFoundHttpException) {
                return $this->errorMessage('The specified URL connot be found.', 404);
            }

            // when try excepted resource api methods or nonexistent method
            if ($e instanceof MethodNotAllowedHttpException) {
                return $this->errorMessage('The specified methode for the request is invaild.', 404);
            }

            // general http exception
            if ($e instanceof HttpException) {
                return $this->errorMessage($e->getMessage(), $e->getStatusCode());
            }

            // when have error in query like delete an instance which has a relation with other models
            if ($e instanceof QueryException) {
                $errorCode = $e->errorInfo[1];
                if ($errorCode == 1451) {
                    return $this->errorMessage('Cannot remove this resource permanently. It is related with any other resource', 409);
                }
            }

            // return $this->errorMessage('Unexpected Error. Try later.', 500);
        }

        // if we turn on the debugbar
        if (config('app.debug')) {
            return parent::render($request, $e);
        }
    }
}
