# Changelog

Este paquete usa versionado semántico. Los tags publicados son inmutables.

## Unreleased

## 2.0.2 — 2026-11-21

- Las respuestas de error para solicitudes `HEAD` tienen el cuerpo vacío.
- Las respuestas generadas a partir de excepciones 404 conservan headers como `Cache-Control`.
- Las denegaciones de gates y policies sin un código explícito aplican el mensaje configurable `messages.authorization`.
- Las excepciones de validación respetan su código HTTP y permiten que Laravel gestione las respuestas explícitas.

## 2.0.1 — 2026-10-12

- Instalación del paquete exportado en aplicaciones Laravel 12 y 13 nuevas dentro de CI.
- Pruebas del kernel HTTP real para la integración de excepciones.
- Check agregado `CI`, permisos de lectura explícitos y acciones fijadas por SHA.
- Auditoría de dependencias de producción y actualizaciones automatizadas mediante PR.
- Guías de contribución, seguridad y releases; plantilla de PR.
- Ejemplos corregidos de configuración y JSON; README incluido en el archivo distribuible.
- Enlaces de soporte en Composer y ejecución de tests con el mismo PHP de Composer.

## 2.0.0 — 2026-09-08

- Soporte de Laravel 12 y 13 con Testbench 10 y 11 y matriz de dependencias mínimas.
- `AccessDeniedHttpException` responde con 403.
- Los errores HTTP 5xx conservan su status y ocultan mensajes internos.
- Conservación de headers HTTP en las respuestas de excepciones correspondientes.
- Un array vacío de errores se mantiene como `[]`.
- Configuración publicable para patrones de rutas y mensajes.

## 1.2.0

- Manejo de `ServiceUnavailableHttpException` con respuesta 503.

## 1.1.1

- Corrección de la matriz CI para excluir Laravel 13 con PHP 8.2.

## 1.1.0

- Compatibilidad con Laravel 13.

## 1.0.0

- Publicación inicial.
