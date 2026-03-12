# ApiResponse — Instrucciones del Proyecto

Paquete Laravel para estandarizar respuestas JSON en APIs REST.
**Paquete**: `hardcodear/api-response-service` · **Namespace**: `Hardcodear\ApiResponseService` · **PHP 8.2+** · **Laravel 12+**

## Arquitectura

```
src/
  ApiResponseService.php          # Clase principal — genera JsonResponse
  ExceptionApiRegistrar.php    # Registrador de excepciones globales para rutas /api/*
  helpers.php                  # Función global apiresponse()
  Facades/ApiResponse.php         # Facade estática
  Providers/ApiResponseServiceProvider.php  # Registra singleton 'apiresponse'
```

- `ApiResponseService` es un singleton registrado bajo la clave `'apiresponse'`.
- `ExceptionApiRegistrar::bind()` se llama manualmente en `bootstrap/app.php` del proyecto consumidor.
- Auto-discovery habilitado vía `extra.laravel` en `composer.json`.
- PSR-4 autoload: `Hardcodear\ApiResponseService\` → `src/`

## Formato de Respuestas JSON

Todo método público retorna `JsonResponse` con esta estructura:

```json
// Éxito
{ "status": 200, "message": "...", "data": {} }

// Error
{ "status": 422, "message": "...", "errors": [] }
```

Los campos `data` y `errors` se omiten si son `null`.

## Convenciones de Código

- **Type hints completos**: `mixed`, `int`, `?string`, return types declarados en todos los métodos públicos.
- **Constantes para códigos HTTP**: Usar `self::HTTP_OK`, `self::HTTP_NOT_FOUND`, etc. — nunca números mágicos.
- **Métodos de conveniencia**: `success()`, `error()`, `notFound()`, `validation()`, `forbidden()`, `unauthorized()`, `serverError()` delegan a `successResponse()` / `errorResponse()`.
- **PSR-4 + PSR-12**: Indentación de 4 espacios, sin tabs.
- **Lógica interna privada**: Helpers como `isAssociativeArray()` son `private`.

## Instalación / Builds

```bash
# Instalar dependencias
composer install

# Añadir el paquete a un proyecto Laravel
composer require hardcodear/api-response-service
```

No hay suite de tests configurada actualmente. Al añadir tests, usar PHPUnit con `require-dev`.

## Excepciones Manejadas Automáticamente (rutas `/api/*`)

| Excepción                       | Código HTTP |
| ------------------------------- | ----------- |
| `AccessDeniedHttpException`     | 401         |
| `NotFoundHttpException`         | 404         |
| `TooManyRequestsHttpException`  | 429         |
| `RouteNotFoundException`        | 401         |
| `AuthenticationException`       | 401         |
| `MethodNotAllowedHttpException` | 405         |

## Patrones a Evitar

- No retornar arrays crudos en lugar de `JsonResponse`.
- No agregar lógica de negocio dentro del paquete — solo formateo de respuestas.
- No usar `response()->json()` directamente; usar los métodos del servicio.
