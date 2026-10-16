<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Build a standard JSON success response.
     */
    protected function successResponse(
        mixed $data = [],
        string $message = 'Content fetched successfully',
        int $statusCode = 200,
        array $extra = []
    ): JsonResponse {
        $response = array_merge([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $extra);

        return response()->json($response, $statusCode);
    }

    /**
     * Build a standard JSON paginated response.
     */
    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        mixed $resourceCollection,
        string $message = 'Content fetched successfully',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resourceCollection,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], $statusCode);
    }

    /**
     * Build a standard JSON error response.
     */
    protected function errorResponse(
        string $message = 'An error occurred',
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
