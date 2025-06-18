# Documentación de la Clase Correo - AUTOEXAM2

Este documento detalla la implementación, funcionalidad y uso de la clase `Correo` en AUTOEXAM2, responsable de la gestión y envío de correos electrónicos en el sistema.

## 🌟 Características principales

- Soporte completo para envío de correos vía SMTP usando PHPMailer
- Codificación UTF-8 con soporte total para caracteres especiales
- Sistema de plantillas HTML para correos
- Múltiples métodos de envío con fallback automático
- Logging detallado para diagnóstico de problemas
- Modo de depuración para pruebas sin envío real
- Manejo de errores y excepciones robusto
- Compatibilidad con múltiples servidores y clientes de correo

## 📝 Descripción general

La clase `Correo` proporciona una capa de abstracción sobre PHPMailer para simplificar el envío de correos electrónicos en AUTOEXAM2. Está diseñada para gestionar automáticamente los detalles de configuración SMTP, codificación de caracteres, format de correos y diagnóstico de problemas.

La clase se encuentra en:
```
app/utilidades/correo.php
```

## 🔧 Configuración

### Variables de entorno requeridas

La clase `Correo` utiliza las siguientes variables definidas en el archivo `.env`:

```bash
# Servidor SMTP
SMTP_HOST=smtp.ejemplo.com
SMTP_PORT=587
SMTP_SECURE=tls     # Opciones: tls, ssl, o vacío
SMTP_USER=usuario@ejemplo.com
SMTP_PASS=contraseña_segura

# Remitente
SMTP_FROM=no-reply@autoexam2.com
SMTP_FROM_NAME=AUTOEXAM2

# Debugging
DEBUG=false         # En true, simula el envío pero no lo realiza
SMTP_DEBUG=0        # Niveles: 0-4, donde 0 es sin debug y 4 es máximo detalle
```

### Valores por defecto

Si algunas variables no están definidas, la clase utiliza estos valores por defecto:
- `SMTP_PORT`: 587
- `SMTP_SECURE`: 'tls'
- `SMTP_DEBUG`: 0
- `SMTP_FROM`: Intenta usar `SMTP_USER` como valor
- `SMTP_FROM_NAME`: Genera un nombre genérico basado en el dominio de correo

## 🧩 Métodos principales

### Constructor

```php
public function __construct()
```

Inicializa la configuración SMTP desde las variables de entorno y realiza validaciones básicas. Registra advertencias si hay configuraciones faltantes o problemáticas.

### Envío básico

```php
public function enviar($para, $asunto, $cuerpo, $adjuntos = [])
```

**Parámetros:**
- `$para`: String o array con direcciones de correo destinatarias
- `$asunto`: Asunto del correo
- `$cuerpo`: Contenido HTML del mensaje
- `$adjuntos`: Array opcional de rutas de archivo para adjuntar

**Retorno:**
- `bool`: True si el envío fue exitoso, False en caso contrario

### Envío especializado para recuperación

```php
public function enviarRecuperacionContrasena($destinatario, $asunto, $cuerpo)
```

Método optimizado específicamente para el envío de correos de recuperación de contraseña, con configuraciones específicas para garantizar la entrega y compatibilidad.

**Parámetros:**
- `$destinatario`: Dirección de correo del destinatario
- `$asunto`: Asunto del correo
- `$cuerpo`: Contenido HTML del correo

**Retorno:**
- `bool`: True si el envío fue exitoso, False en caso contrario

### Generación de plantillas

```php
public function generarPlantillaRecuperacion($datos)
```

Genera el HTML para correos de recuperación de contraseña, sustituyendo variables en una plantilla con los datos proporcionados.

**Parámetros:**
- `$datos`: Array asociativo con variables para la plantilla (nombre, url, etc.)

**Retorno:**
- `string`: HTML completo del correo

## 📊 Gestión de errores

### Sistema de logging

La clase implementa un sistema detallado de logging que registra:
- Inicialización y configuración cargada
- Intentos de envío (inicio y fin)
- Errores de configuración o validación
- Errores de conexión o autenticación SMTP
- Éxito o fracaso de cada operación

Todos los logs se escriben en:
```
almacenamiento/registros/php_errors.log
```

### Modo DEBUG

Cuando `DEBUG=true` en el archivo `.env`:
- Los correos no se envían realmente
- Se registran todos los detalles en los logs
- Se simula un envío exitoso para pruebas
- Se muestra información del destinatario y asunto en los logs

Ejemplo de log en modo DEBUG:
```
[13-Jun-2025 10:45:22 UTC] === INICIO ENVÍO DE CORREO ===
[13-Jun-2025 10:45:22 UTC] CORREO SIMULADO (modo DEBUG): Para: usuario@ejemplo.com, Asunto: Recuperación de contraseña en AUTOEXAM2
[13-Jun-2025 10:45:22 UTC] === FIN ENVÍO DE CORREO (SIMULADO) ===
```

## 🛠️ Implementación detallada

### Soporte UTF-8 completo

La clase implementa un manejo completo de UTF-8:
- `CharSet = 'UTF-8'` para PHPMailer
- `Encoding = 'base64'` para contenido
- Codificación de headers (From, Subject) para nombres y asuntos con caracteres especiales

```php
// Configuración UTF-8
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';

// Headers codificados para caracteres especiales
$mail->Subject = '=?utf-8?B?' . base64_encode($asunto) . '?=';
```

### Plantillas HTML y texto plano

Cada correo se envía en formato HTML con una versión alternativa en texto plano generada automáticamente:

```php
// Versión HTML
$mail->isHTML(true);
$mail->Body = $cuerpo;

// Versión texto plano alternativa
$mail->AltBody = strip_tags(str_replace('<br>', "\n", $cuerpo));
```

### Reintentos automáticos

La clase implementa un sistema de reintentos con diferentes configuraciones:

```php
// Si falla el primer intento con PHPMailer
if (!$resultado) {
    // Intentar con configuración alternativa
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $resultado = $mail->send();
    
    // Si aún falla, intentar con mail() nativo
    if (!$resultado) {
        // Fallback a mail() nativo de PHP
        $cabeceras = "MIME-Version: 1.0\r\n";
        $cabeceras .= "Content-type: text/html; charset=utf-8\r\n";
        $cabeceras .= "From: {$this->nombre} <{$this->de}>\r\n";
        
        $resultado = mail($para, $asunto, $cuerpo, $cabeceras);
    }
}
```

## ⚙️ Ejemplos de uso

### Envío básico

```php
require_once APP_PATH . '/utilidades/correo.php';

$correo = new Correo();
$resultado = $correo->enviar(
    'destinatario@ejemplo.com',
    'Prueba de correo',
    '<h1>Hola</h1><p>Este es un correo de prueba con caracteres especiales: áéíóúñ.</p>'
);

if ($resultado) {
    echo "Correo enviado con éxito";
} else {
    echo "Error al enviar el correo";
}
```

### Múltiples destinatarios

```php
$destinatarios = [
    'usuario1@ejemplo.com',
    'usuario2@ejemplo.com',
    'usuario3@ejemplo.com'
];

$correo = new Correo();
$resultado = $correo->enviar(
    $destinatarios,
    'Notificación para múltiples usuarios',
    '<p>Este correo ha sido enviado a varios destinatarios.</p>'
);
```

### Correo de recuperación con plantilla

```php
$correo = new Correo();

// Datos para la plantilla
$datos = [
    'nombre' => 'Juan Pérez',
    'url' => 'https://autoexam2.com/autenticacion/restablecer/abc123token'
];

// Generar HTML desde plantilla
$htmlCorreo = $correo->generarPlantillaRecuperacion($datos);

// Enviar usando método especializado
$resultado = $correo->enviarRecuperacionContrasena(
    'juanperez@ejemplo.com',
    'Recuperación de contraseña en AUTOEXAM2',
    $htmlCorreo
);
```

## 🧪 Diagnóstico y solución de problemas

### Herramientas de diagnóstico disponibles

- `/publico/diagnostico/test_smtp_debug.php`: Prueba básica de configuración SMTP
- `/publico/diagnostico/test_caracteres_especiales.php`: Verificación de codificación UTF-8
- `/publico/diagnostico/smtp_avanzado.php`: Pruebas con diferentes configuraciones
- `/publico/diagnostico/test_recuperacion.php`: Prueba del flujo completo de recuperación

### Problemas comunes y soluciones

1. **Caracteres especiales incorrectos**
   - Síntomas: Caracteres como tildes, eñes aparecen como símbolos extraños
   - Solución: Verificar que se usa la versión más reciente de la clase con soporte UTF-8 completo

2. **Error de autenticación SMTP**
   - Síntomas: "SMTP Error: Could not authenticate"
   - Solución: Verificar credenciales en .env y probar diferentes combinaciones de puerto/seguridad

3. **Tiempos de espera en conexión**
   - Síntomas: "SMTP connect() failed" o tiempos de espera largos
   - Solución: Verificar firewall, probar con puertos alternativos (465, 25)

4. **Correos en carpeta de spam**
   - Síntomas: Los correos llegan pero a la carpeta de spam
   - Solución: Configurar SPF/DKIM/DMARC si es posible, usar dominios verificados

5. **Errores de formato de correo**
   - Síntomas: "Invalid address" o problemas similares
   - Solución: Verificar que las direcciones de correo tienen formato correcto

## 📚 Compatibilidad

La clase ha sido probada y es compatible con:

### Servidores de correo
- Gmail SMTP (smtp.gmail.com)
- Outlook/Office 365 (smtp.office365.com)
- Sendgrid, Mailgun, Amazon SES
- IONOS, GoDaddy, cPanel (hosting compartido)

### Clientes de correo
- Gmail web e iOS/Android
- Outlook desktop y web
- Apple Mail
- Thunderbird
- Clientes móviles nativos

## 📝 Mejoras recientes implementadas

1. **Soporte UTF-8 mejorado**:
   - Codificación base64 para headers
   - Charset explícito en plantillas HTML

2. **Sistema de logging avanzado**:
   - Registro detallado de cada etapa
   - Información de conexión y configuración

3. **Plantillas responsivas**:
   - Diseño compatible con móviles
   - Mejor visualización en diversos clientes

4. **Manejo de errores**:
   - Mensajes específicos por tipo de error
   - Reintentos automáticos con configuraciones alternativas

5. **Métodos especializados**:
   - Funciones dedicadas para casos de uso comunes
   - Plantillas predefinidas para correos del sistema

## 📚 Documentación relacionada

- [Solución de problemas de correo](../03_autenticacion_seguridad/solucion_problemas_correo.md)
- [Recuperación de contraseña](../03_autenticacion_seguridad/11_recuperacion_contrasena.md)
- [Variables de entorno](./variables_entorno.md)

---

Última actualización: 13 de junio de 2025
