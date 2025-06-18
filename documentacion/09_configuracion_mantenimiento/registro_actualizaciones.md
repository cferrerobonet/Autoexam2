# Registro de Actualizaciones - AUTOEXAM2

## 13/06/2025 - Unificación de la estructura de almacenamiento

### Cambios principales

1. **Estructura centralizada**: Todos los archivos generados por el sistema (logs, caché, temporales, subidas) ahora se almacenan en una estructura unificada dentro de `/almacenamiento/`.

2. **Nueva configuración**: Se ha creado un archivo `/config/storage.php` que define constantes y funciones para la gestión de archivos.

3. **Funciones de logs**: Se han implementado funciones centralizadas para el registro de eventos y errores.

4. **Herramientas de mantenimiento**: Se han añadido scripts para gestionar los archivos generados por el sistema:
   - Migración de archivos antiguos
   - Limpieza de logs y archivos temporales
   - Verificación de rutas obsoletas

### Directrices para los desarrolladores

1. Utilizar siempre las constantes definidas en `storage.php` en lugar de rutas hardcodeadas.

2. Para el registro de eventos, utilizar la función `log_message()` con los parámetros adecuados:
   ```php
   log_message($mensaje, $nombre_log, $tipo_log);
   ```
   Donde `$tipo_log` puede ser: 'app', 'error', 'access' o 'system'.

3. Antes de crear directorios, utilizar la función `ensure_directory()` para garantizar que existen y tienen los permisos adecuados.

4. Para un mayor detalle sobre la nueva estructura, consultar la documentación en `/documentacion/09_configuracion_mantenimiento/estructura_almacenamiento.md`.

### Rutas actualizadas

| Propósito | Ruta Antigua | Nueva Ruta | Constante |
|-----------|--------------|------------|-----------|
| Logs de aplicación | Varios | `/almacenamiento/logs/app/` | `APP_LOGS_PATH` |
| Logs de errores | `almacenamiento/registros/php_errors.log` | `/almacenamiento/logs/errores/` | `ERROR_LOGS_PATH` |
| Logs de acceso | `publico/logs/` | `/almacenamiento/logs/acceso/` | `ACCESS_LOGS_PATH` |
| Logs del sistema | `tmp/logs/` | `/almacenamiento/logs/sistema/` | `SYSTEM_LOGS_PATH` |
| Cache de aplicación | `tmp/cache/` | `/almacenamiento/cache/app/` | `APP_CACHE_PATH` |
| Archivos temporales | `publico/temp/` | `/almacenamiento/tmp/` | `TMP_PATH` |
| Subidas | `publico/uploads/`, `publico/subidas/` | `/almacenamiento/subidas/` | `UPLOADS_PATH` |
| Copias de seguridad | `almacenamiento/copias/` | `/almacenamiento/copias/` | `BACKUP_PATH` |

### Tareas pendientes

- [ ] Actualizar cualquier referencia residual en scripts y controladores
- [ ] Eliminar o marcar como obsoletas las carpetas antiguas tras un período de verificación
- [ ] Comunicar los cambios a todos los desarrolladores del equipo

## 14/06/2025 - Limpieza final de la estructura de almacenamiento

### Cambios principales

1. **Eliminación de carpetas residuales**: Se han eliminado las carpetas residuales en `/publico/`:
   - `/publico/logs/`
   - `/publico/temp/`
   - Verificación de `/publico/uploads/` y `/publico/subidas/`

2. **Actualización de referencias en instalador**: Se han actualizado todas las referencias a las rutas antiguas en los archivos del instalador para usar la nueva estructura en `/almacenamiento/`.

3. **Nuevas herramientas de mantenimiento**:
   - `limpiar_directorios_residuales.sh`: Elimina las carpetas residuales en `/publico/` que solo contienen README.md
   - `actualizar_rutas_instalador.php`: Busca y reemplaza referencias a rutas antiguas en archivos PHP

### Estado de la migración

- [x] Actualizar referencias en el instalador
- [x] Eliminar carpetas antiguas y residuales
- [x] Comunicar los cambios al equipo de desarrollo

Todas las tareas de migración a la nueva estructura unificada de almacenamiento han sido completadas. El sistema ahora utiliza exclusivamente la estructura centralizada en `/almacenamiento/`.

## 14/06/2025 - Segunda fase de migración a estructura centralizada

### Cambios principales

1. **Migración de php.ini**: Se ha movido el archivo `php.ini` desde `/tmp/` a `/almacenamiento/config/`:
   - Actualizada la ruta en `index.php` para usar `CONFIG_PATH` en lugar de `TMP_PATH`
   - Actualizada la ruta del `error_log` en el archivo `php.ini` para usar la nueva estructura

2. **Limpieza de directorios residuales**:
   - Se eliminaron las carpetas `/publico/logs/` y `/publico/temp/`
   - Se limpió el directorio `/tmp/` dejando solo un archivo README.md
   - Se actualizaron las referencias en archivos del instalador para usar las nuevas rutas

3. **Actualización de gestor.sh**:
   - Añadidas nuevas herramientas para la migración y limpieza de archivos
   - Actualizada la verificación del estado de migración

### Estructura final de directorios

La estructura centralizada de almacenamiento ahora incluye:

```
/almacenamiento/
  ├── cache/         # Archivos de caché de aplicación
  ├── config/        # Archivos de configuración (php.ini, etc.)
  ├── copias/        # Copias de seguridad
  ├── logs/          # Logs de aplicación, errores, sistema, etc.
  ├── registros/     # Registros históricos (obsoleto)
  ├── subidas/       # Archivos subidos por usuarios
  └── tmp/           # Archivos temporales
```

**Actualización (14/06/2025)**: El directorio `/tmp` en la raíz del proyecto ha sido eliminado por completo. Todas sus funcionalidades han sido migradas a la estructura de `/almacenamiento/`.

### Estado de la migración

La migración a la estructura centralizada de almacenamiento ha sido completada. Todas las referencias a las rutas antiguas han sido actualizadas o eliminadas, y la aplicación está utilizando exclusivamente la nueva estructura centralizada en `/almacenamiento/`. Los directorios antiguos `/tmp/`, `/publico/logs/`, `/publico/temp/`, `/publico/uploads/` y `/publico/subidas/` han sido completamente eliminados.
