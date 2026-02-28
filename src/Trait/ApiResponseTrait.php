<?php

namespace App\Trait;

use Symfony\Component\HttpFoundation\JsonResponse;

trait ApiResponseTrait
{
    private function errorResponse(int $code, array|string $errors, mixed $data = null): JsonResponse
    {
        $errorList = is_array($errors) ? array_values($errors) : [$errors];

        $body = [
            'code' => $code,
            'errors' => $errorList,
        ];

        if ($data !== null) {
            $body['data'] = $data;
        }

        return new JsonResponse($body, $code);
    }

    private function successResponse(mixed $data, int $code = 200): JsonResponse
    {
        return new JsonResponse([
            'code' => $code,
            'data' => $data,
        ], $code);
    }
}
