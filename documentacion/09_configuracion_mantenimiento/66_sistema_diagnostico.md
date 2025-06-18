# Sistema de Diagnóstico - AUTOEXAM2

**Archivo:** `66_sistema_diagnostico.md`  
**Ubicación:** `/documentacion/09_configuracion_mantenimiento/`  
**Fecha:** 15 de junio de 2025  
**Versión:** 1.0

---

## Descripción General

El **Sistema de Diagnóstico** de AUTOEXAM2 proporciona un conjunto integral de herramientas web para diagnosticar, verificar y solucionar problemas del sistema de manera centralizada y accesible desde el navegador.

## Ubicación y Acceso

### Ubicación Física
```
/publico/diagnostico/
```

### Acceso Web
```
https://tu-dominio.com/publico/diagnostico/
```

## Características Principales

### 🎯 **Panel Centralizado**
- Interface web moderna y responsive
- Organización por categorías temáticas
- Acceso directo a todos los scripts
- Compatible con móviles y escritorio

### 🔒 **Seguridad Integrada**
- Configuración `.htaccess` específica
- Headers de seguridad
- Control de acceso a archivos
- Sin cache para resultados en tiempo real

### ⚡ **Optimización de Rendimiento**
- Tiempo de ejecución extendido (300 segundos)
- Memoria aumentada (256MB)
- Logging de errores habilitado
- Configuración específica para diagnóstico

## Estructura del Sistema

### Categorías de Scripts

#### 🔌 **Conexión (2 scripts)**
- `test_bd.php` - Test básico de conexión a base de datos
- `test_conexion_remota.php` - Test específico para conexiones remotas

#### 📧 **Correo (4 scripts)**
- `test_correo.php` - Test básico de envío de correos
- `test_simple_correo.php` - Test simplificado de correo
- `test_smtp_configs.php` - Verificación de configuraciones SMTP
- `test_smtp_debug.php` - Debug avanzado de SMTP

#### 🔑 **Recuperación (4 scripts)**
- `test_recuperacion.php` - Test del sistema de recuperación
- `test_servicio_recuperacion.php` - Test del servicio específico
- `test_recuperacion_completa.php` - Test completo del flujo
- `test_enlaces_recuperacion.php` - Verificación de enlaces

#### 🛡️ **Seguridad (3 scripts)**
- `test_fuerza_bruta.php` - Test de protección anti fuerza bruta
- `test_html_sanitizacion.php` - Verificación de sanitización HTML
- `test_caracteres_especiales.php` - Test de caracteres especiales UTF-8

#### ⚙️ **Sistema (4 scripts)**
- `test_almacenamiento.php` - Verificación del sistema de archivos
- `test_redirection.php` - Test de redirecciones
- `corregir_base_url.php` - Corrección automática de BASE_URL
- `eliminar_test_base_url.php` - Limpieza de archivos temporales

## Configuración Técnica

### Archivo .htaccess
```apache
# Permitir solo archivos PHP
<Files "*">
    Order Allow,Deny
    Deny from all
</Files>

<Files "*.php">
    Order Allow,Deny
    Allow from all
</Files>

# Configuración de rendimiento
php_value max_execution_time 300
php_value memory_limit 256M

# Sin cache para diagnósticos
Header set Cache-Control "no-cache, no-store, must-revalidate"
```

### Características de Seguridad
- Control de acceso por tipo de archivo
- Headers de seguridad estándar
- Prevención de acceso a archivos de configuración
- Configuración específica para diagnóstico

## Uso del Sistema

### Acceso Principal
1. Navegar a `https://tu-dominio.com/publico/diagnostico/`
2. Seleccionar la categoría apropiada
3. Hacer clic en el script deseado
4. Revisar los resultados del diagnóstico

### Scripts Individuales
Cada script puede ejecutarse individualmente:
```
https://tu-dominio.com/publico/diagnostico/test_bd.php
https://tu-dominio.com/publico/diagnostico/test_correo.php
```

## Casos de Uso Comunes

### 🔧 **Resolución de Problemas**
- **Error 500**: Usar `test_bd.php` y `test_conexion_remota.php`
- **Problemas de correo**: Usar scripts de la categoría 📧
- **BASE_URL incorrecta**: Usar `corregir_base_url.php`

### 🔍 **Verificación Post-Instalación**
- Verificar conectividad de base de datos
- Probar configuración de correo
- Validar configuración de seguridad

### 🚀 **Mantenimiento Preventivo**
- Ejecutar tests periódicos
- Verificar integridad del sistema
- Monitorizar rendimiento

## Integración con Documentación

### Referencias Cruzadas
- [Solución de Problemas de Correo](../03_autenticacion_seguridad/solucion_problemas_correo.md)
- [Herramientas Administrativas](herramientas_administrativas.md)
- [Estructura de Almacenamiento](estructura_almacenamiento_unificado.md)

### Documentación Relacionada
- Scripts de instalación y actualización
- Configuración de variables de entorno
- Gestión de base de datos

## Mantenimiento del Sistema

### Actualización de Scripts
1. Añadir nuevos scripts a `/publico/diagnostico/`
2. Actualizar el array `$scripts_disponibles` en `index.php`
3. Verificar permisos y configuración

### Monitorización
- Revisar logs de error regularmente
- Verificar funcionamiento de scripts críticos
- Mantener documentación actualizada

## Historial de Versiones

### Versión 1.0 (15 de junio de 2025)
- ✅ Implementación inicial del panel centralizado
- ✅ Organización de 19 scripts en 5 categorías
- ✅ Configuración de seguridad y rendimiento
- ✅ Interface responsive y moderna

---

## Notas Técnicas

### Requisitos
- PHP 7.4+
- Acceso web al directorio `/publico/`
- Permisos de escritura para logs

### Limitaciones
- Scripts requieren configuración `.env` válida
- Algunos tests necesitan conectividad externa
- Tiempo de ejecución limitado por configuración del servidor

### Recomendaciones
- Ejecutar en entorno de desarrollo para tests extensivos
- Usar con precaución en producción
- Mantener logs para auditoría

---

**Documentación complementaria:**
- Ver [Panel de Diagnóstico](../03_autenticacion_seguridad/solucion_problemas_correo.md) para casos específicos
- Consultar [Herramientas Administrativas](herramientas_administrativas.md) para scripts adicionales
