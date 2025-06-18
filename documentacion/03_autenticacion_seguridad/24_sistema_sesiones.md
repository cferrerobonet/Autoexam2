# 24 - Sistema de Gestión de Sesiones

**Implementado y funcional** ✅  
**Ubicación:** `app/utilidades/sesion.php`, `app/utilidades/sesion_clean.php`, `app/utilidades/verifica_sesiones.php`  
**Base de datos:** Tabla `sesiones_activas`

---

## 🎯 Objetivos del sistema

- Proporcionar un manejo robusto y seguro de sesiones de usuario
- Permitir cerrar sesión en todos los dispositivos o en uno específico
- Detectar y gestionar sesiones inactivas o expiradas
- Monitorizar la actividad de usuarios por seguridad
- Prevenir el secuestro de sesiones y otros ataques

---

## 🧱 Arquitectura del Sistema

### Componentes principales

```
app/utilidades/sesion.php             # Clase principal de gestión de sesiones
app/utilidades/sesion_clean.php       # Script para limpieza de sesiones antiguas
app/utilidades/verifica_sesiones.php  # Verificador de sesiones activas

app/modelos/sesion_activa_modelo.php  # Modelo para gestionar sesiones en BD
app/controladores/sesiones_activas_controlador.php  # Controlador para gestión de sesiones
```

### Estructura de base de datos

```sql
CREATE TABLE IF NOT EXISTS `sesiones_activas` (
  `id_sesion` INT AUTO_INCREMENT PRIMARY KEY,
  `id_usuario` INT NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `php_session_id` VARCHAR(64),
  `fecha_inicio` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `ultima_actividad` DATETIME,
  `fecha_fin` DATETIME,
  `ip` VARCHAR(45),
  `user_agent` TEXT,
  `activa` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
)
```

---

## 🔄 Flujo de Gestión de Sesiones

### Inicio de Sesión

1. Usuario ingresa credenciales correctamente
2. Se genera un token seguro único con `bin2hex(random_bytes(32))`
3. Se crea o regenera la sesión PHP
4. Se registra en la tabla `sesiones_activas`:
   - ID de usuario
   - Token generado
   - ID de sesión PHP
   - IP y User-Agent del cliente
5. Se almacena el token en la sesión PHP y una cookie segura (opcional)

### Durante la sesión activa

1. En cada petición, `verifica_sesiones.php` valida:
   - Existencia del token en sesión
   - Coincidencia con registro en base de datos
   - Que la sesión esté marcada como activa
   - Que no haya expirado
2. Se actualiza `ultima_actividad` para rastrear actividad
3. Si la validación falla, se fuerza cierre de sesión

### Cierre de sesión

1. **Cierre individual**: Marca la sesión actual como inactiva
2. **Cierre global**: Marca todas las sesiones del usuario como inactivas
3. Se destruye la sesión PHP y se elimina la cookie de token
4. Se registra la fecha de fin de sesión

---

## 📊 Funciones de Administración

El sistema permite varias funciones administrativas:

1. **Listado de sesiones activas**: Ver todas las sesiones activas del usuario
2. **Cierre forzado**: Administradores pueden cerrar sesiones de cualquier usuario
3. **Estadísticas**: Seguimiento de frecuencia de acceso y patrones
4. **Alertas de seguridad**: Notificación de múltiples sesiones o accesos sospechosos

---

## ⚙️ Configuración del Sistema

El sistema de sesiones se puede configurar con estas opciones:

- **Duración máxima**: Tiempo máximo permitido para una sesión (por defecto: 12 horas)
- **Tiempo de inactividad**: Tiempo tras el cual una sesión inactiva expira (por defecto: 30 minutos)
- **Validación de IP**: Activa/desactiva la validación de IP (puede causar problemas con IPs dinámicas)
- **Sesiones concurrentes**: Número máximo de sesiones simultáneas permitidas

---

## 🔄 Mantenimiento de Sesiones

El sistema incluye un proceso automático de limpieza:

1. Script `sesion_clean.php` ejecutado periódicamente:
   - Marca como inactivas las sesiones expiradas
   - Elimina registros antiguos (mayores a 30 días)
   - Mantiene el historial de sesiones recientes

2. El proceso se puede ejecutar:
   - Mediante un cronjob programado
   - Al inicio de sesión de un administrador
   - En momentos de baja carga del servidor

---

## 💻 Uso para Desarrolladores

### Verificar sesión activa

```php
// Verificar si hay una sesión activa
if (!SesionHelper::esUsuarioAutenticado()) {
    header('Location: ' . BASE_URL . '/autenticacion/iniciar');
    exit;
}

// Obtener datos del usuario en sesión
$id_usuario = $_SESSION['id_usuario'];
$rol_usuario = $_SESSION['rol'];
```

### Trabajar con múltiples sesiones

```php
// Obtener todas las sesiones activas del usuario
$modelo = new SesionActivaModelo();
$sesiones = $modelo->obtenerSesionesActivas($id_usuario);

// Cerrar todas las sesiones excepto la actual
$modelo->cerrarOtrasSesiones($id_usuario, $_SESSION['token']);
```

---

## 🔒 Seguridad Implementada

El sistema implementa varias medidas de seguridad:

1. **Tokens aleatorios**: Generados con `random_bytes()` para máxima entropía
2. **Regeneración de ID**: Previene ataques de fijación de sesión
3. **Validación múltiple**: Combina token, ID de sesión, IP y User-Agent
4. **Registro completo**: Todas las operaciones quedan registradas para auditoría
5. **Expiración automática**: Las sesiones inactivas o antiguas expiran automáticamente
6. **Cookies seguras**: Implementación opcional con flags HttpOnly y Secure
