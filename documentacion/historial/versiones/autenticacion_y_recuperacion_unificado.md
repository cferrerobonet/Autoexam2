# Autenticación y Recuperación de Contraseñas - AUTOEXAM2

**Última actualización:** 13 de junio de 2025

Este documento unifica la información sobre el sistema de autenticación y recuperación de contraseñas de AUTOEXAM2, incluyendo los estados de implementación, arquitectura, flujos de trabajo y mejoras recientes.

---

## 1. Visión General del Sistema de Autenticación

### 1.1 Objetivos Clave
- Validar usuarios activos y su rol antes de permitir acceso a cualquier funcionalidad
- Controlar el acceso al sistema con múltiples métodos seguros (contraseña, PIN, OAuth corporativo)
- Garantizar sesión única por usuario y trazabilidad de accesos
- Bloquear accesos no autorizados, fuera de horario o por repetidos intentos
- Integrarse con todos los módulos del sistema mediante validación continua de sesión y permisos

### 1.2 Estado Actual de Implementación
- ✅ Validación básica del correo y contraseña implementada
- ✅ Protección CSRF en formularios de login y recuperación implementada
- ✅ Uso de cookies seguras con atributos `Secure`, `HttpOnly` y `SameSite`
- ✅ Logs de errores técnicos en `/almacenamiento/logs/`
- ✅ Sistema de recuperación de contraseña completamente funcional
- ✅ Envío de correos de recuperación con soporte UTF-8
- ✅ Manejo de tokens seguros para restablecimiento de contraseña
- ✅ Sistema completo y robusto de envío de correos vía SMTP con PHPMailer
- ✅ Registro de actividad implementado con logs detallados
- ✅ Refactorización del módulo de recuperación (13/06/2025)
- ⚠️ Control de sesión única implementado parcialmente
- ❌ Integración con módulos de fuerza bruta pendiente

### 1.3 Características Pendientes
- Completar registro de actividad en base de datos (`registro_actividad`)
- Implementar control completo de sesión única con verificación de token cruzado
- Implementar la protección contra fuerza bruta
- Agregar control horario de login

---

## 2. Arquitectura del Sistema de Autenticación

### 2.1 Tablas Utilizadas
- **usuarios**: Almacena credenciales y datos básicos de usuarios
- **sesiones_activas**: Registro de sesiones activas, IP, navegador y estado
- **tokens_recuperacion**: Tokens temporales para recuperación de contraseña
- **registro_actividad**: Historial completo de acciones (implementación parcial)

### 2.2 Clases Principales
- **Sesion** (`app/utilidades/sesion.php`): Gestión de sesiones y autenticación
- **AutenticacionControlador** (`app/controladores/autenticacion_controlador.php`): Manejo de rutas de autenticación
- **RecuperacionServicio** (`app/servicios/recuperacion_servicio.php`): Lógica de recuperación de contraseñas
- **TokenRecuperacion** (`app/modelos/token_recuperacion_modelo.php`): Modelo para tokens de recuperación
- **ValidadorContrasena** (`app/utilidades/validador_contrasena.php`): Validación de complejidad de contraseñas
- **Correo** (`app/utilidades/correo.php`): Envío de correos con PHPMailer

---

## 3. Flujos de Usuario Implementados

### 3.1 Inicio de Sesión
1. Usuario accede a formulario de login
2. Introduce credenciales (correo y contraseña)
3. Sistema valida credenciales y genera token único de sesión
4. Si la validación es exitosa, se registra sesión en base de datos
5. Se almacenan datos relevantes en `$_SESSION`
6. Si está habilitado el control de sesión única, se cierran otras sesiones

### 3.2 Recuperación de Contraseña
1. **Solicitud de recuperación:**
   - Usuario solicita recuperación con su correo
   - Sistema genera token único (64 caracteres)
   - Se envía correo con enlace de recuperación
   - Se muestra mensaje genérico por seguridad

2. **Procesamiento del enlace:**
   - Usuario hace clic en el enlace del correo
   - Sistema verifica validez del token (existencia, expiración, uso previo)
   - Si es válido, muestra formulario para nueva contraseña
   - Si no es válido, muestra mensaje de error

3. **Cambio de contraseña:**
   - Usuario introduce nueva contraseña y confirmación
   - Sistema valida complejidad usando `ValidadorContrasena`
   - Si cumple requisitos, actualiza hash con `password_hash()`
   - Marca token como usado para prevenir reutilización
   - Muestra confirmación con enlace para iniciar sesión

---

## 4. Refactorización del Sistema de Recuperación (13/06/2025)

### 4.1 Mejoras Implementadas
- **Separación de responsabilidades**: Creación de `RecuperacionServicio` para centralizar lógica
- **Validación robusta**: Nueva clase `ValidadorContrasena` para verificar políticas de contraseñas
- **Mejor UX**: Interfaz mejorada con validación en tiempo real
- **Configurabilidad**: Requisitos de contraseñas configurables
- **Herramienta de diagnóstico**: Nueva herramienta para pruebas de recuperación

### 4.2 Diagrama de Flujo del Nuevo Proceso
```
Usuario solicita recuperación
↓
AutenticacionControlador → RecuperacionServicio
↓
TokenRecuperacion (modelo) → Genera token
↓
Correo (utilidad) → Envía email con enlace
↓
Usuario hace clic en el enlace
↓
AutenticacionControlador → RecuperacionServicio → Valida token
↓
Usuario introduce nueva contraseña
↓
ValidadorContrasena → Valida complejidad
↓
RecuperacionServicio → Actualiza contraseña
```

### 4.3 Mejoras de Seguridad
1. **Validación robusta**: Validación configurable de requisitos de contraseñas
2. **Mejor manejo de errores**: Todas las operaciones con try-catch para capturar excepciones
3. **Mejores logs**: Registro detallado para facilitar auditoría
4. **Limpieza automática**: Eliminación periódica de tokens expirados
5. **Soporte UTF-8 completo**: Corrección de problemas con caracteres especiales

---

## 5. Gestión de Sesiones Activas

### 5.1 Estado de Implementación
- ⚠️ Control básico de sesiones implementado en la clase Sesion
- ⚠️ Regeneración periódica de ID de sesión implementada
- ✅ Logs de actividad implementados como archivos
- ❌ Gestión en base de datos de sesiones activas pendiente
- ❌ Control de sesión única con expulsión pendiente
- ❌ Interfaz de administración de sesiones pendiente

### 5.2 Tabla `sesiones_activas`
| Campo         | Tipo        | Descripción                    |
|---------------|-------------|--------------------------------|
| id_sesion     | INT PK AI   | ID único                       |
| id_usuario    | INT (FK)    | Usuario relacionado            |
| fecha_inicio  | DATETIME    | Hora de inicio de sesión       |
| ip            | VARCHAR(45) | IP del cliente                 |
| user_agent    | TEXT        | Navegador y sistema operativo  |
| activa        | TINYINT(1)  | 1 = activa, 0 = cerrada manual |

### 5.3 Pendiente de Implementación
- Almacenamiento completo de sesiones activas en base de datos
- Expulsión de sesiones anteriores al iniciar nueva sesión
- Interfaz de administración para visualizar y forzar cierre de sesiones
- Redirección automática si la sesión ha sido cerrada por un administrador
- Registro completo de eventos de sesión en `registro_actividad`

---

## 6. Medidas de Seguridad Implementadas

### 6.1 Generación y Gestión de Tokens
- Uso de `random_bytes(32)` para máxima entropía
- Tokens de 64 caracteres hexadecimales (32 bytes aleatorios)
- Almacenamiento seguro en base de datos
- Expiración automática después de 24 horas
- Tokens de un solo uso (se marcan como usados)

### 6.2 Protección contra Ataques
- Validación CSRF en todos los formularios
- Mensajes genéricos que no revelan existencia de usuarios
- Limitación de un token activo por usuario
- Cookies seguras con atributos apropiados
- Regeneración periódica de ID de sesión (cada 30 minutos)

### 6.3 Protección de Contraseñas
- Validación de complejidad:
  - Mínimo 8 caracteres
  - Al menos una letra mayúscula
  - Al menos una letra minúscula
  - Al menos un número
- Almacenamiento con `password_hash()` y algoritmo seguro

---

## 7. Checklist de Tareas Pendientes

- [ ] Implementar registro completo en base de datos `registro_actividad`
- [ ] Completar control de sesión única con expulsión de sesiones anteriores
- [ ] Añadir protección contra fuerza bruta
- [ ] Implementar control horario de login
- [ ] Crear interfaz de administración para sesiones activas
- [ ] Implementar notificaciones por correo cuando se detecta inicio de sesión inusual
- [ ] Añadir pruebas unitarias para el sistema de autenticación
