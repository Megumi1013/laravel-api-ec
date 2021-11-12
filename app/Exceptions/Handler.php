<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
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

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     * @return \Illuminate\Http\Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof UnauthorizedHttpException) {
            $preException = $e->getPrevious();

            if (request()->expectsJson()) {
                if ($preException instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                    $response = response()->json(['code' => 403, 'message' => __('api.exception_token_expired'), 'status' => 'exception_token_expired'], 403);
                } elseif ($preException instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                    $response = response()->json(['code' => 403, 'message' => __('api.exception_token_invalid'), 'status' => 'exception_token_invalid'], 403);
                } elseif ($preException instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException) {
                    $response = response()->json(['code' => 403, 'message' => __('api.exception_token_blacklisted'), 'status' => 'exception_token_blacklisted'], 403);
                }

                if ($e->getMessage() === 'Token not provided') {
                    $response = response()->json(['code' => 400, 'message' => __('api.exception_token_not_provided'), 'status' => 'exception_token_not_provided'], 400);
                }
            }
        } elseif (method_exists($e, 'getStatusCode') && request()->expectsJson()) {
            switch ($e->getStatusCode()) {
                case 404:
                    $response = response()->json(['code' => 404, 'message' => __('api.exception_content_not_found'), 'status' => 'exception_content_not_found'], 404);
                    break;
                case 429:
                    $response = response()->json(['code' => 429, 'message' => __('api.exception_api_limit_reached'), 'status' => 'exception_api_limit_reached'], 429);
                    break;
                case 500:
                    Log::error('Exception Handler: 500 Error '.$e->getMessage());
                    $response = response()->json(['code' => 500, 'message' => __('api.exception_unknown_error'), 'status' => 'exception_unknown_error'], 500);
                    break;

                default:

                    if ($e->getMessage() === 'The token has been blacklisted') {
                        $response = response()->json(['code' => 403, 'message' => __('api.exception_token_blacklisted'), 'status' => 'exception_token_blacklisted'], 403);
                    } else {
                        $response = response()->json(['code' => 500, 'message' => __('api.exception_unknown_error'), 'status' => 'exception_unknown_error'], 500);
                    }

                    Log::error('Exception Handler: Unknown Error Code ('.$e->getStatusCode().') '.$e->getMessage());
                    break;
            }
        }

        if (isset($response)) {
            app('Asm89\Stack\CorsService')->addActualRequestHeaders($response, $request);
        } else {
            $response = parent::render($request, $e);
        }

        return $response;
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['code' => 401, 'message' => __('api.not_authenticated'), 'status' => 'not_authenticated'], 401);
        }

        return redirect()->guest(route('/'));
    }
}
