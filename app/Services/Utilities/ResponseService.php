<?php

namespace App\Services\Utilities;

use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseService
{

    public static function success(string $message = '', $data = [], int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => [],
        ], $statusCode);
    }

    public static function created(string $message = '', $data = [], int $statusCode = 201): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => [],
        ], $statusCode);
    }

    public static function error(string $message = '', array $errors = [], int $statusCode = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $statusCode);
    }

    public static function notFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __('The requested resource was not found.'),
            'data' => null,
            'errors' => [
                'route' => [__('The requested route does not exist.')],
            ],
        ], 404);
    }

    public static function unauthenticated(string $message = '', array $errors = [], int $statusCode = 401): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $statusCode);
    }

    public static function unauthorized(string $message = '', array $errors = [], int $statusCode = 403): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $statusCode);
    }

    public static function failedValidationResponse($errors = null, string $message = 'Validation errors'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => [],
            'errors' => $errors ?? [],
        ], 422);
    }
}
