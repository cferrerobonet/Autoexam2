# 05 – Autenticación y control de acceso en AUTOEXAM2

## 🎯 Objetivos clave del sistema

- Validar usuarios activos y su rol antes de permitir acceso a cualquier funcionalidad
- Controlar el acceso al sistema con múltiples métodos seguros (contraseña, PIN, OAuth corporativo)
- Garantizar sesión única por usuario y trazabilidad de accesos
- Bloquear accesos no autorizados, fuera de horario o por repetidos intentos
- Integrarse con todos los módulos del sistema mediante validación continua de sesión y permisos

---

## 🗃️ Tablas utilizadas o requeridas

### Tabla `usuarios`

| Campo          | Tipo          | Descripción                         |
|----------------|---------------|-------------------------------------|
| id_usuario     | INT PK AI     | Identificador único                 |
| nombre         | VARCHAR(100)  | Nombre del usuario                  |
| apellidos      | VARCHAR(150)  | Apellidos del usuario               |
| correo         | VARCHAR(150)  | Correo electrónico (único)         |
| contrasena     | VARCHAR(255)  | Contraseña cifrada                  |
| pin            | VARCHAR(6)    | PIN temporal (nullable)             |
| rol            | ENUM          | 'admin', 'profesor', 'alumno'       |
| curso_asignado | INT (nullable)| FK a curso si aplica (solo alumnos)|
| activo         | TINYINT(1)    | 1 = activo, 0 = inactivo            |
| foto           | VARCHAR(255)  | Ruta a la imagen (nullable)         |

### Tabla `sesiones_activas`

| Campo         | Tipo        | Descripción                    |
|---------------|-------------|--------------------------------|
| id_sesion     | INT PK AI   | ID de la sesión                |
| id_usuario    | INT (FK)    | Usuario conectado              |
| fecha_inicio  | DATETIME    | Cuándo inició la sesión        |
| ip            | VARCHAR(45) | IP del cliente                 |
| user_agent    | TEXT        | Navegador / Sistema            |
| activa        | TINYINT(1)  | Si sigue activa                |

### Tabla `tokens_recuperacion`

| Campo          | Tipo         | Descripción                         |
|----------------|--------------|-------------------------------------|
| id_token       | INT PK AI    | ID del token                        |
| id_usuario     | INT (FK)     | Usuario que lo generó               |
| token          | VARCHAR(64)  | Token único                         |
| fecha_creacion | DATETIME     | Cuándo se generó                    |
| usado          | TINYINT(1)   | 0 = activo, 1 = usado o caducado    |

---

## 🔒 Sistema de Variables de Entorno para Configuración Segura

### Configuración de Seguridad
AUTOEXAM2 implementa un sistema completo de gestión de configuración usando variables de entorno:

```bash
# Configuración de seguridad en .env
HASH_COST=12
SESSION_LIFETIME=7200
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_DURATION=900
```

### Biblioteca Env
La clase `Env` en `app/utilidades/env.php` proporciona:
- Carga segura de variables desde archivo `.env`
- Conversión automática de tipos (booleanos, números)
- Valores por defecto para variables faltantes
- Compatibilidad con `$_ENV` y `putenv()`

### Verificación de Instalación Previa
El sistema incluye verificaciones automáticas en todos los puntos de entrada:
- Verificación de archivos críticos (`.env`, `.lock`, `config.php`)
- Redirección automática al instalador si falta configuración
- Prevención de acceso no autorizado durante instalación

---

## 🔗 Dependencias funcionales

- `10_modulo_usuarios.md` (Implementado parcialmente)
- `23_sesiones_activas.md` (Pendiente de implementación completa)
- `24_control_horario_login.md` (Pendiente de implementación)
- `46_proteccion_fuerza_bruta.md` (Pendiente de implementación)
- `41_registro_actividad.md` (Implementado parcialmente mediante logs)
- `11_recuperacion_contrasena.md` (Implementado completamente)

---

## 🛡️ Estado actual de implementación

- ✅ Validación básica del correo y contraseña implementada
- ✅ Protección CSRF en formularios de login y recuperación implementada
- ✅ Uso de cookies seguras con atributos `Secure`, `HttpOnly` y `SameSite`
- ✅ Logs de errores técnicos en `/almacenamiento/logs/` y `/almacenamiento/registros/php_errors.log`
- ✅ Sistema de recuperación de contraseña completamente funcional
- ✅ Envío de correos de recuperación con soporte UTF-8
- ✅ Manejo de tokens seguros para restablecimiento de contraseña
- ✅ Sistema completo y robusto de envío de correos vía SMTP con PHPMailer
- ✅ Registro de actividad implementado con logs detallados
- ⚠️ Control de sesión única implementado parcialmente
- ❌ Integración con módulos de fuerza bruta pendiente
- ❌ Control horario de login pendiente

## 🛡️ Características a implementar

- Completar registro de actividad en base de datos (`registro_actividad`)
- Implementar control completo de sesión única con verificación de token cruzado
- Implementar la protección contra fuerza bruta
- Agregar control horario de login

## 📋 Herramientas de diagnóstico implementadas

El sistema incluye un conjunto completo de herramientas para diagnosticar problemas relacionados con la autenticación:

### Herramientas Web
- `/publico/diagnostico/test_smtp_debug.php` - Diagnóstico básico de SMTP
- `/publico/diagnostico/test_caracteres_especiales.php` - Prueba de caracteres UTF-8
- `/publico/diagnostico/smtp_avanzado.php` - Diagnóstico avanzado de SMTP
- `/publico/diagnostico/test_recuperacion.php` - Prueba del proceso de recuperación
- `/publico/diagnostico/test_enlaces_recuperacion.php` - Verificación de enlaces de recuperación

### Herramientas CLI
- `herramientas/diagnostico/test_correo.php` - Pruebas de envío por línea de comandos

## 🔄 Implementación de codificación UTF-8 segura

El sistema está completamente preparado para el manejo seguro de caracteres UTF-8:
- Configuración explícita de charset UTF-8 en todas las conexiones a bases de datos
- Envío de correos con encabezados y codificación UTF-8
- Plantillas HTML con meta tags UTF-8
- Base64 encoding para los headers de correo que contienen caracteres especiales
- Pruebas específicas para verificar el manejo correcto de acentos, eñes y otros caracteres especiales

---

## ✅ Checklist para Copilot

- [x] Crear formularios de login, recuperación y cambio de contraseña
- [x] Validar correo y contraseña en backend
- [x] Usar `password_hash` y `password_verify`
- [x] Generar token de sesión único y almacenarlo en `sesiones_activas`
- [✓] Verificar duplicidad y forzar cierre si hay otra sesión activa (parcial)
- [x] Implementar recuperación de contraseña por token temporal
- [✓] Registrar eventos en `registro_actividad` con IP y navegador (implementado con logs)
- [x] Aplicar middleware `verificarSesionActiva()` y `verificarRol()` en cada módulo protegido

---

📌 Para información completa sobre la recuperación de contraseña, consultar: `11_recuperacion_contrasena.md`