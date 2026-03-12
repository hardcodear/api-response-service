# Api Response Service

[![Version](https://img.shields.io/packagist/v/hardcodear/api-response-service)](https://packagist.org/packages/hardcodear/api-response-service)
[![Downloads](https://img.shields.io/packagist/dt/hardcodear/api-response-service)](https://packagist.org/packages/hardcodear/api-response-service)
[![PHP Version](https://img.shields.io/packagist/php-v/hardcodear/api-response-service)](https://packagist.org/packages/hardcodear/api-response-service)
[![License](https://img.shields.io/packagist/l/hardcodear/api-response-service)](https://packagist.org/packages/hardcodear/api-response-service)
[![CI](https://github.com/hardcodear/api-response-service/actions/workflows/tests.yml/badge.svg)](https://github.com/hardcodear/api-response-service/actions/workflows/tests.yml)

**Paquete Laravel 12+ para formatear respuestas JSON de forma estandarizada.**

Este paquete proporciona una forma consistente de estructurar las respuestas JSON para APIs Laravel, siguiendo un formato uniforme para respuestas exitosas y de error.

---

## ✅ Compatibilidad

- PHP `8.2+`
- Laravel `12+`

---

## 📦 Instalación

### Instalar el paquete

En consola:

```bash
composer require hardcodear/api-response-service
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
apiresponse()->success(string $mensaje = null, mixed $data = null)
```

```php
return apiresponse()->success('Operación realizada con éxito', ['id' => 123]);
```

---

### 📭 Not Found (404)

```php
apiresponse()->notFound(string $mensaje = null, mixed $errores = null)
```

```php
return apiresponse()->notFound('Recurso no encontrado');
```

---

### 🛑 Validación fallida (422)

```php
apiresponse()->validation(string $mensaje = null, mixed $errores = null)
```

```php
return apiresponse()->validation('Datos inválidos', $validator->errors());
```

---

### 🔐 No autorizado (401)

```php
apiresponse()->unauthorized(string $mensaje = null, mixed $errores = null)
```

```php
return apiresponse()->unauthorized('Token inválido');
```

---

### 🚫 Prohibido (403)

```php
apiresponse()->forbidden(string $mensaje = null, mixed $errores = null)
```

```php
return apiresponse()->forbidden('Acceso denegado');
```

---

### 💥 Error del servidor (500)

```php
apiresponse()->serverError(string $mensaje = null, mixed $errores = null)
```

```php
return apiresponse()->serverError('Error inesperado');
```

---

### ❌ Errores personalizados

```php
apiresponse()->error(string $mensaje = null, mixed $errores = null)
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

* AccessDeniedHttpException → 401 Unauthorized
* NotFoundHttpException → 404 Not Found
* TooManyRequestsHttpException → 429 Too Many Requests
* RouteNotFoundException → 401 Unauthorized
* AuthenticationException → 401 Unauthorized
* AuthorizationException → 403 Forbidden
* MethodNotAllowedHttpException → 405 Method Not Allowed
* ValidationException → 422 Unprocessable Entity
* HttpExceptionInterface (fallback) → respeta el status HTTP en rutas API

### ⚙️ Configuracion opcional (v1.1)

Si queres personalizar patrones de rutas y mensajes, publica la configuracion:

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

El repositorio también ejecuta tests automáticamente en GitHub Actions para `push` y `pull_request` con PHP `8.2` y `8.3`.

---

## 🧑 Autor

Ricardo Bazán  
Argentina, 2026  
Repositorio interno: [https://github.com/hardcodear/api-response-service](https://github.com/hardcodear/api-response-service)

---

## 📄 Licencia

Este paquete está licenciado bajo la [MIT License](LICENSE.md).
