<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HttpResponses
{
    public function success(
        $data,
        $statusCode = 200,
        $error = null,
        $withCount = false
    ): JsonResponse {
        return response()->json(
            [
                'status' => 'Success!',
                'results' => $withCount ? $data->count() : ($data ? 1 : 0),
                'data' => $data,
                'error' => $error
            ],
            $statusCode
        );
    }

    public function failure(
        $error,
        $statusCode = 404,
        $data = null
    ): JsonResponse {
        return response()->json(
            [
                'status' => 'Unsuccessful!',
                'data' => $data,
                'error' => $error
            ],
            $statusCode
        );
    }
}
