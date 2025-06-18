# Variables de Entorno en AUTOEXAM2

## Descripción

Este documento detalla todas las variables de entorno utilizadas en AUTOEXAM2, su propósito, valores por defecto y ubicación en la estructura del proyecto. Estas variables se configuran en el archivo `.env` en la raíz del proyecto.

## Variables de Base de Datos

| Variable | Descripción | Valor por defecto | Estado |
|----------|-------------|-------------------|--------|
| `DB_HOST` | Host de la base de datos | `localhost` | ✅ Implementado |
| `DB_NAME` | Nombre de la base de datos | `autoexam2` | ✅ Implementado |
| `DB_USER` | Usuario de la base de datos | - | ✅ Implementado |
| `DB_PASS` | Contraseña de la base de datos | - | ✅ Implementado |
| `DB_PORT` | Puerto de la base de datos | `3306` | ✅ Implementado |
| `DB_CHARSET` | Charset de la base de datos | `utf8mb4` | ✅ Implementado |

## Variables de Email (SMTP)

| Variable | Descripción | Valor por defecto | Estado |
|----------|-------------|-------------------|--------|
| `SMTP_HOST` | Servidor SMTP | - | ✅ Implementado |
| `SMTP_USER` | Usuario SMTP | - | ✅ Implementado |
| `SMTP_PASS` | Contraseña SMTP | - | ✅ Implementado |
| `SMTP_PORT` | Puerto SMTP | `587` | ✅ Implementado |
| `SMTP_SECURE` | Tipo de seguridad (tls/ssl) | `tls` | ✅ Implementado |
| `SMTP_FROM` | Dirección de correo remitente | `no-reply@autoexam.epla.es` | ✅ Implementado |
| `SMTP_FROM_NAME` | Nombre del remitente | `AUTOEXAM2` | ✅ Implementado |

## Variables de FTP/SFTP

| Variable | Descripción | Valor por defecto | Estado |
|----------|-------------|-------------------|--------|
| `FTP_HOST` | Host FTP/SFTP | - | ✅ Implementado |
| `FTP_USER` | Usuario FTP/SFTP | - | ✅ Implementado |
| `FTP_PASS` | Contraseña FTP/SFTP | - | ✅ Implementado |
| `FTP_PORT` | Puerto | `21` | ✅ Implementado |
| `FTP_PATH` | Ruta base | `/archivos/` | ✅ Implementado |
| `FTP_SECURE` | Usar SFTP seguro | `false` | ✅ Implementado |

## Variables de Seguridad

| Variable | Descripción | Valor por defecto | Estado |
|----------|-------------|-------------------|--------|
| `HASH_COST` | Coste algoritmo de hash | `12` | ✅ Implementado |
| `SESSION_LIFETIME` | Tiempo de vida de sesión (segundos) | `7200` | ✅ Implementado |
| `MAX_LOGIN_ATTEMPTS` | Máximo de intentos de login | `5` | ⚠️ No implementado completamente |
| `LOCKOUT_DURATION` | Duración del bloqueo (segundos) | `900` | ⚠️ No implementado completamente |

## Variables de Sistema

| Variable | Descripción | Valor por defecto | Estado |
|----------|-------------|-------------------|--------|
| `DEBUG` | Modo depuración | `false` | ✅ Implementado |
| `MODO_MANTENIMIENTO` | Sistema en mantenimiento | `false` | ✅ Implementado |
| `TIMEZONE` | Zona horaria | `Europe/Madrid` | ✅ Implementado |
| `BASE_URL` | URL base del sitio | - | ✅ Implementado |
| `SISTEMA_VERSION` | Versión del sistema | `1.2` | ✅ Implementado |
| `SISTEMA_FECHA` | Fecha de la versión | `2025-05-25` | ✅ Implementado |

## Variables de Archivo

| Variable | Descripción | Valor por defecto | Estado |
|----------|-------------|-------------------|--------|
| `MAX_UPLOAD_SIZE` | Tamaño máximo de subida (bytes) | `5242880` | ✅ Implementado |
| `ALLOWED_EXTENSIONS` | Extensiones permitidas | `jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx` | ✅ Implementado |

## Ejemplo de archivo .env

```
# Configuración de la base de datos
DB_HOST=localhost
DB_NAME=autoexam2
DB_USER=usuario_bd
DB_PASS=contraseña_bd
DB_PORT=3306
DB_CHARSET=utf8mb4

# Configuración SMTP
SMTP_HOST=smtp.ejemplo.com
SMTP_USER=usuario@ejemplo.com
SMTP_PASS=contraseña_smtp
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_FROM=no-reply@autoexam.epla.es
SMTP_FROM_NAME=AUTOEXAM2

# Configuración FTP
FTP_HOST=ftp.ejemplo.com
FTP_USER=usuario_ftp
FTP_PASS=contraseña_ftp
FTP_PORT=21
FTP_PATH=/archivos/
FTP_SECURE=false

# Configuración de seguridad
HASH_COST=12
SESSION_LIFETIME=7200
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_DURATION=900

# Configuración del sistema
DEBUG=false
MODO_MANTENIMIENTO=false
TIMEZONE=Europe/Madrid
BASE_URL=https://autoexam.epla.es
SISTEMA_VERSION=1.2
SISTEMA_FECHA=2025-05-25

# Configuración de archivos
MAX_UPLOAD_SIZE=5242880
ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx
```

## Uso en el código

Las variables se utilizan en el código a través de la clase `Env`:

```php
// Obtener una variable con valor por defecto
$debug = Env::obtener('DEBUG', false);

// Verificar si existe una variable
if (Env::existe('SMTP_HOST')) {
    // Configurar el servidor SMTP
}
```

O se accede a través de constantes definidas en `config.php`:

```php
// Usar constantes definidas
$host = DB_HOST;
$user = DB_USER;
$smtpFrom = SMTP_FROM;
```
