# Sistema de Autenticación y Seguridad - AUTOEXAM2

**Última actualización:** 17 de junio de 2025

Este documento unifica toda la información sobre el sistema de autenticación, seguridad, gestión de sesiones y recuperación de contraseñas de AUTOEXAM2.

---

## 1. Visión General

El sistema de autenticación y seguridad de AUTOEXAM2 proporciona mecanismos robustos para el control de acceso, protección contra ataques y gestión de sesiones de usuario.

### 1.1 Objetivos Clave

- Validar usuarios activos y su rol antes de permitir acceso a cualquier funcionalidad
- Controlar el acceso al sistema con métodos seguros (contraseña, sesión única)
- Garantizar sesión única por usuario y trazabilidad de accesos
- Bloquear accesos no autorizados, fuera de horario o por repetidos intentos
- Integrarse con todos los módulos mediante validación continua de sesión y permisos

### 1.2 Estado Actual de Implementación

- ✅ Validación básica del correo y contraseña - PRODUCCIÓN
- ✅ Protección CSRF en formularios - PRODUCCIÓN
- ✅ Recuperación de contraseña - PRODUCCIÓN
- ✅ Protección contra fuerza bruta - PRODUCCIÓN
- ✅ Sesión única por usuario - PRODUCCIÓN
- ✅ Gestión de sesiones activas - PRODUCCIÓN
- ✅ Control horario de acceso - PRODUCCIÓN
- ✅ Registro de actividad y auditoría - PRODUCCIÓN

---

## 2. Arquitectura del Sistema

### 2.1 Componentes Principales

```
┌────────────────────────┐     ┌──────────────────────┐     ┌──────────────────────┐
│                        │     │                      │     │                      │
│   Autenticación        │────▶│   Gestión Sesiones   │────▶│   Autorización       │
│   - Login/Logout       │     │   - Sesión activa    │     │   - Validación rol   │
│   - Recuperación       │     │   - Tracking         │     │   - Control acceso   │
│                        │     │                      │     │                      │
└────────────────────────┘     └──────────────────────┘     └──────────────────────┘
         ▲                               ▲                            ▲
         │                               │                            │
         ▼                               ▼                            ▼
┌────────────────────────┐     ┌──────────────────────┐     ┌──────────────────────┐
│                        │     │                      │     │                      │
│   Seguridad            │     │   Base de Datos      │     │   Registro           │
│   - Protección CSRF    │     │   - usuarios         │     │   - Logs acceso      │
│   - Anti-fuerza bruta  │     │   - sesiones_activas │     │   - Registro evento  │
│                        │     │   - tokens           │     │                      │
└────────────────────────┘     └──────────────────────┘     └──────────────────────┘
```

### 2.2 Tablas de Base de Datos

#### Tabla `usuarios`

| Campo         | Tipo          | Descripción                                    |
|---------------|---------------|------------------------------------------------|
| id_usuario    | INT           | Identificador único (PK)                       |
| correo        | VARCHAR(150)  | Correo electrónico (único)                     |
| contrasena    | VARCHAR(255)  | Hash de la contraseña                          |
| nombre        | VARCHAR(100)  | Nombre del usuario                             |
| apellidos     | VARCHAR(150)  | Apellidos del usuario                          |
| rol           | ENUM          | Rol: 'admin', 'profesor', 'alumno'             |
| activo        | TINYINT(1)    | Estado: 1=activo, 0=inactivo                   |
| ultimo_login  | DATETIME      | Fecha del último acceso exitoso                |
| intentos      | INT           | Contador de intentos fallidos                   |
| bloqueado     | DATETIME      | Fecha hasta la que está bloqueado              |

#### Tabla `tokens_recuperacion`

| Campo         | Tipo          | Descripción                                    |
|---------------|---------------|------------------------------------------------|
| id            | INT           | Identificador único (PK)                       |
| id_usuario    | INT           | ID del usuario (FK)                            |
| token         | VARCHAR(255)  | Token de recuperación                          |
| fecha_creacion| DATETIME      | Fecha de generación                            |
| fecha_expiracion| DATETIME    | Fecha de expiración                            |
| usado         | TINYINT(1)    | Indica si ya fue utilizado                     |

#### Tabla `sesiones_activas`

| Campo         | Tipo          | Descripción                                    |
|---------------|---------------|------------------------------------------------|
| id            | INT           | Identificador único (PK)                       |
| id_usuario    | INT           | ID del usuario (FK)                            |
| token_sesion  | VARCHAR(255)  | Token de sesión                                |
| ip            | VARCHAR(50)   | Dirección IP                                   |
| agente        | TEXT          | Navegador/dispositivo                          |
| fecha_inicio  | DATETIME      | Inicio de sesión                               |
| fecha_ultimo_acceso | DATETIME| Última actividad                               |
| activa        | TINYINT(1)    | Estado: 1=activa, 0=cerrada                    |

---

## 3. Flujo de Autenticación

### 3.1 Proceso de Login

1. Usuario envía credenciales (correo + contraseña)
2. Se valida token CSRF
3. Se verifica existencia del usuario y estado activo
4. Se verifica si el usuario está bloqueado por intentos fallidos
5. Se valida la contraseña con password_verify()
6. Si es correcto:
   - Se inicia sesión
   - Se regenera ID de sesión
   - Se registra en sesiones_activas
   - Se actualiza último_login
   - Se redirecciona según rol
7. Si es incorrecto:
   - Se incrementa contador de intentos
   - Se bloquea temporalmente si supera el límite

### 3.2 Proceso de Recuperación de Contraseña

1. Usuario solicita recuperación (introduce correo)
2. Sistema genera token único y lo almacena en la BD
3. Sistema envía correo con enlace que incluye token 
4. Usuario accede al enlace
5. Sistema valida token (existencia, caducidad, uso previo)
6. Usuario establece nueva contraseña
7. Sistema actualiza contraseña y marca token como usado

---

## 4. Implementación de Seguridad

### 4.1 Protección CSRF

- Generación de token único por sesión
- Almacenamiento en sesión del usuario
- Validación en cada petición POST
- Regeneración periódica

### 4.2 Protección contra Fuerza Bruta

- Contador de intentos fallidos en la tabla usuarios
- Bloqueo temporal progresivo (5min, 15min, 1h, 24h)
- Registro detallado de intentos fallidos
- Alertas de seguridad para administradores

### 4.3 Gestión de Sesiones

- Sesión única por usuario (configurable)
- Control de inactividad y tiempo máximo
- Listado de sesiones activas para el usuario
- Capacidad para cerrar sesiones remotas

### 4.4 Control Horario

- Restricción de acceso en horarios no permitidos
- Configuración por rol y grupo de usuarios
- Excepciones para usuarios específicos

---

## 5. Uso en el Código

### 5.1 Verificación de Autenticación

```php
// En controladores
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ' . BASE_URL . '/autenticacion/login');
    exit;
}

// En vistas
<?php if (isset($_SESSION['id_usuario'])): ?>
    <!-- Contenido para usuarios autenticados -->
<?php else: ?>
    <!-- Contenido para usuarios no autenticados -->
<?php endif; ?>
```

### 5.2 Verificación de Rol

```php
// En controladores
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
```

### 5.3 Protección CSRF

```php
// Generar token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// En formulario
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

// Validar token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: ' . BASE_URL . '/error/csrf');
    exit;
}
```

---

## 6. Archivos del Sistema

### 6.1 Archivos Principales

- `/app/controladores/autenticacion_controlador.php` - Gestión de login/logout
- `/app/servicios/recuperacion_servicio.php` - Recuperación de contraseñas
- `/app/utilidades/sesion.php` - Gestión de sesiones
- `/app/utilidades/fuerza_bruta.php` - Protección contra ataques
- `/app/modelos/sesion_activa_modelo.php` - Modelo para sesiones activas
- `/app/modelos/token_recuperacion_modelo.php` - Modelo para tokens

### 6.2 Archivos de Vista

- `/app/vistas/autenticacion/login.php` - Formulario de acceso
- `/app/vistas/autenticacion/recuperar.php` - Solicitud de recuperación
- `/app/vistas/autenticacion/restablecer.php` - Formulario de nueva contraseña
- `/app/vistas/perfil/sesiones.php` - Gestión de sesiones activas

---

## 7. Solución de Problemas Comunes

### 7.1 Problemas de Acceso
- Verificar estado del usuario (activo/inactivo)
- Comprobar bloqueo temporal por intentos fallidos
- Validar credenciales con la base de datos
- Verificar restricciones horarias

### 7.2 Problemas de Sesión
- Comprobar configuración de sesiones en PHP
- Verificar permisos en directorio de sesiones
- Validar tiempo de vida de la sesión

### 7.3 Problemas de Recuperación
- Verificar configuración de correo en .env
- Comprobar carpetas spam del usuario
- Verificar validez y caducidad de tokens

---

## 8. Documentación Histórica

Este documento unifica la información anteriormente contenida en:
- `/03_autenticacion_seguridad/05_autenticacion.md`
- `/03_autenticacion_seguridad/11_modulo_autenticacion.md` 
- `/03_autenticacion_seguridad/11_recuperacion_contrasena.md`
- `/03_autenticacion_seguridad/23_sesiones_activas.md`
- `/03_autenticacion_seguridad/24_control_horario_login.md`
- `/03_autenticacion_seguridad/46_proteccion_fuerza_bruta.md`
- `/03_autenticacion_seguridad/autenticacion_y_recuperacion_unificado.md`

Para acceder a las versiones históricas, consultar el directorio `/documentacion/historial/versiones/`.
