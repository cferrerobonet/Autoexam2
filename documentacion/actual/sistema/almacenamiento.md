# Sistema de Almacenamiento Unificado - AUTOEXAM2

**Última actualización:** 17 de junio de 2025

Este documento describe la estructura unificada de almacenamiento en AUTOEXAM2, integrando toda la información sobre directorios, archivos, políticas de retención y prácticas recomendadas.

---

## 1. Visión General

AUTOEXAM2 implementa una estructura centralizada y organizada para todos los archivos generados por el sistema. Esta unificación mejora la mantenibilidad, seguridad y eficiencia del sistema.

### 1.1 Objetivos del Sistema

- Proporcionar una estructura organizada para todos los archivos generados por la aplicación
- Centralizar la gestión de rutas de almacenamiento
- Separar claramente los archivos del sistema de los archivos de usuario
- Asegurar permisos adecuados y seguridad para los datos sensibles
- Facilitar el mantenimiento y las copias de seguridad

### 1.2 Evolución del Sistema

| Fecha | Cambio Principal |
|-------|-----------------|
| Junio 2024 | Estructura inicial distribuida |
| Junio 2025 (Fase 1) | Centralización en `/almacenamiento/` |
| Junio 2025 (Fase 2) | Migración de configuraciones y limpieza |
| Junio 2025 (Fase 3) | Unificación de avatares en directorio público |

---

## 2. Estructura Actual Completa

### 2.1 Estructura de Directorios Principal

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
```

### 2.2 Directorios Públicos

```
/publico/
  └── recursos/
      ├── img/       # Imágenes estáticas del sistema
      ├── css/       # Hojas de estilo
      ├── js/        # Scripts JavaScript
      └── subidas/
          └── avatares/ # Imágenes de perfil de usuarios (nueva ubicación)
```

---

## 3. Configuración del Sistema

### 3.1 Archivo de Configuración Central

La configuración del sistema de almacenamiento se define en `config/storage.php`:

```php
// Directorio raíz para todo el almacenamiento
define('STORAGE_PATH', ROOT_PATH . '/almacenamiento');

// Rutas específicas para cada tipo de almacenamiento
define('CONFIG_PATH', STORAGE_PATH . '/config');
define('LOGS_PATH', STORAGE_PATH . '/logs');
define('APP_LOGS_PATH', LOGS_PATH . '/app');
// etc...

// Rutas para avatares de usuario (públicas)
define('AVATARS_PUBLIC_SUBPATH', 'recursos/subidas/avatares');
define('AVATARS_STORAGE_DIR', ROOT_PATH . '/publico/' . AVATARS_PUBLIC_SUBPATH);
```

### 3.2 Constantes Disponibles

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
| Avatares | `AVATARS_STORAGE_DIR` | `/publico/recursos/subidas/avatares/` |

---

## 4. Uso para Desarrolladores

### 4.1 Registrar un mensaje en el log

```php
// Log general de la aplicación
log_message($mensaje, 'app');

// Log de errores
log_message($mensaje, 'error');

// Log del sistema
log_sistema($mensaje, 'info');
```

### 4.2 Subir un archivo

```php
// Ejemplo para subir un documento
$ruta_destino = UPLOADS_PATH . '/documentos/';
$nombre_archivo = 'doc_' . uniqid() . '.pdf';
move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_destino . $nombre_archivo);

// Ejemplo para avatar de usuario (ver documentación específica)
$ruta_destino = AVATARS_STORAGE_DIR . '/';
$nombre_archivo = 'perfil_' . uniqid() . '.png';
move_uploaded_file($_FILES['avatar']['tmp_name'], $ruta_destino . $nombre_archivo);
```

### 4.3 Funciones Auxiliares

```php
// Asegurar que existe un directorio
ensure_directory($path, $permissions = 0755);

// Obtener ruta para un archivo de log
get_log_path($name, $type = 'app');

// Registrar mensaje en log
log_message($message, $log_name = 'app', $type = 'app');
```

---

## 5. Gestión y Mantenimiento

### 5.1 Herramientas Disponibles

Para limpiar archivos temporales y logs antiguos, puede usar los scripts de mantenimiento:

```bash
./herramientas/gestor.sh limpiar-logs    # Limpia logs antiguos
./herramientas/gestor.sh limpiar-cache   # Limpia archivos de caché
```

### 5.2 Políticas de Retención

| Tipo de archivo | Tiempo de retención predeterminado |
|-----------------|-----------------------------------|
| Logs de errores | 30 días |
| Logs de acceso | 90 días |
| Cache | 7 días |
| Archivos temporales | 24 horas |
| Tokens de sesión expirados | Inmediato |
| Copias de seguridad | 90 días |

---

## 6. Consideraciones de Seguridad

1. **Permisos de directorios**:
   - El servidor web debe tener permisos de escritura
   - Los directorios deben tener permisos restrictivos (750 o 755)

2. **Acceso a archivos sensibles**:
   - Los archivos fuera del directorio `publico/` no son accesibles directamente
   - Implementar controladores específicos para servir archivos de forma segura

3. **Prevención de ataques**:
   - Validar siempre el tipo y tamaño de los archivos
   - Generar nombres aleatorios para evitar conflictos y predicciones
   - No confiar en la extensión proporcionada por el cliente

---

## 7. Compatibilidad

La estructura unificada es compatible con todos los entornos de producción soportados, incluyendo:
- Hosting compartido (IONOS, Hostinger, etc.)
- VPS/Servidores dedicados
- Entornos de desarrollo local

### 7.1 Rutas Actualizadas

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

## 8. Documentación Histórica

Este documento unifica la información anteriormente contenida en:
- `/09_configuracion_mantenimiento/estructura_almacenamiento.md` (Estructura básica/antigua)
- `/09_configuracion_mantenimiento/estructura_almacenamiento_unificado.md` (Estructura actualizada)
- `/01_estructura_presentacion/06_sistema_almacenamiento.md` (Información complementaria)

Para acceder a las versiones históricas, consultar el directorio `/documentacion/historial/versiones/`.
