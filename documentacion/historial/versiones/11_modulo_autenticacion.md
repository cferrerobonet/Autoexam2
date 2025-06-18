# 11 – Módulo de Autenticación en AUTOEXAM2

Este documento define la estructura, métodos de autenticación y seguridad del módulo de acceso a la plataforma, con compatibilidad multidispositivo, protecciones avanzadas y soporte de autenticación externa.

---

## 🎯 Objetivos clave del sistema

- Centralizar todas las funciones relacionadas con login, logout, verificación de sesiones y recuperación de acceso  
- Proporcionar un núcleo común para que otros módulos validen sesión y rol del usuario antes de ejecutar lógica o mostrar contenido  
- Registrar y trazar todos los eventos relacionados con autenticación: entradas, salidas, fallos, bloqueos y recuperaciones  

---

## 🔗 Dependencias funcionales

- `05_autenticacion.md` (Implementado parcialmente)
- `41_registro_actividad.md` (Implementado parcialmente mediante logs)
- `23_sesiones_activas.md` (Pendiente de implementación completa)
- `46_proteccion_fuerza_bruta.md` (Pendiente de implementación)
- `24_control_horario_login.md` (Pendiente de implementación)
- `config/config.php` (Implementado)

---

## 🗃️ Tablas utilizadas o requeridas

### Tabla `sesiones_activas`

| Campo         | Tipo        | Descripción                    |
|---------------|-------------|--------------------------------|
| id_sesion     | INT PK AI   | ID de la sesión                |
| id_usuario    | INT (FK)    | Usuario conectado              |
| fecha_inicio  | DATETIME    | Cuándo inició la sesión        |
| ip            | VARCHAR(45) | IP del cliente                 |
| user_agent    | TEXT        | Navegador / Sistema            |
| activa        | TINYINT(1)  | Si sigue activa                |

---

## 📦 Uso de tabla `usuarios`

Este módulo no requiere una tabla propia. Utiliza la tabla `usuarios` y valida los campos:

- `correo`  
- `contrasena` (hash segura)  
- `activo`  
- `rol`  

---

## 🔐 Métodos de autenticación

### 1. Correo y contraseña (Implementado)

- ✅ Validación visual + HTML5  
- ✅ Hash seguro (`password_hash`)
- ⚠️ Seguridad reforzada pendiente: mínimo 8 caracteres, mayúscula, número, símbolo  
- ❌ Doble campo para verificación no implementado

### 2. PIN temporal por email (Pendiente)

- ❌ Código numérico de 6 cifras válido durante 15 minutos  
- ❌ Envío automático por SMTP  
- ❌ Asociado al ID de usuario y con expiración  
- ❌ De un solo uso  

### 3. Microsoft 365 (OAuth) (Pendiente)

- ❌ Basado en OAuth 2.0 (pendiente de configuración en Microsoft Entra ID)  
- ❌ Redirección a login de Microsoft  
- ❌ Retorno con token válido  
- ❌ No se almacena contraseña localmente  

---

## 🔄 Recuperación de contraseña (Parcialmente implementado)

- ✅ Formulario de solicitud mediante correo  
- ❌ Envío de enlace con token único válido 60 minutos (pendiente)
- ❌ Formulario para nueva contraseña (pendiente)

---

## 🔒 Seguridad y control de sesiones (Parcialmente implementado)

- ⚠️ Control de sesión única parcial
- ❌ Expulsión de sesión anterior en nuevo login (pendiente)
- ❌ Limitación de 5 intentos fallidos (pendiente)
- ❌ Bloqueo temporal de 30 minutos (pendiente)
- ❌ Notificación a administrador de bloqueo (pendiente)
- ✅ Formularios protegidos con tokens CSRF  
- ✅ Cookies configuradas con `Secure`, `HttpOnly`, `SameSite=Lax` (compatible)

---

## 🧑‍💻 UI/UX (Implementado)

- ✅ Formularios con iconos (`fa-envelope`, `fa-lock`, `fa-key`)  
- ❌ Barra de fuerza de contraseña (pendiente)
- ❌ Tooltips explicativos en campos sensibles (pendiente) 
- ✅ Feedback visual inmediato con Bootstrap  

---

## 🛡️ Seguridad y control de sesiones

- Al iniciar sesión, se genera un token único guardado en sesión y en `sesiones_activas`  
- Cada vista protegida debe usar `validarSesionActiva()` y `verificarRol()`  
- Si se detecta token duplicado, se fuerza cierre de sesión anterior  
- Toda acción queda registrada en `registro_actividad`  

---

## ✅ Validación de datos

- Validación de email y contraseña desde lógica de backend  
- Validación del token de sesión en cada página privada  
- Sanitización de cualquier parámetro recuperado vía GET o POST  

---

## 🪵 Manejo de errores y logs

- Cada login, logout, fallo o bloqueo se registra en `registro_actividad`  
- Si se detecta sesión inválida → redirección + log  
- Errores técnicos → registrados en `/almacenamiento/logs/sesion_error.log`  

---

## 🧪 Casos límite esperados

- Sesión inexistente → redirección automática al login  
- Token manipulado → sesión cerrada + log  
- Nuevo login fuerza cierre de sesión anterior  
- Acceso a recursos sin permisos → redirección + log  

---

## 📂 Archivos y estructura MVC

| Componente                   | Ruta                                                |
|-----------------------------|-----------------------------------------------------|
| Controlador de acceso       | `app/controladores/autenticacion.php`              |
| Vista de login              | `app/vistas/autenticacion/login.php`               |
| Vista login con PIN         | `app/vistas/autenticacion/login_pin.php`           |
| Vista recuperar contraseña  | `app/vistas/autenticacion/recuperar_contrasena.php`|
| Validaciones                | `app/utilidades/validacion.php`                    |
| Lógica de sesión            | `app/utilidades/sesion.php`                        |
| Envío de correo PIN         | `app/utilidades/correo_pin.php`                    |

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación exhaustiva de entradas, permisos y sesiones
- Uso de token CSRF en formularios críticos
- Registro detallado de acciones en `registro_actividad`
- Logs técnicos separados por módulo en `/almacenamiento/logs/`
- Acceso restringido por rol y curso donde aplique
- Control de errores con feedback claro para el usuario
- Sanitización de entradas y protección contra manipulación
- Integración segura con otros módulos relacionados


## ✅ Checklist para Copilot

- [ ] Implementar formulario de login (correo + contraseña)  
- [ ] Añadir login con PIN temporal  
- [ ] Preparar OAuth Microsoft (dejar modularizado)  
- [ ] Incorporar lógica de sesión única  
- [ ] Proteger todos los formularios con token CSRF  
- [ ] Añadir recuperación de contraseña por email  
- [ ] Mostrar feedback visual de errores y validaciones  

---

📌 A continuación, Copilot debe leer e implementar: `23_sesiones_activas.md`
