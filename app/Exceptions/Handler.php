<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        "current_password",
        "password",
        "password_confirmation",
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

        $this->renderable(function (NotFoundHttpException $e) {
            $previous = $e->getPrevious();
            if ($previous instanceof ModelNotFoundException) {
                return response()->json(
                    [
                        "message" => __("Record is not found"),
                        "data" => null,
                    ],
                    404
                );
            }
            return response()->json(
                [
                    "message" => __("Resource is not found"),
                    "data" => null,
                ],
                404
            );
        });

        $this->renderable(function (ValidationException $e) {
            return response()->json(
                [
                    "message" => __($e->validator->errors()->first()),
                    "data" => null,
                ],
                400
            );
        });
    }
}
