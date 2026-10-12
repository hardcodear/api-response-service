# Publicar una versión

1. Prepará un PR desde una rama nueva y actualizá `CHANGELOG.md` y las notas de migración si hay cambios incompatibles. Elegí una versión según SemVer.
2. Verificá `composer validate --strict`, `composer test` y el check completo `CI`. Las pruebas de consumidor instalan el árbol de `git archive`, incluyendo las reglas `export-ignore`.
3. Integrá el PR mediante squash y esperá a que CI pase también para ese commit en `main`.
4. Etiquetá exactamente ese commit y publicá una release en GitHub con las notas del changelog. No reutilices ni muevas tags publicados. Ejemplo para una versión futura:

```bash
git switch main
git pull --ff-only
git tag -a v2.0.1 -m "Release v2.0.1"
git push origin v2.0.1
```

5. Comprobá en [Packagist](https://packagist.org/packages/hardcodear/api-response-service) que la versión y la referencia coincidan con el tag. El repositorio tiene un webhook de push; si la versión no aparece, revisá sus entregas y la actualización de Packagist.
6. Instalá la versión publicada en una aplicación Laravel limpia mediante `composer require hardcodear/api-response-service:2.0.1 --prefer-dist`, y verificá el ejemplo del README y `php artisan vendor:publish --tag=apiresponse-config`.

La versión de Composer se infiere del tag: no agregues un campo `version` a `composer.json`. No publiques `vendor`, tests, scripts de CI ni credenciales. El archivo distribuible conserva `src`, `config`, `composer.json`, README y licencia.

La rama `main` exige PR y checks, incluso para el administrador. El check requerido es `CI` de GitHub Actions y no depende de los nombres variables de la matriz. No renombres ni elimines ese job sin actualizar la regla de protección. No se configura aprobación externa obligatoria mientras el proyecto tenga un solo mantenedor.
