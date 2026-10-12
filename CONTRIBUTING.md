# Contribuir

Los cambios entran a `main` mediante pull requests. Para contribuir desde fuera del repositorio, creá un fork y una rama; no necesitás permisos de escritura.

## Preparación

- PHP 8.2+ para Laravel 12; PHP 8.3+ para Laravel 13.
- Composer 2 y las extensiones requeridas por Laravel y PHPUnit.

```bash
composer install
composer validate --strict
composer test
```

Como es una librería, `composer.lock` no se versiona. CI resuelve dependencias recientes y mínimas para las versiones soportadas.

## Pull requests

1. Abrí una rama desde `main` actualizado.
2. Explicá el problema y el comportamiento esperado. Mantené cada PR enfocado.
3. Añadí una prueba cuando cambie un comportamiento observable o una integración.
4. Actualizá README y la sección `Unreleased` del changelog si corresponde.
5. Abrí el PR contra `main` y esperá a que pase el check `CI`.

La suite cubre respuestas JSON, provider, helper, facade y excepciones. CI también prueba la instalación del paquete exportado en aplicaciones Laravel nuevas. Los cambios en Composer, autodiscovery, configuración o distribución deben mantener esas instalaciones funcionando.

`main` requiere PR, checks exitosos sobre una rama actualizada y conversaciones resueltas. No exige aprobación externa mientras haya un único mantenedor. Las reglas también se aplican al administrador; no se permite force-push ni borrar `main`. Los PR se integran con squash.

## Compatibilidad

Seguí versionado semántico. Cambiar códigos HTTP, estructura JSON, firmas públicas o versiones mínimas soportadas puede requerir una versión mayor. No cambies tags ya publicados.

Para reportar un bug, incluí versiones de PHP/Laravel/paquete, un ejemplo mínimo, resultado esperado y resultado obtenido. Quitá tokens y datos privados. Los problemas de seguridad se reportan mediante el canal de [SECURITY.md](SECURITY.md).
