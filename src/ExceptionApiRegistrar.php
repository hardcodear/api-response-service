<?php

namespace Hardcodear\ApiResponseService;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ExceptionApiRegistrar
{
    public static function bind(Exceptions $exceptions): void
    {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (self::shouldHandle($request)) {
                if ($e instanceof ValidationException) {
                    return apiresponse()->validation(self::message('validation', 'Error de validacion'), $e->errors());
                }

                if ($e instanceof AccessDeniedHttpException) {
                    return apiresponse()->unauthorized(self::message('access_denied', 'No tiene permiso para ejecutar esta API'));
                }

                if ($e instanceof NotFoundHttpException) {
                    return apiresponse()->notFound(self::message('not_found', 'URL no encontrada'));
                }

                if ($e instanceof TooManyRequestsHttpException) {
                    return apiresponse()->errorResponse(ApiResponseService::HTTP_TOO_MANY_REQUESTS, self::message('too_many_requests', 'Demasiadas peticiones'));
                }

                if ($e instanceof RouteNotFoundException) {
                    return apiresponse()->unauthorized(self::message('route_not_found', 'Token invalido o no enviado'));
                }

                if ($e instanceof AuthenticationException) {
                    return apiresponse()->unauthorized(self::message('authentication', 'Token de autorizacion requerido o invalido'));
                }

                if ($e instanceof AuthorizationException) {
                    return apiresponse()->forbidden(self::message('authorization', 'No autorizado para este recurso'));
                }

                if ($e instanceof MethodNotAllowedHttpException) {
                    return apiresponse()->errorResponse(ApiResponseService::HTTP_METHOD_NOT_ALLOWED, self::message('method_not_allowed', 'Metodo HTTP no permitido para esta ruta'));
                }

                if ($e instanceof HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                    $message = $e->getMessage();

                    if ($status >= 500) {
                        return apiresponse()->serverError(self::message('server_error', 'Error interno del servidor'));
                    }

                    return apiresponse()->errorResponse(
                        $status,
                        $message !== '' ? $message : self::message('client_error', 'Error en la solicitud')
                    );
                }
            }

            return null;
        });
    }

    private static function shouldHandle(Request $request): bool
    {
        $patterns = config('apiresponse.api_patterns', ['api', 'api/*']);

        if (!is_array($patterns)) {
            return $request->is('api') || $request->is('api/*');
        }

        foreach ($patterns as $pattern) {
            if (is_string($pattern) && $request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    private static function message(string $key, string $default): string
    {
        $message = config("apiresponse.messages.$key");

        if (is_string($message) && $message !== '') {
            return $message;
        }

        return $default;
    }
}
