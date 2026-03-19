<?php

namespace Hardcodear\ApiResponseService;

use Illuminate\Http\JsonResponse;

class ApiResponseService
{

    //use Macroable;
    public const HTTP_OK = 200;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    public const HTTP_INTERNAL_SERVER_ERROR = 500;
    public const HTTP_TOO_MANY_REQUESTS = 429;
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    public const HTTP_SERVICE_UNAVAILABLE = 503;

    public function successResponse(int $status = self::HTTP_OK, ?string $message = null, mixed $data = null): JsonResponse
    {
        $json = [
            'status'  => $status,
            'message' => $message
        ];
        if ($data !== null) {
            $json['data'] = $data;
        }
        return response()->json($json, $status);
    }

    public function errorResponse(int $status = self::HTTP_INTERNAL_SERVER_ERROR, ?string $message = null, mixed $errors = null): JsonResponse
    {
        if (is_object($errors)) {
            $errors = [$errors];
        }
        if (is_array($errors) && $this->isAssociativeArray($errors)) {
            $errors = [$errors];
        }
        $json = [
            'status'  => $status,
            'message' => $message,
        ];

        if ($errors !== null) {
            $json['errors'] = $errors;
        }
        return response()->json($json, $status);
    }

    public function success(?string $message = null, mixed $data = null): JsonResponse
    {
        return $this->successResponse(self::HTTP_OK, $message, $data);
    }

    public function error(?string $message = null, mixed $errors = null): JsonResponse
    {
        return $this->errorResponse(self::HTTP_INTERNAL_SERVER_ERROR, $message, $errors);
    }

    public function notFound(?string $message = null, mixed $errors = null): JsonResponse
    {
        return $this->errorResponse(self::HTTP_NOT_FOUND, $message, $errors);
    }

    public function validation(?string $message = null, mixed $errors = null): JsonResponse
    {
        return $this->errorResponse(self::HTTP_UNPROCESSABLE_ENTITY, $message, $errors);
    }

    public function forbidden(?string $message = null, mixed $errors = null): JsonResponse
    {
        return $this->errorResponse(self::HTTP_FORBIDDEN, $message, $errors);
    }

    public function unauthorized(?string $message = null, mixed $errors = null): JsonResponse
    {
        return $this->errorResponse(self::HTTP_UNAUTHORIZED, $message, $errors);
    }

    public function serverError(?string $message = null, mixed $errors = null): JsonResponse
    {
        return $this->errorResponse(self::HTTP_INTERNAL_SERVER_ERROR, $message, $errors);
    }
    // Método auxiliar para determinar si un array es asociativo
    private function isAssociativeArray(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
