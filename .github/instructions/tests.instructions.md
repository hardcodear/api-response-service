---
description: "Use when writing, editing or reviewing tests for api-response-service. Covers PHPUnit setup, test structure, assertion patterns for JsonResponse, and fixture conventions."
applyTo: "tests/**"
---

# Convenciones de Tests — api-response-service

## Setup de PHPUnit

Añadir al `composer.json` si no existe:

```json
"require-dev": {
    "orchestra/testbench": "^8.0|^9.0|^10.0",
    "phpunit/phpunit": "^10.0|^11.0"
},
"scripts": {
    "test": "vendor/bin/phpunit"
}
```

Ejecutar tests:

```bash
composer test
# o directamente:
vendor/bin/phpunit
```

## Estructura de Archivos

```
tests/
  Unit/
    ApiResponseServiceTest.php       # Métodos de respuesta de ApiResponseService
    ExceptionApiRegistrarTest.php # Binding de excepciones HTTP
  Feature/
    ApiResponseIntegrationTest.php # Integración con rutas /api/*
  TestCase.php                    # Base con bootstrapping de Orchestra Testbench
```

## TestCase Base

```php
namespace Hardcodear\ApiResponseService\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Hardcodear\ApiResponseService\Providers\ApiResponseServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [ApiResponseServiceProvider::class];
    }
}
```

## Patrones de Aserción para JsonResponse

Siempre verificar los tres niveles: **status HTTP**, **clave `status` del JSON** y **contenido**:

```php
use Illuminate\Http\JsonResponse;

$response = $this->service->success('OK', ['id' => 1]);

// 1. Tipo de retorno
$this->assertInstanceOf(JsonResponse::class, $response);

// 2. Código HTTP real
$this->assertSame(200, $response->getStatusCode());

// 3. Payload JSON
$data = $response->getData(true); // true → array asociativo
$this->assertSame(200, $data['status']);
$this->assertSame('OK', $data['message']);
$this->assertArrayHasKey('data', $data);
$this->assertArrayNotHasKey('errors', $data);
```

## Casos a Cubrir por Método

| Método              | Verificar                                       |
| ------------------- | ----------------------------------------------- |
| `success()`         | status=200, `data` presente, `errors` ausente   |
| `error()`           | status=500, `errors` presente, `data` ausente   |
| `notFound()`        | status=404                                      |
| `validation()`      | status=422, `errors` es array con los mensajes  |
| `forbidden()`       | status=403                                      |
| `unauthorized()`    | status=401                                      |
| `serverError()`     | status=500                                      |
| `successResponse()` | status HTTP personalizado, `data` condicional   |
| `errorResponse()`   | status HTTP personalizado, `errors` condicional |

## Reglas

- Un `it()` / método de test por comportamiento, no por método.
- Usar `setUp()` para instanciar `ApiResponseService` — no instanciar en cada test.
- No mockear `ApiResponseService` en sus propios tests unitarios — probar la clase real.
- Los campos `data` y `errors` **no deben aparecer** cuando se pasan como `null`.
- Probar tanto arrays indexados como asociativos en `$data` y `$errors`.
