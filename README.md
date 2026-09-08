# Api Response Service

[![Version](https://img.shields.io/packagist/v/hardcodear/api-response-service)](https://packagist.org/packages/hardcodear/api-response-service)
[![Downloads](https://img.shields.io/packagist/dt/hardcodear/api-response-service)](https://packagist.org/packages/hardcodear/api-response-service)
[![PHP Version](https://img.shields.io/packagist/php-v/hardcodear/api-response-service)](https://packagist.org/packages/hardcodear/api-response-service)
[![License](https://img.shields.io/packagist/l/hardcodear/api-response-service)](https://packagist.org/packages/hardcodear/api-response-service)
[![CI](https://github.com/hardcodear/api-response-service/actions/workflows/tests.yml/badge.svg)](https://github.com/hardcodear/api-response-service/actions/workflows/tests.yml)

**Paquete Laravel 12 y 13 para formatear respuestas JSON de forma estandarizada.**

Este paquete proporciona una forma consistente de estructurar las respuestas JSON para APIs Laravel, siguiendo un formato uniforme para respuestas exitosas y de error.

---

## ✅ Compatibilidad

- PHP `8.2+` con Laravel 12
- PHP `8.3+` con Laravel 13
- Laravel `12` y `13`

---

## 📦 Instalación

### Instalar el paquete

En consola:

```bash
composer require hardcodear/api-response-service:^2.0
```

Laravel detectará automáticamente el `ServiceProvider` y registrará el alias del facade `apiresponse` gracias al archivo `composer.json` del paquete.

---

## ⚡ Quick Start

```php
<?php

use Illuminate\Http\Request;

class UserController
{
  public function index()
  {
    return apiresponse()->success('Listado de usuarios', [
      ['id' => 1, 'name' => 'Ana'],
      ['id' => 2, 'name' => 'Luis'],
    ]);
  }

  public function store(Request $request)
  {
    $errors = [];

    if (! $request->input('email')) {
      $errors['email'][] = 'El campo email es obligatorio.';
    }

    if ($errors !== []) {
      return apiresponse()->validation('Datos inválidos', $errors);
    }

    return apiresponse()->success('Usuario creado', ['id' => 123]);
  }
}
```

---

## 🧰 Funcionalidades disponibles

El paquete expone los siguientes métodos a través del helper `apiresponse()` (o facade `ApiResponse`):

### ✅ Respuestas exitosas

```php
apiresponse()->success(?string $message = null, mixed $data = null): JsonResponse
```

```php
return apiresponse()->success('Operación realizada con éxito', ['id' => 123]);
```

---

### 📭 Not Found (404)

```php
apiresponse()->notFound(?string $message = null, mixed $errors = null): JsonResponse
```

```php
return apiresponse()->notFound('Recurso no encontrado');
```

---

### 🛑 Validación fallida (422)

```php
apiresponse()->validation(?string $message = null, mixed $errors = null): JsonResponse
```

```php
return apiresponse()->validation('Datos inválidos', $validator->errors());
```

---

### 🔐 No autorizado (401)

```php
apiresponse()->unauthorized(?string $message = null, mixed $errors = null): JsonResponse
```

```php
return apiresponse()->unauthorized('Token inválido');
```

---

### 🚫 Prohibido (403)

```php
apiresponse()->forbidden(?string $message = null, mixed $errors = null): JsonResponse
```

```php
return apiresponse()->forbidden('Acceso denegado');
```

---

### 💥 Error del servidor (500)

```php
apiresponse()->serverError(?string $message = null, mixed $errors = null): JsonResponse
```

```php
return apiresponse()->serverError('Error inesperado');
```

---

### ❌ Errores personalizados

```php
apiresponse()->error(?string $message = null, mixed $errors = null): JsonResponse
```

```php
return apiresponse()->error('Error inesperado', ['detalle' => '...']);
```

---

## 🧪 Estructura del JSON resultante

### Éxito

```json
{
  "status": 200,
  "message": "Mensaje opcional",
  "data": {
    // contenido devuelto
  }
}
```

### Error

```json
{
  "status": 500,
  "message": "Mensaje de error",
  "errors": [
    // array de errores
  ]
}
```

> El campo `errors` puede ser un array plano o un array asociativo (por ejemplo, errores de validación).

Los arrays indexados se conservan sin cambios. Los arrays asociativos se envuelven como un elemento de `errors`, y un array vacío se mantiene como `[]`.

> Cuando `data` o `errors` son `null`, esas claves se omiten automáticamente del JSON.

---

## 📌 Manejo global de excepciones (opcional)

Si querés que tu API devuelva respuestas JSON uniformes ante errores comunes como rutas no encontradas, permisos o límites de peticiones, podés usar el **registrador de excepciones** incluido en este paquete.

Esto te permite centralizar el manejo de errores en `bootstrap/app.php`, sin repetir lógica en cada controlador.

---

### 🧱 Editar `bootstrap/app.php`

Agregá el binding dentro de `withExceptions(...)` en `bootstrap/app.php`:

```php
use Hardcodear\ApiResponseService\ExceptionApiRegistrar;
use Illuminate\Foundation\Configuration\Exceptions;

$app->withExceptions(function (Exceptions $exceptions) {
    ExceptionApiRegistrar::bind($exceptions);
});
```

### ⚙️ ¿Qué hace esto?

Intercepta excepciones comunes y devuelve respuestas formateadas como:

```json
{
  "status": 404,
  "message": "URL no encontrada"
}
```

Las excepciones manejadas por defecto son:

- AccessDeniedHttpException → 403 Forbidden
- NotFoundHttpException → 404 Not Found
- TooManyRequestsHttpException → 429 Too Many Requests
- RouteNotFoundException → 401 Unauthorized
- AuthenticationException → 401 Unauthorized
- AuthorizationException → 403 Forbidden
- MethodNotAllowedHttpException → 405 Method Not Allowed
- ValidationException → 422 Unprocessable Entity
- ServiceUnavailableHttpException → 503 Service Unavailable
- HttpExceptionInterface (fallback) → respeta el status HTTP en rutas API; los mensajes 5xx se reemplazan por un mensaje seguro

Los headers definidos por la excepción, como `Allow` o `Retry-After`, se conservan en la respuesta JSON.

### ⚙️ Configuración opcional

Si querés personalizar patrones de rutas y mensajes, publicá la configuración:

```bash
php artisan vendor:publish --tag=apiresponse-config
```

Archivo publicado: `config/apiresponse.php`

- `api_patterns`: patrones de ruta a interceptar (default: `['api', 'api/*']`)
- `messages`: mensajes por tipo de excepcion

---

## 🧪 Testing

Ejecutar la suite localmente:

```bash
composer test
```

El repositorio también ejecuta tests automáticamente en GitHub Actions para `push` y `pull_request`, validando Laravel `12` con PHP `8.2+` y Laravel `13` con PHP `8.3+` en combinaciones compatibles.

Matriz de versiones recientes:

| PHP   | Laravel |
| ----- | ------- |
| `8.2` | `12`    |
| `8.3` | `12`    |
| `8.3` | `13`    |
| `8.4` | `12`    |
| `8.4` | `13`    |
| `8.5` | `12`    |
| `8.5` | `13`    |

Además, se prueban las dependencias mínimas declaradas para Laravel 12/PHP 8.2 y Laravel 13/PHP 8.3.

---

## Migración a v2.0

- `AccessDeniedHttpException` ahora responde correctamente con 403 en lugar de 401.
- Las excepciones HTTP 5xx conservan su status original; su mensaje interno no se expone.
- Los headers HTTP de las excepciones se preservan.
- Un array vacío enviado como `errors` permanece como `[]`.
- La estructura y los nombres de las claves JSON no cambian.

---

## 🧑 Autor

Ricardo Bazán  
Argentina, 2026  
Repositorio: [https://github.com/hardcodear/api-response-service](https://github.com/hardcodear/api-response-service)

---

## 📄 Licencia

Este paquete está licenciado bajo la [MIT License](LICENSE.md).
