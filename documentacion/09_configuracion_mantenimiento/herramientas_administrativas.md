# Herramientas Administrativas de AUTOEXAM2

## 📋 Resumen General

AUTOEXAM2 incluye un sistema completo de herramientas administrativas organizadas por categorías y accesibles a través de un gestor maestro interactivo. Estas herramientas facilitan la administración, mantenimiento y seguridad del sistema.

---

## 🎛️ Gestor Maestro

### Ubicación y Ejecución
```bash
# Desde la raíz del proyecto
./herramientas/gestor.sh
```

### Características del Gestor
- **Menú interactivo colorizado**: Navegación intuitiva con códigos de color
- **Categorización**: Herramientas organizadas por tipo (Seguridad, Administración, Diagnóstico, Mantenimiento)
- **Ejecución segura**: Validación de archivos antes de ejecución
- **Estado del sistema**: Información en tiempo real sobre el estado del sistema
- **Suite automatizada**: Opciones para ejecutar múltiples herramientas secuencialmente

---

## 🔒 Herramientas de Seguridad

### Configuración Automática
**Archivo**: `herramientas/seguridad/configuracion/configurar_cron.sh`
- Configuración automática de tareas cron para monitorización
- Programación de alertas de seguridad
- Configuración de backup automático

### Migración de Configuración
**Archivo**: `herramientas/seguridad/migracion/migrar_configuracion.php`
- Migración automatizada de configuración legacy a sistema .env
- Backup automático antes de migración
- Modo dry-run para previsualización de cambios
- Validación de integridad de datos

### Monitorización 24/7
**Archivo**: `herramientas/seguridad/monitoreo/monitor_instalador.php`
- Monitor continuo del estado del instalador
- Detección de accesos no autorizados
- Alertas por email en caso de problemas
- Registro detallado de eventos de seguridad

### Suite de Testing
**Archivos de test ubicados en**: `herramientas/seguridad/testing/`

#### test_env.php
- Validación completa de la biblioteca de variables de entorno
- Verificación de carga de archivos .env
- Test de conversión de tipos (booleanos, strings)
- Validación de valores por defecto

#### test_deteccion_instalacion.php
- Test de detección automática de instalación previa
- Verificación de archivos críticos (.env, .lock, config.php)
- Validación de redirecciones automáticas
- Test de comportamiento en diferentes estados

#### test_autocompletado.php
- Validación del autocompletado del instalador
- Test de pre-rellenado de campos desde configuración existente
- Verificación de lectura de variables de entorno
- Validación de interfaz informativa

#### tests_integracion.php
- Suite completa de 63 tests de validación
- Tests de integración entre módulos
- Validación de funcionalidad end-to-end
- Reportes detallados de resultados

### Validación de Producción
**Archivo**: `herramientas/seguridad/validacion/validacion_produccion.php`
- Validación completa del entorno de producción
- Verificación de permisos de archivos
- Comprobación de configuración de seguridad
- Validación de conectividad (BD, SMTP, FTP)

---

## 👥 Herramientas de Administración (Preparadas para Futuro)

### Estructura Preparada
```
herramientas/administracion/
├── README.md              # Documentación de administración
├── usuarios/              # Gestión masiva de usuarios
├── permisos/              # Configuración de permisos del sistema
└── configuracion/         # Configuración general del sistema
```

### Funcionalidades Planificadas
- **Gestión masiva de usuarios**: Importación/exportación de usuarios desde CSV
- **Configuración de permisos**: Gestión granular de permisos por rol
- **Configuración del sistema**: Herramientas para modificar configuración global

---

## 🩺 Herramientas de Diagnóstico

### Ubicación y Acceso
Las herramientas de diagnóstico están ubicadas en `/publico/diagnostico/` para permitir su acceso desde el navegador web en entorno de producción.

**Acceso web**: `https://tudominio.com/diagnostico/`

### Herramientas Disponibles

#### Test de Correo Electrónico
**Archivo**: `publico/diagnostico/test_correo.php`
- Verificación completa de configuración SMTP
- Envío de correos de prueba
- Diagnóstico de problemas de conectividad
- Interfaz web amigable con formularios

**Uso**:
```
https://tudominio.com/diagnostico/test_correo.php
https://tudominio.com/diagnostico/test_correo.php?email=destino@ejemplo.com
```

#### Test Simple de Correo
**Archivo**: `publico/diagnostico/test_simple_correo.php`
- Prueba básica de envío de correos
- Menos configuraciones, más directo
- Ideal para diagnósticos rápidos

#### Test de Recuperación de Contraseña
**Archivo**: `publico/diagnostico/test_recuperacion_completa.php`
- Prueba completa del flujo de recuperación de contraseña
- Verificación de tokens de recuperación
- Validación de plantillas de correo
- Test de integración completo

**Uso**:
```
https://tudominio.com/diagnostico/test_recuperacion_completa.php?email=usuario@ejemplo.com
```

#### Test de Base de Datos
**Archivo**: `publico/diagnostico/test_bd.php`
- Verificación de conectividad a la base de datos
- Análisis de estructura de tablas
- Conteo de registros importantes
- Diagnóstico de problemas de conexión

### Consideraciones de Seguridad

⚠️ **IMPORTANTE**: Las herramientas de diagnóstico están ubicadas en el directorio público para permitir pruebas en producción con hostings como IONOS que no permiten acceso SSH. 

**Recomendaciones de seguridad**:
1. Restringir acceso por IP si es posible
2. Usar solo para diagnóstico temporal
3. Eliminar o proteger con contraseña en producción
4. Monitorear logs de acceso

### Migración de Ubicación

Las herramientas de diagnóstico fueron movidas desde `/herramientas/diagnostico/` a `/publico/diagnostico/` para:
- Permitir acceso web en hostings restrictivos
- Facilitar diagnóstico en producción
- Mejorar la interfaz de usuario con HTML
- Integrar mejor con el entorno web del sistema

---

## 🔧 Herramientas de Mantenimiento (Preparadas para Futuro)

### Estructura Preparada
```
herramientas/mantenimiento/
├── README.md              # Documentación de mantenimiento
├── backup/                # Scripts de backup automático
├── limpieza/              # Limpieza de archivos temporales
└── optimizacion/          # Optimización del sistema
```

### Funcionalidades Planificadas
- **Backup automático**: Respaldos programados de BD y archivos
- **Limpieza del sistema**: Eliminación de archivos temporales y logs antiguos
- **Optimización**: Herramientas para mejorar el rendimiento del sistema

---

## 📁 Preservación de Estructura con .gitkeep

### Archivos .gitkeep Implementados
Para asegurar que todos los directorios se mantengan en el control de versiones, se han implementado 9 archivos `.gitkeep`:

#### Administración (3 archivos)
- `herramientas/administracion/configuracion/.gitkeep`
- `herramientas/administracion/permisos/.gitkeep`
- `herramientas/administracion/usuarios/.gitkeep`

#### Diagnóstico (3 archivos)
- `herramientas/diagnostico/base_datos/.gitkeep`
- `herramientas/diagnostico/rendimiento/.gitkeep`
- `herramientas/diagnostico/sistema/.gitkeep`

#### Mantenimiento (3 archivos)
- `herramientas/mantenimiento/backup/.gitkeep`
- `herramientas/mantenimiento/limpieza/.gitkeep`
- `herramientas/mantenimiento/optimizacion/.gitkeep`

---

## 🚀 Uso de las Herramientas

### Ejecución Individual
```bash
# Ejecutar una herramienta específica
./herramientas/seguridad/configuracion/configurar_cron.sh
php ./herramientas/seguridad/monitoreo/monitor_instalador.php
```

### Ejecución a través del Gestor
```bash
# Iniciar el gestor maestro
./herramientas/gestor.sh

# Seleccionar categoría → Seleccionar herramienta
# El gestor maneja la ejecución y muestra resultados
```

### Suite Completa de Seguridad
El gestor incluye una opción para ejecutar automáticamente todas las herramientas de seguridad en secuencia:
1. Configuración de cron
2. Migración de configuración (si es necesaria)
3. Validación de producción
4. Suite completa de tests
5. Monitorización del instalador
6. Verificación final del sistema

---

## 📊 Estado de Implementación

### ✅ Completado (100%)
- Estructura de directorios
- Gestor maestro interactivo
- Herramientas de seguridad (5 herramientas)
- Suite de testing (4 tests + suite integral)
- Documentación completa
- Archivos .gitkeep para preservación de estructura
- Integración con sistema de variables de entorno

### 🔄 Preparado para Futuro Desarrollo
- Herramientas de administración
- Herramientas de diagnóstico  
- Herramientas de mantenimiento

La estructura está completamente preparada para el desarrollo futuro de nuevas herramientas administrativas.
