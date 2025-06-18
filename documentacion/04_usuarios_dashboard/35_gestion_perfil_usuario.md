# 35 – Gestión de Perfil de Usuario

**Implementado y funcional** ✅  
**Controlador:** `app/controladores/perfil_controlador.php`  
**Vistas:** `app/vistas/perfil/`  

---

## 🎯 Objetivos del módulo

- Permitir a los usuarios gestionar sus datos personales
- Visualizar y controlar sesiones activas propias
- Cerrar sesiones específicas de otros dispositivos
- Mantener seguridad con validación CSRF
- Acceso restringido solo al propietario del perfil

---

## 🧱 Arquitectura MVC

| Componente | Ruta | Estado |
|------------|------|--------|
| Controlador | `app/controladores/perfil_controlador.php` | ✅ Implementado |
| Vista principal | `app/vistas/perfil/index.php` | ✅ Implementado |
| Vista sesiones | `app/vistas/perfil/sesiones.php` | ✅ Implementado |
| Modelo usuario | `app/modelos/usuario_modelo.php` | ✅ Implementado |
| Utilidad sesión | `app/utilidades/sesion.php` | ✅ Implementado |

---

## 🔐 Funcionalidades Implementadas

### Vista Principal del Perfil (`/perfil`)
- **Método:** `index()`
- **Funcionalidad:** Muestra y permite editar datos del perfil
- **Seguridad:** Validación de sesión activa
- **Datos:** Información personal del usuario logueado

### Gestión de Sesiones Activas (`/perfil/sesiones`)
- **Método:** `sesiones()`
- **Funcionalidad:** Lista todas las sesiones activas del usuario
- **Características:**
  - Muestra fecha de inicio
  - Muestra IP de origen
  - Muestra navegador/dispositivo
  - Identifica sesión actual
  - Permite cerrar sesiones específicas

### Cerrar Sesión Específica (`/perfil/cerrarSesion`)
- **Método:** `cerrarSesion()`
- **Funcionalidad:** Cierra una sesión específica del usuario
- **Validaciones:**
  - Token CSRF obligatorio
  - No permite cerrar sesión actual
  - Solo sesiones propias del usuario
  - Verificación de token de sesión válido

---

## 🛡️ Seguridad Implementada

### Validación de Sesión
```php
if (!$this->sesion->validarSesionActiva()) {
    header('Location: ' . BASE_URL . '/autenticacion/login');
    exit;
}
```

### Protección CSRF
```php
if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Error de validación de seguridad';
    header('Location: ' . BASE_URL . '/perfil/sesiones');
    exit;
}
```

### Protección de Sesión Actual
```php
if ($token === $_SESSION['token_sesion']) {
    $_SESSION['error'] = 'No puede cerrar su sesión actual desde aquí. Utilice "Cerrar sesión"';
    header('Location: ' . BASE_URL . '/perfil/sesiones');
    exit;
}
```

---

## 📋 Flujo de Funcionamiento

### 1. Acceso al Perfil
1. Usuario autenticado accede a `/perfil`
2. Controlador valida sesión activa
3. Carga datos del usuario desde BD
4. Muestra formulario de edición de perfil
5. Incluye token CSRF para seguridad

### 2. Gestión de Sesiones
1. Usuario accede a `/perfil/sesiones`
2. Sistema obtiene todas las sesiones del usuario
3. Muestra listado con detalles de cada sesión
4. Marca sesión actual como no cerrable
5. Proporciona botones para cerrar otras sesiones

### 3. Cierre de Sesión Específica
1. Usuario hace clic en "Cerrar sesión" de una sesión específica
2. Sistema valida token CSRF
3. Verifica que no sea la sesión actual
4. Elimina sesión de la base de datos
5. Redirige con mensaje de confirmación

---

## 🗃️ Interacción con Base de Datos

### Tabla `usuarios`
- Consulta datos del usuario para mostrar en perfil
- Actualiza información personal modificada

### Tabla `sesiones_activas`
- Consulta sesiones activas del usuario específico
- Elimina sesiones cerradas por el usuario
- Mantiene integridad referencial

---

## 🎨 Características de UI/UX

### Vista de Perfil
- Formulario pre-rellenado con datos actuales
- Campos de edición seguros
- Validación visual de campos
- Mensajes de éxito/error

### Vista de Sesiones
- Tabla responsive con información de sesiones
- Identificación visual de sesión actual
- Botones de acción diferenciados
- Iconos representativos (dispositivo, navegador)

---

## 🪵 Logs y Monitorización

### Eventos Registrados
- Acceso a perfil de usuario
- Modificación de datos personales
- Visualización de sesiones activas
- Cierre de sesiones específicas
- Intentos de acceso no autorizado

### Ubicación de Logs
- Logs de acceso: `/almacenamiento/logs/acceso/`
- Logs de actividad: `/almacenamiento/logs/app/`
- Logs de errores: `/almacenamiento/logs/errores/`

---

## 🔄 Integración con Otros Módulos

### Sistema de Autenticación
- Utiliza validación de sesión centralizada
- Comparte tokens CSRF del sistema
- Integrado con logout general

### Sistema de Sesiones
- Utiliza modelo de sesiones activas
- Mantiene coherencia con gestión de sesiones
- Integrado con verificación de sesiones

### Dashboard por Rol
- Accesible desde menú de usuario
- Integrado en navegación principal
- Respeta permisos por rol

---

## ✅ Checklist de Implementación

- [x] Controlador de perfil funcional
- [x] Validación de sesión activa
- [x] Vista de edición de perfil
- [x] Gestión de sesiones propias
- [x] Cierre de sesiones específicas
- [x] Protección CSRF completa
- [x] Validación de tokens de sesión
- [x] Mensajes de error y éxito
- [x] Integración con sistema de logs
- [x] Responsive design

---

## 🚀 Mejoras Futuras Sugeridas

1. **Cambio de contraseña integrado**
   - Formulario de cambio de contraseña
   - Validación de contraseña actual
   - Aplicación de políticas de contraseña

2. **Foto de perfil**
   - Subida de avatar
   - Redimensionado automático
   - Integración con galería de imágenes

3. **Configuraciones personales**
   - Preferencias de idioma
   - Configuración de notificaciones
   - Tema de interfaz

4. **Actividad reciente**
   - Historial de accesos
   - Últimas acciones realizadas
   - Estadísticas de uso

---

📌 **Nota:** Este módulo está completamente implementado y funcional. La documentación ha sido creada basándose en el análisis del código existente.
