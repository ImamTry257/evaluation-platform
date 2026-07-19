<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait HasApiResponse
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return a paginated list response.
     */
    protected function listResponse($data, string $message = 'Data retrieved successfully', $paginator = null)
    {
        // If $data is a ResourceCollection, extract items and get paginator from second param
        if ($data instanceof ResourceCollection && $paginator instanceof LengthAwarePaginator) {
            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => [
                    'contents' => $data->response()->getData(true)['data'],
                    'meta' => [
                        'page' => $paginator->currentPage(),
                        'limit' => $paginator->perPage(),
                        'total' => $paginator->total(),
                    ],
                ],
            ]);
        }

        // Default: $data is a LengthAwarePaginator
        if ($data instanceof LengthAwarePaginator) {
            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => [
                    'contents' => $data->items(),
                    'meta' => [
                        'page' => $data->currentPage(),
                        'limit' => $data->perPage(),
                        'total' => $data->total(),
                    ],
                ],
            ]);
        }

        // Fallback: just return data as-is
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(string $message = 'Error', int $code = 400, $errors = [])
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
