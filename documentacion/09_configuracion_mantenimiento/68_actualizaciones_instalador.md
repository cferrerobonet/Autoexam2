# Actualizaciones del Instalador - AUTOEXAM2

**Archivo:** `68_actualizaciones_instalador.md`  
**Ubicación:** `/documentacion/09_configuracion_mantenimiento/`  
**Fecha:** 15 de junio de 2025  
**Versión:** 1.0

---

## Descripción General

Este documento detalla las **actualizaciones críticas** implementadas en el sistema de instalación de AUTOEXAM2, incluyendo mejoras en la detección automática de BASE_URL, manejo de tablas actualizadas y corrección de errores en el ruteador.

## Problemas Identificados y Solucionados

### 🔍 **Problema Principal: Error 500 y Redirección Incorrecta**

#### Síntomas
- Error 500 en la página de inicio
- Redirección automática a `http://localhost:8000`
- Conflicto entre configuración local y servidor remoto
- BASE_URL no detectada automáticamente

#### Causa Raíz
```env
# Configuración problemática en .env
BASE_URL=http://localhost:8000          # ← Local
DB_HOST=db5017707563.hosting-data.io    # ← Remoto
DB_NAME=dbs14153299                     # ← Remoto
```

## Mejoras Implementadas

### 1. **Detección Automática de BASE_URL**

#### Archivo: `publico/instalador/index.php`
**Funcionalidad mejorada:**
```php
// Detección automática del dominio real
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . '://' . $host;

// Escribir BASE_URL detectada automáticamente
$env_content = "BASE_URL={$base_url}\n" . $env_content;
```

#### Beneficios
- ✅ Detección automática del protocolo (HTTP/HTTPS)
- ✅ Adaptación al dominio real del servidor
- ✅ Eliminación de configuraciones hardcoded
- ✅ Funcionamiento tanto en local como producción

### 2. **Lista de Tablas Actualizada**

#### Archivo: `publico/instalador/funciones_tablas.php`
**Tablas añadidas:**
```php
'tokens_recuperacion',      // Nueva - Gestión de tokens
'registro_actividad',       // Nueva - Auditoría del sistema  
'intentos_login',          // Nueva - Seguridad anti fuerza bruta
```

#### Lista Completa (17 tablas)
```php
return [
    'usuarios', 'instituciones', 'cursos', 'modulos', 'examenes',
    'preguntas', 'opciones_respuesta', 'respuestas_usuario', 
    'resultados_examen', 'configuracion_sistema', 'permisos', 
    'roles', 'usuario_roles', 'sesiones_activas',
    'tokens_recuperacion', 'registro_actividad', 'intentos_login'
];
```

### 3. **Scripts SQL Actualizados**

#### Nueva Estructura de Archivos
- ✅ `/base_datos/migraciones/001_esquema_completo.sql`
- ✅ `/base_datos/mantenimiento/vaciar_todas_tablas.sql`
- ✅ `/base_datos/mantenimiento/eliminar_todas_tablas.sql`

#### Archivo: `publico/instalador/instalacion_completa.php`
**Ruta corregida:**
```php
// Ruta actualizada al nuevo esquema
$script_path = '/base_datos/migraciones/001_esquema_completo.sql';
```

### 4. **Manejo Robusto de Errores**

#### Archivo: `app/controladores/ruteador.php`
**Mejoras implementadas:**
```php
// Manejo seguro de excepciones
try {
    // Lógica de enrutamiento
} catch (Exception $e) {
    error_log("Error en ruteador: " . $e->getMessage());
    $this->manejarError500();
}

// Corrección de error con get_class()
if (is_object($e)) {
    $tipo_excepcion = get_class($e);
} else {
    $tipo_excepcion = 'Exception';
}
```

## Scripts de Diagnóstico Creados

### 1. **Script de Conexión Remota**
**Archivo:** `publico/diagnostico/test_conexion_remota.php`
```php
// Test específico para conexión BD remota
$host = 'db5017707563.hosting-data.io';
$database = 'dbs14153299';
// ... lógica de conexión y diagnóstico
```

### 2. **Script de Corrección BASE_URL**
**Archivo:** `publico/diagnostico/corregir_base_url.php`
```php
// Detección automática y corrección
$protocol = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
$new_base_url = $protocol . '://' . $_SERVER['HTTP_HOST'];
// ... escritura al archivo .env
```

### 3. **Script de Limpieza**
**Archivo:** `publico/diagnostico/eliminar_test_base_url.php`
```php
// Limpieza de archivos temporales de test
// Eliminación segura de pruebas BASE_URL
```

## Funcionalidades del Instalador Mejorado

### **Opciones Disponibles**

#### 1. **Instalación Completa**
- Crear base de datos completa
- Configurar usuario administrador
- Generar archivo .env automático
- Detectar BASE_URL del entorno

#### 2. **Actualizar Tablas**
- Verificar existencia de las 17 tablas
- Crear tablas faltantes
- Mantener datos existentes
- Reporte detallado de cambios

#### 3. **Vaciar Tablas**
- Limpiar contenido manteniendo estructura
- Ideal para entorno de desarrollo
- Conservar configuraciones críticas

#### 4. **Eliminar Todo**
- Eliminación completa para reinstalación
- Limpieza total del sistema
- Confirmación de seguridad requerida

### **Detección de Entorno**

#### Variables Automáticas
```php
// Detección automática en instalador
$env_vars = [
    'BASE_URL' => $base_url,                     // Auto-detectada
    'DB_HOST' => $_POST['db_host'],              // Usuario
    'DB_NAME' => $_POST['db_name'],              // Usuario
    'DB_USER' => $_POST['db_user'],              // Usuario
    'DB_PASS' => $_POST['db_pass'],              // Usuario
    'MAIL_HOST' => 'smtp.gmail.com',             // Predefinida
    'MAIL_PORT' => '587',                        // Predefinida
    'MAIL_ENCRYPTION' => 'tls',                  // Predefinida
];
```

## Integración con Sistema de Diagnóstico

### **Panel Centralizado**
- Acceso web: `https://dominio.com/publico/diagnostico/`
- Tests específicos para instalador
- Verificación post-instalación
- Herramientas de corrección automática

### **Scripts de Verificación**
- `test_bd.php` - Conexión básica
- `test_conexion_remota.php` - Conexión específica remota
- `corregir_base_url.php` - Corrección automática
- `test_instalador_completo.php` - Verificación integral

## Casos de Uso Típicos

### **Instalación en Servidor Nuevo**
1. Ejecutar instalador completo
2. Configurar parámetros de BD
3. Sistema detecta BASE_URL automáticamente
4. Verificar con panel de diagnóstico

### **Migración de Desarrollo a Producción**
1. Usar script de corrección BASE_URL
2. Verificar conexión a BD remota
3. Ejecutar tests de funcionamiento
4. Confirmar configuración final

### **Actualización de Sistema Existente**
1. Usar opción "Actualizar Tablas"
2. Sistema verifica las 17 tablas
3. Crea tablas faltantes automáticamente
4. Conserva datos existentes

## Archivos Modificados

### **Instalador Principal**
- `publico/instalador/index.php` - Detección BASE_URL
- `publico/instalador/funciones_tablas.php` - Lista actualizada
- `publico/instalador/actualizar_tablas.php` - Lógica mejorada
- `publico/instalador/instalacion_completa.php` - Rutas corregidas

### **Sistema de Ruteo**
- `app/controladores/ruteador.php` - Manejo de errores

### **Configuración**
- `.env` - Variables corregidas automáticamente
- `base_datos/` - Nueva estructura organizacional

## Validación y Testing

### **Tests Automatizados**
- Verificación de conexión BD
- Validación de configuración .env  
- Test de redirecciones
- Verificación de BASE_URL

### **Scenarios de Prueba**
- ✅ Instalación en localhost
- ✅ Instalación en servidor remoto
- ✅ Migración entre entornos
- ✅ Actualización de versiones

## Beneficios de las Actualizaciones

### 🚀 **Facilidad de Uso**
- Instalación automática sin configuración manual
- Detección inteligente del entorno
- Interface web intuitiva

### 🛡️ **Robustez y Confiabilidad**
- Manejo de errores mejorado
- Validaciones de integridad
- Recuperación automática de errores

### 🔧 **Mantenimiento Simplificado**
- Scripts de diagnóstico integrados
- Herramientas de corrección automática
- Documentación completa

### 📈 **Escalabilidad**
- Estructura preparada para nuevas tablas
- Sistema de migraciones organizadas
- Configuración flexible

## Documentación Relacionada

### Referencias Técnicas
- [Estructura de Base de Datos](67_estructura_base_datos.md)
- [Sistema de Diagnóstico](66_sistema_diagnostico.md)
- [Variables de Entorno](variables_entorno.md)

### Guías de Usuario
- [Instalador Original](../01_estructura_presentacion/03_instalador.md)
- [Solución de Problemas](../03_autenticacion_seguridad/solucion_problemas_correo.md)

## Historial de Versiones

### Versión 1.0 (15 de junio de 2025)
- ✅ Implementación de detección automática BASE_URL
- ✅ Actualización completa de lista de tablas (17 total)
- ✅ Corrección de manejo de errores en ruteador
- ✅ Integración con sistema de diagnóstico
- ✅ Nueva estructura organizacional de BD
- ✅ Scripts de corrección y verificación

---

## Notas de Implementación

### **Requisitos Previos**
- PHP 7.4+
- MySQL/MariaDB 5.7+
- Permisos de escritura en directorio raíz
- Acceso web al directorio `/publico/`

### **Consideraciones de Seguridad**
- Validación de parámetros de entrada
- Sanitización de variables de entorno
- Verificación de permisos de archivos
- Logging de operaciones críticas

### **Limitaciones Conocidas**
- Requiere configuración manual de SMTP
- Algunas configuraciones avanzadas no son automáticas
- Dependiente de permisos del servidor web

---

**Para usar el instalador actualizado:** Navegar a `https://tu-dominio.com/publico/instalador/` y seguir las instrucciones de la interface web.
