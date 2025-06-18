# Herramientas de Seguridad

Esta carpeta contiene todas las herramientas relacionadas con la seguridad del sistema AUTOEXAM2.

## Subdirectorios

### 🔧 configuracion/
Scripts para configurar aspectos de seguridad del sistema:
- `configurar_cron.sh` - Configuración automática de tareas cron de monitorización

### 📦 migracion/
Herramientas para migrar configuraciones de forma segura:
- `migrar_configuracion.php` - Migración de variables de entorno y configuración

### 👁️ monitoreo/
Scripts de monitorización continua de seguridad:
- `monitor_instalador.php` - Monitor de seguridad del instalador

### 🧪 testing/
Suite de tests de seguridad e integración:
- `tests_integracion.php` - 63 tests automatizados de seguridad

### ✅ validacion/
Validadores de configuración y producción:
- `validacion_produccion.php` - Validador para entornos de producción

## Uso Rápido

```bash
# Desde la raíz del proyecto:

# Configurar monitorización automática
./herramientas/seguridad/configuracion/configurar_cron.sh

# Migrar configuración
php herramientas/seguridad/migracion/migrar_configuracion.php

# Monitorizar seguridad
php herramientas/seguridad/monitoreo/monitor_instalador.php

# Ejecutar tests de seguridad
php herramientas/seguridad/testing/tests_integracion.php

# Validar producción
php herramientas/seguridad/validacion/validacion_produccion.php
```
