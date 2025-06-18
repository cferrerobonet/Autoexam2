# 23 – Registro de sesiones activas

Módulo para monitorizar desde el panel de administración las sesiones activas por usuario.

---

## 🎯 Objetivos clave del sistema

- Detectar suplantaciones, accesos simultáneos y posibles abusos de sesión  
- Validar el correcto cumplimiento de la política de sesión única por usuario  
- Permitir al administrador visualizar y forzar el cierre de sesiones activas  
- Registrar metadatos relevantes de cada inicio de sesión (IP, navegador, hora)

---

## 🔗 Dependencias funcionales

- `11_modulo_autenticacion.md` (Implementado parcialmente)
- `41_registro_actividad.md` (Implementado parcialmente mediante logs)
- `10_modulo_usuarios.md` (Implementado parcialmente)

---

## 🗃️ Tabla `sesiones_activas`

| Campo         | Tipo        | Descripción                    |
|---------------|-------------|--------------------------------|
| id_sesion     | INT PK AI   | ID único                       |
| id_usuario    | INT (FK)    | Usuario relacionado            |
| fecha_inicio  | DATETIME    | Hora de inicio de sesión       |
| ip            | VARCHAR(45) | IP del cliente                 |
| user_agent    | TEXT        | Navegador y sistema operativo  |
| activa        | TINYINT(1)  | 1 = activa, 0 = cerrada manual |

---

## 🧑‍💻 UI/UX

- Vista disponible en: `admin/sesiones_activas.php`  
- Tabla con filtros por usuario, IP y fecha  
- Botón para **forzar cierre** de sesión individual  
- Visualización en color si la sesión lleva activa más de X tiempo  

---

## 🔐 Estado actual de implementación

- ⚠️ Control básico de sesiones implementado en la clase Sesion
- ⚠️ Regeneración periódica de ID de sesión implementada
- ✅ Logs de actividad implementados como archivos
- ❌ Gestión en base de datos de sesiones activas pendiente
- ❌ Control de sesión única con expulsión pendiente
- ❌ Interfaz de administración de sesiones pendiente

## 🔐 Características pendientes de implementar

- Almacenamiento de sesiones activas en base de datos
- Al iniciar sesión desde un segundo navegador, forzar cierre de la anterior  
- Permitir a administradores forzar cierre de sesiones
- Redirección automática al login si la sesión ha sido cerrada por un administrador  
- Toda acción de cierre se registra en `registro_actividad` con causa y origen  

---

## ✅ Validación de datos

- Validar existencia del `id_usuario` al insertar  
- Validar que `ip` y `user_agent` estén presentes y correctamente formateados  
- Verificar que solo administradores pueden cerrar sesiones manualmente  

---

## 🪵 Manejo de errores y logs

- Cualquier error al cerrar sesión se registra en `/almacenamiento/logs/sesiones_error.log`  
- Si el cierre manual falla, se muestra mensaje al admin y se graba el intento  
- Todos los cierres se registran en `registro_actividad` con marca de tipo `forzado`  

---

## 🧪 Casos límite esperados

- Admin intenta cerrar sesión de un usuario ya desconectado → feedback sin error  
- Usuario activo en dos dispositivos → se cierra la anterior automáticamente  
- Usuario accede con sesión marcada como inactiva → redirección a login  
- Admin intenta cerrar su propia sesión desde el panel → se le bloquea la opción  

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

- [ ] Registrar cada login con IP, navegador y fecha  
- [ ] Insertar y actualizar registros en `sesiones_activas`  
- [ ] Mostrar sesiones activas en tabla accesible solo para admin  
- [ ] Permitir cierre manual con botón individual por fila  
- [ ] Redirigir automáticamente si la sesión ha sido cerrada  
- [ ] Registrar todos los eventos en `registro_actividad`  
- [ ] Proteger esta funcionalidad contra acceso no autorizado  

---

📌 A continuación, Copilot debe leer e implementar: `24_control_horario_login.md`
