# Herramientas Administrativas de AUTOEXAM2

Esta carpeta contiene todas las herramientas administrativas organizadas por categorías.

## Estructura de Directorios

### 🔒 **seguridad/**
Herramientas relacionadas con la seguridad del sistema:
- **configuracion/**: Scripts de configuración de seguridad
- **migracion/**: Herramientas de migración segura
- **monitoreo/**: Scripts de monitorización de seguridad
- **testing/**: Tests de seguridad e integración
- **validacion/**: Validadores de configuración y producción

### 👥 **administracion/**
Herramientas de administración del sistema:
- **usuarios/**: Gestión de usuarios y roles
- **permisos/**: Configuración de permisos
- **configuracion/**: Configuración general del sistema

### 🩺 **diagnostico/**
Herramientas de diagnóstico y análisis:
- **sistema/**: Diagnóstico del sistema
- **rendimiento/**: Análisis de rendimiento
- **base_datos/**: Diagnóstico de base de datos

### 🔧 **mantenimiento/**
Herramientas de mantenimiento del sistema:
- **backup/**: Scripts de respaldo
- **limpieza/**: Limpieza de archivos temporales
- **optimizacion/**: Optimización del sistema

## Scripts Actuales

### Seguridad
- `seguridad/configuracion/configurar_cron.sh` - Configuración de tareas cron automáticas
- `seguridad/migracion/migrar_configuracion.php` - Migración de configuración de entorno
- `seguridad/monitoreo/monitor_instalador.php` - Monitor de seguridad del instalador
- `seguridad/testing/tests_integracion.php` - Suite de tests de integración
- `seguridad/validacion/validacion_produccion.php` - Validador de producción

## Uso

Para ejecutar cualquier script desde la raíz del proyecto:

```bash
# Configurar cron automáticamente
./herramientas/seguridad/configuracion/configurar_cron.sh

# Ejecutar tests de integración
php herramientas/seguridad/testing/tests_integracion.php

# Validar configuración de producción
php herramientas/seguridad/validacion/validacion_produccion.php

# Migrar configuración existente
php herramientas/seguridad/migracion/migrar_configuracion.php

# Monitorear seguridad del instalador
php herramientas/seguridad/monitoreo/monitor_instalador.php
```

## 🎛️ Script Gestor Maestro

Para una gestión centralizada de todas las herramientas, utilice el script gestor maestro:

```bash
# Ejecutar el gestor interactivo
./herramientas/gestor.sh
```

El gestor maestro proporciona:
- **Menú interactivo** con navegación por categorías
- **Ejecución segura** de todas las herramientas
- **Estado del sistema** en tiempo real
- **Suite completa de seguridad** con un solo comando
- **Ayuda contextual** para cada herramienta

### Funcionalidades del Gestor

1. **🔒 Seguridad**: Acceso a todas las herramientas de seguridad
   - Configuración automática de cron
   - Migración de configuración
   - Monitorización de seguridad
   - Tests de integración
   - Validación de producción
   - **Suite completa**: Ejecuta todos los scripts de seguridad secuencialmente

2. **📊 Estado del Sistema**: Verificación automática de:
   - Archivos de configuración
   - Permisos de directorios
   - Configuración de tareas cron
   - Herramientas disponibles

3. **❓ Ayuda**: Documentación integrada de la estructura de herramientas

## Ventajas de la Estructura Organizativa

### ✅ **Escalabilidad**
- Estructura preparada para futuras herramientas
- Organización por categorías específicas
- Fácil localización de scripts

### ✅ **Mantenibilidad**
- Cada script en su directorio apropiado
- README específico por categoría
- Documentación integrada

### ✅ **Usabilidad**
- Script gestor maestro para facilitar el uso
- Rutas consistentes y predecibles
- Ejecución desde la raíz del proyecto

## Futuras Herramientas

Esta estructura está preparada para escalar con nuevas herramientas administrativas:

### 👥 **Administración** (Planificado)
- Gestión masiva de usuarios
- Configuración automática de permisos
- Herramientas de configuración del sistema

### 🩺 **Diagnóstico** (Planificado)
- Análisis de rendimiento
- Diagnóstico de base de datos
- Monitorización del sistema

### 🔧 **Mantenimiento** (Planificado)
- Scripts de backup automatizado
- Limpieza de archivos temporales
- Optimización del sistema
