<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function createSuccessResponse($code, $message, $status, $data = null): \Illuminate\Http\JsonResponse
    {
        $status = $status ?: Str::slug($message, '_');

        return response()->json(['code' => $code, 'message' => $message, 'status' => $status, 'data' => $data], $code);
    }

    public function createErrorResponse($code, $message, $status, $data = null): \Illuminate\Http\JsonResponse
    {
        $status = $status ?: Str::slug($message, '_');

        return response()->json(['code' => $code, 'message' => $message, 'status' => $status, 'data' => $data], $code);
    }
}
