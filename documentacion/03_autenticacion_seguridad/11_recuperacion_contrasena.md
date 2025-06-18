# 11 - Recuperación de Contraseña en AUTOEXAM2

Este documento describe detalladamente el sistema de recuperación de contraseña implementado en AUTOEXAM2.

## 🎯 Objetivos clave

- Proporcionar un método seguro y confiable para recuperar el acceso a cuentas
- Implementar validación y expiración de tokens de recuperación
- Asegurar que el proceso es resistente a ataques
- Garantizar la correcta codificación de caracteres especiales en comunicaciones
- Proporcionar retroalimentación clara al usuario sobre el estado del proceso

---

## 🔄 Flujo del proceso de recuperación

### 1. Solicitud de recuperación
1. El usuario accede a la ruta `/autenticacion/recuperar`
2. Introduce su correo electrónico registrado
3. El sistema genera un token único vinculado al usuario
4. Se envía un correo electrónico con un enlace que incluye el token
5. El sistema muestra un mensaje genérico (por seguridad) indicando que se ha enviado el correo

### 2. Procesamiento del enlace
1. El usuario recibe el correo y hace clic en el enlace de recuperación
2. El enlace dirige a la ruta `/autenticacion/restablecer/{token}`
3. El sistema verifica la validez del token:
   - Existe en la base de datos
   - No ha sido utilizado previamente
   - No ha expirado (menos de 24 horas desde su creación)
   - Está asociado a un usuario activo
4. Si es válido, muestra el formulario para establecer una nueva contraseña
5. Si no es válido, muestra una página de error

### 3. Cambio de contraseña
1. El usuario introduce su nueva contraseña y la confirmación
2. El sistema valida la complejidad de la contraseña (longitud, mayúsculas, minúsculas, números)
3. Si cumple los requisitos, actualiza la contraseña del usuario usando hash seguro
4. Marca el token como usado para prevenir reutilización
5. Muestra una página de éxito con un enlace para iniciar sesión

---

## 📦 Componentes implementados

### AutenticacionControlador
- `recuperar()`: Gestiona la solicitud inicial de recuperación
  - Valida el token CSRF
  - Verifica existencia del usuario
  - Genera token de recuperación
  - Envía el correo con el enlace
  
- `restablecer($token)`: Procesa la validación del token y cambio de contraseña
  - Valida el token recibido
  - Presenta formulario de nueva contraseña
  - Valida complejidad de la contraseña
  - Actualiza la contraseña y finaliza el proceso

### TokenRecuperacion (Modelo)
- `crearToken($idUsuario)`: Genera un token seguro y lo almacena
- `desactivarTokensAnteriores($idUsuario)`: Invalida tokens previos
- `validarToken($token)`: Verifica validez y no expiración
- `marcarComoUsado($idToken)`: Desactiva tokens utilizados
- `limpiarTokensExpirados()`: Mantenimiento periódico

### Clase Correo
- `enviarRecuperacionContrasena($destinatario, $asunto, $cuerpo)`: Método especializado
- `generarPlantillaRecuperacion($datos)`: Genera el HTML del correo

### Vistas
- `recuperar.php`: Formulario inicial de solicitud
- `restablecer.php`: Formulario para nueva contraseña
- `restablecer_error.php`: Página de error para tokens inválidos
- `restablecer_exito.php`: Confirmación de cambio exitoso

---

## 🛡️ Medidas de seguridad implementadas

### Generación de tokens
- Uso de `random_bytes(32)` para máxima entropía
- Tokens de 64 caracteres hexadecimales (32 bytes aleatorios)
- Almacenamiento seguro en base de datos

### Protección contra ataques
- Validación CSRF en todos los formularios
- Mensajes genéricos que no revelan existencia de usuarios
- Limitación de un token activo por usuario
- Expiración automática después de 24 horas
- Tokens de un solo uso (se marcan como usados)

### Protección de contraseñas
- Validación de complejidad:
  - Mínimo 8 caracteres
  - Al menos una letra mayúscula
  - Al menos una letra minúscula
  - Al menos un número
- Almacenamiento con `password_hash()` y algoritmo seguro

### Protección contra enumeración
- Mismo mensaje de respuesta independientemente de si el correo existe
- Tiempos de respuesta constantes
- Logging detallado pero accesible solo para administradores

---

## 🗃️ Estructura de datos

### Tabla `tokens_recuperacion`

| Campo          | Tipo         | Descripción                           |
|----------------|--------------|---------------------------------------|
| id_token       | INT PK AI    | ID único del token                    |
| id_usuario     | INT (FK)     | ID del usuario asociado               |
| token          | VARCHAR(64)  | Token hexadecimal                     |
| fecha_creacion | DATETIME     | Timestamp de creación                 |
| usado          | TINYINT(1)   | 0=activo, 1=usado o expirado          |

### Consultas principales

```sql
-- Creación de un nuevo token
INSERT INTO tokens_recuperacion (id_usuario, token, fecha_creacion, usado) 
VALUES (:id_usuario, :token, NOW(), 0)

-- Invalidación de tokens previos
UPDATE tokens_recuperacion SET usado = 1 
WHERE id_usuario = :id_usuario AND usado = 0

-- Validación de token
SELECT t.id_token, t.id_usuario, t.fecha_creacion, 
       u.nombre, u.apellidos, u.correo, u.rol
FROM tokens_recuperacion t
JOIN usuarios u ON t.id_usuario = u.id_usuario
WHERE t.token = :token AND t.usado = 0 AND u.activo = 1

-- Marcar como usado
UPDATE tokens_recuperacion SET usado = 1 WHERE id_token = :id_token

-- Limpieza automática
UPDATE tokens_recuperacion 
SET usado = 1
WHERE usado = 0 AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 24 HOUR)
```

---

## 📧 Sistema de correos

### Plantilla de correo

El sistema utiliza una plantilla HTML con las siguientes características:
- Diseño responsivo compatible con la mayoría de clientes de correo
- Codificación UTF-8 explícita para caracteres especiales
- Versión alternativa en texto plano
- Variables personalizadas (nombre del usuario, enlace de recuperación)

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Recuperación de contraseña</title>
</head>
<body>
    <h1>Recuperación de contraseña</h1>
    <p>Hola {{nombre}},</p>
    <p>Has solicitado restablecer tu contraseña en AUTOEXAM2.</p>
    <p><a href="{{url}}">Haz clic aquí para crear una nueva contraseña</a></p>
    <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
    <p>Este enlace expirará en 24 horas.</p>
</body>
</html>
```

### Configuración de correo

La configuración SMTP se realiza mediante variables de entorno en el archivo `.env`:

```
SMTP_HOST=smtp.ejemplo.com
SMTP_USER=usuario@ejemplo.com
SMTP_PASS=contraseña_segura
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_FROM=no-reply@autoexam2.com
SMTP_FROM_NAME=AUTOEXAM2
```

---

## 🧪 Herramientas de diagnóstico

### Diagnóstico web
- `/publico/diagnostico/test_recuperacion.php`: Prueba el flujo completo
- `/publico/diagnostico/test_enlaces_recuperacion.php`: Verifica la validez de los enlaces
- `/publico/diagnostico/test_caracteres_especiales.php`: Prueba la codificación UTF-8
- `/publico/diagnostico/smtp_avanzado.php`: Diagnóstico avanzado de SMTP

### Diagnóstico por línea de comandos
```bash
php herramientas/diagnostico/test_recuperacion.php correo@ejemplo.com
```

### Registros (logs)
- Todos los eventos significativos se registran en `php_errors.log`
- El sistema registra intentos, éxitos, fracasos y errores técnicos
- Se incluyen datos como correo, IP, timestamp y detalles de error

---

## 🚀 Mejoras recientes implementadas

1. **Soporte UTF-8 completo**:
   - Corrección de problemas con caracteres especiales
   - Codificación base64 para headers de correo
   - Etiquetas meta charset en plantillas HTML

2. **Seguridad mejorada**:
   - Validación CSRF en todos los formularios
   - Verificación automática de expiración de tokens
   - Mensajes genéricos para evitar enumeración

3. **Manejo de errores**:
   - Captura y registro detallado de errores
   - Mensajes de usuario claros pero seguros
   - Fallback automático para envío de correos

4. **Herramientas de diagnóstico**:
   - Conjunto completo para troubleshooting
   - Interfaces web y CLI para diferentes entornos
   - Detección específica de problemas comunes

5. **Optimización de base de datos**:
   - Limpieza automática de tokens expirados
   - Índices para búsquedas eficientes
   - Cierre seguro de conexiones

---

## 📝 Código clave implementado

### Generación de token seguro
```php
// Generar token único y seguro
$token = bin2hex(random_bytes(32));
            
// Desactivar tokens anteriores del usuario
$this->desactivarTokensAnteriores($idUsuario);
            
// Insertar en la base de datos
$sql = "INSERT INTO tokens_recuperacion (id_usuario, token, fecha_creacion, usado) 
        VALUES (:id_usuario, :token, NOW(), 0)";
```

### Validación de token
```php
// Verificar si el token ha expirado (24 horas)
$fechaCreacion = new DateTime($resultado['fecha_creacion']);
$ahora = new DateTime();
$diferencia = $ahora->diff($fechaCreacion);
            
// Si han pasado más de 24 horas
if ($diferencia->days >= 1) {
    $this->marcarComoUsado($resultado['id_token']);
    return false;
}
```

### Validación de complejidad de contraseña
```php
// Validar complejidad de la contraseña
$contrasena = $_POST['nueva_contrasena'];
if (strlen($contrasena) < 8 || 
    !preg_match('/[A-Z]/', $contrasena) || 
    !preg_match('/[a-z]/', $contrasena) || 
    !preg_match('/[0-9]/', $contrasena)) {
    $datos['error'] = 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.';
    return;
}
```

---

## 📚 Documentación relacionada

- [Estado de autenticación general](05_autenticacion.md)
- [Solución de problemas de correo](solucion_problemas_correo.md)
- [Clase Correo](../09_configuracion_mantenimiento/clase_correo.md)
- [Variables de entorno](../09_configuracion_mantenimiento/variables_entorno.md)

---

Última actualización: 13 de junio de 2025
