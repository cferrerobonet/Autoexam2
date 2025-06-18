# Estructura de Almacenamiento Unificada - AUTOEXAM2

**Última actualización:** 17 de junio de 2025

Este documento describe la estructura unificada de almacenamiento implementada en AUTOEXAM2, incluyendo las actualizaciones recientes, directrices de uso y herramientas de mantenimiento.

---

## 1. Visión General

AUTOEXAM2 ha implementado una estructura centralizada y organizada para todos los archivos generados por el sistema. Esta unificación busca mejorar la mantenibilidad, seguridad y eficiencia del sistema.

### 1.1 Estructura General Actual

```
/almacenamiento/
  ├── cache/         # Archivos de caché de aplicación
  │   ├── app/       # Cache de la aplicación
  │   ├── vistas/    # Cache de vistas
  │   └── datos/     # Cache de datos
  ├── config/        # Archivos de configuración (php.ini, etc.)
  ├── copias/        # Copias de seguridad
  │   ├── db/        # Copias de la base de datos
  │   └── sistema/   # Copias de configuración
  ├── logs/          # Logs de aplicación, errores, sistema, etc.
  │   ├── app/       # Logs de la aplicación
  │   ├── errores/   # Logs de errores PHP
  │   ├── acceso/    # Logs de acceso/auditoría
  │   └── sistema/   # Logs del sistema y diagnósticos
  ├── registros/     # Registros históricos (obsoleto)
  ├── subidas/       # Archivos subidos por usuarios (excepto avatares)
  │   ├── documentos/# Documentos subidos
  │   ├── examenes/  # Exámenes subidos
  │   └── imagenes/  # Imágenes subidas
  └── tmp/           # Archivos temporales
      ├── uploads/   # Subidas temporales
      └── sesiones/  # Datos de sesión (si se usan archivos)

/publico/
  └── recursos/
      └── subidas/
          └── avatares/ # Imágenes de perfil de usuarios (nueva ubicación)
```

### 1.2 Objetivos de la Unificación

1. **Centralización**: Todos los archivos generados por el sistema están en un solo directorio raíz.
2. **Organización**: Clara separación por tipo y propósito de los archivos.
3. **Mantenimiento**: Facilita las copias de seguridad y la limpieza periódica.
4. **Seguridad**: Mejor control de permisos y acceso a los archivos.

---

## 2. Implementación y Uso

### 2.1 Configuración del Sistema

La estructura centralizada se configura en el archivo:
```
/config/storage.php
```

Este archivo define las constantes y funciones necesarias para gestionar el almacenamiento.

### 2.2 Uso en el Código

```php
// Las constantes están disponibles después de cargar config/storage.php
require_once CONFIG_PATH . '/storage.php';

// Ejemplos de uso
$logPath = get_log_path('mi_modulo', 'app');
file_put_contents($logPath, "Mi mensaje", FILE_APPEND);

// O usando la función helper
log_message("Mi mensaje de error", "mi_modulo", "error");
```

### 2.3 Constantes Disponibles

| Propósito | Constante | Ruta |
|-----------|----------|-------|
| Logs de aplicación | `APP_LOGS_PATH` | `/almacenamiento/logs/app/` |
| Logs de errores | `ERROR_LOGS_PATH` | `/almacenamiento/logs/errores/` |
| Logs de acceso | `ACCESS_LOGS_PATH` | `/almacenamiento/logs/acceso/` |
| Logs del sistema | `SYSTEM_LOGS_PATH` | `/almacenamiento/logs/sistema/` |
| Cache de aplicación | `APP_CACHE_PATH` | `/almacenamiento/cache/app/` |
| Archivos temporales | `TMP_PATH` | `/almacenamiento/tmp/` |
| Subidas | `UPLOADS_PATH` | `/almacenamiento/subidas/` |
| Copias de seguridad | `BACKUP_PATH` | `/almacenamiento/copias/` |
| Configuración | `STORAGE_CONFIG_PATH` | `/almacenamiento/config/` |

---

## 3. Migración y Actualización

### 3.1 Historial de Cambios

#### Primera fase (13/06/2025)
- Creación de estructura centralizada en `/almacenamiento/`
- Implementación de `config/storage.php`
- Funciones de acceso a rutas de almacenamiento
- Herramientas de migración de archivos antiguos

#### Segunda fase (14/06/2025)
- Migración de `php.ini` a `/almacenamiento/config/`
- Eliminación de carpetas residuales en `/publico/`
- Limpieza del directorio `/tmp/`
- Actualización de referencias en instalador

#### Tercera fase (17/06/2025)
- Unificación de avatares en `/publico/recursos/subidas/avatares/`
- Actualización del procesamiento de fotos de perfil
- Documentación específica en `36_gestion_avatares_usuario.md`

### 3.2 Rutas Actualizadas

| Propósito | Ruta Antigua | Nueva Ruta |
|-----------|--------------|------------|
| Logs de aplicación | Varios | `/almacenamiento/logs/app/` |
| Logs de errores | `/almacenamiento/registros/php_errors.log` | `/almacenamiento/logs/errores/` |
| Logs de acceso | `/publico/logs/` | `/almacenamiento/logs/acceso/` |
| Logs del sistema | `/almacenamiento/logs/` | `/almacenamiento/logs/sistema/` |
| Cache de aplicación | `/tmp/cache/` | `/almacenamiento/cache/app/` |
| Archivos temporales | `/publico/temp/` | `/almacenamiento/tmp/` |
| Subidas (general) | `/publico/uploads/`, `/publico/subidas/` | `/almacenamiento/subidas/` |
| Avatares | `/publico/uploads/avatars/` | `/publico/recursos/subidas/avatares/` |
| Configuración PHP | `/tmp/php.ini` | `/almacenamiento/config/php.ini` |

---

## 4. Mantenimiento

### 4.1 Herramientas Disponibles

Para limpiar archivos temporales y logs antiguos, puede usar los scripts de mantenimiento:

```bash
./herramientas/gestor.sh limpiar-logs    # Limpia logs antiguos
./herramientas/gestor.sh limpiar-cache   # Limpia archivos de caché
```

### 4.2 Scripts de Mantenimiento Adicionales

- `limpiar_directorios_residuales.sh`: Elimina carpetas residuales en `/publico/`
- `actualizar_rutas_instalador.php`: Actualiza referencias a rutas antiguas
- `migracion_almacenamiento.php`: Migra archivos a la nueva estructura
- `limpiar_tokens_expirados.php`: Elimina tokens antiguos de la base de datos

### 4.3 Políticas de Retención

| Tipo de archivo | Tiempo de retención predeterminado |
|-----------------|-----------------------------------|
| Logs de errores | 30 días |
| Logs de acceso | 90 días |
| Cache | 7 días |
| Archivos temporales | 24 horas |
| Tokens de sesión expirados | Inmediato |
| Copias de seguridad | 90 días |

---

## 5. Compatibilidad

Las rutas antiguas siguen funcionando por compatibilidad, pero se recomienda migrar progresivamente a la nueva estructura. Todas las nuevas funcionalidades deben utilizar exclusivamente las constantes y funciones definidas en `config/storage.php`.

### 5.1 Compatibilidad con Entornos de Producción

La estructura centralizada es compatible con todos los entornos de producción soportados, incluyendo:
- Hosting compartido (IONOS, Hostinger, etc.)
- VPS/Servidores dedicados
- Entornos de desarrollo local

### 5.2 Recomendaciones para Nuevos Desarrollos

- **Usar siempre constantes**: Nunca hardcodear rutas de almacenamiento
- **Funciones auxiliares**: Utilizar `get_log_path()`, `get_cache_path()`, etc.
- **Verificar directorios**: Usar `ensure_directory()` antes de escribir archivos
- **Logs**: Usar `log_message()` para registro de eventos

---

## 6. Conclusión

La estructura unificada de almacenamiento representa una mejora significativa en la organización y mantenibilidad de AUTOEXAM2. Todas las tareas de migración a la nueva estructura han sido completadas con éxito, y el sistema ahora utiliza exclusivamente este nuevo enfoque centralizado.
