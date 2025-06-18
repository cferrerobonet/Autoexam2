# Estructura de Almacenamiento en AUTOEXAM2

Este documento describe la estructura unificada de almacenamiento implementada el 13/06/2025.

## Estructura General

```
/almacenamiento/
  ├── logs/               # Todos los archivos de registro
  │   ├── app/            # Logs de la aplicación
  │   ├── errores/        # Logs de errores PHP
  │   ├── acceso/         # Logs de acceso/auditoría
  │   └── sistema/        # Logs del sistema y diagnósticos
  ├── cache/              # Cache del sistema
  │   ├── app/            # Cache de la aplicación
  │   ├── vistas/         # Cache de vistas
  │   └── datos/          # Cache de datos
  ├── tmp/                # Archivos temporales
  │   ├── uploads/        # Subidas temporales
  │   └── sesiones/       # Datos de sesión (si se usan archivos)
  ├── subidas/            # Subidas permanentes de usuarios
  │   ├── imagenes/       # Imágenes subidas
  │   ├── documentos/     # Documentos subidos
  │   └── examenes/       # Exámenes subidos
  └── copias/             # Copias de seguridad
      ├── db/             # Copias de la base de datos
      └── sistema/        # Copias de configuración
```

## Objetivos de la Unificación

1. **Centralización**: Todos los archivos generados por el sistema están en un solo directorio raíz.
2. **Organización**: Clara separación por tipo y propósito de los archivos.
3. **Mantenimiento**: Facilita las copias de seguridad y la limpieza periódica.
4. **Seguridad**: Mejor control de permisos y acceso a los archivos.

## Uso en el Código

Para utilizar esta estructura en el código:

```php
// Las constantes están disponibles después de cargar config/storage.php
require_once CONFIG_PATH . '/storage.php';

// Ejemplos de uso
 = get_log_path('mi_modulo', 'app');
file_put_contents(, "Mi mensaje", FILE_APPEND);

// O usando la función helper
log_message("Mi mensaje de error", "mi_modulo", "error");
```

## Compatibilidad

Las rutas antiguas siguen funcionando por compatibilidad, pero se recomienda migrar progresivamente a la nueva estructura.

## Mantenimiento

Para limpiar archivos temporales y logs antiguos, puede usar los scripts de mantenimiento:

```bash
./herramientas/gestor.sh limpiar-logs
./herramientas/gestor.sh limpiar-cache
```
