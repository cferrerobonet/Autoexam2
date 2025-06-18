# Clase Env - Implementación de Variables de Entorno en AUTOEXAM2

## 🎯 Descripción

La clase `Env` es una implementación personalizada para la gestión de variables de entorno en AUTOEXAM2. Esta clase permite cargar configuraciones desde un archivo `.env`, facilita el acceso a estas variables de forma segura y proporciona valores por defecto para variables no definidas.

## 🧰 Características implementadas

- ✅ Carga de variables desde archivo `.env`
- ✅ Conversión automática de tipos (booleanos)
- ✅ Eliminación de comillas en valores
- ✅ Soporte para comentarios en el archivo `.env`
- ✅ Almacenamiento en múltiples formatos (`$_ENV` y `getenv()`)
- ✅ Valores por defecto para variables ausentes
- ✅ Establecimiento dinámico de variables en tiempo de ejecución

## 📋 Métodos implementados

| Método | Descripción | Parámetros |
|--------|-------------|------------|
| `cargar($path)` | Carga las variables desde el archivo .env | `$path`: Ruta al archivo .env |
| `obtener($clave, $valorPorDefecto)` | Obtiene el valor de una variable | `$clave`: Nombre de la variable<br>`$valorPorDefecto`: Valor por defecto si no existe |
| `existe($clave)` | Verifica si una variable existe | `$clave`: Nombre de la variable |
| `establecer($clave, $valor)` | Establece una variable en tiempo de ejecución | `$clave`: Nombre de la variable<br>`$valor`: Valor a establecer |

## 🔄 Uso en el sistema

La clase `Env` se utiliza principalmente en `config.php` para cargar todas las configuraciones del sistema desde el archivo `.env`. Esto permite:

1. Separar las credenciales sensibles del código
2. Facilitar el cambio de configuración sin modificar el código
3. Adaptar la configuración a diferentes entornos (desarrollo, producción)
4. Mantener un control centralizado de parámetros críticos

## 📝 Ejemplo de uso implementado

```php
// Cargar el archivo .env
Env::cargar(ROOT_PATH . '/.env');

// Obtener variables con valores por defecto
$host = Env::obtener('DB_HOST', 'localhost');
$debug = Env::obtener('DEBUG', false);
$max_upload = Env::obtener('MAX_UPLOAD_SIZE', 5242880);

// Verificar si existe una variable
if (Env::existe('SMTP_HOST')) {
    // Configurar SMTP
}

// Establecer una variable en tiempo de ejecución
Env::establecer('EJECUTANDO', true);
```

## 📊 Implementación actual en AUTOEXAM2

Actualmente, la clase `Env` está completamente implementada y se utiliza en:

- ✅ Configuración principal (`config.php`)
- ✅ Configuración de base de datos
- ✅ Configuración de SMTP
- ✅ Configuración de FTP/SFTP
- ✅ Configuración de seguridad

## 🔄 Variables principales soportadas

| Variable | Descripción | Valor por defecto |
|----------|-------------|-------------------|
| `DEBUG` | Modo de depuración | `false` |
| `DB_HOST` | Host de la base de datos | `localhost` |
| `DB_NAME` | Nombre de la base de datos | `autoexam2` |
| `DB_USER` | Usuario de la base de datos | - |
| `DB_PASS` | Contraseña de la base de datos | - |
| `DB_PORT` | Puerto de la base de datos | `3306` |
| `DB_CHARSET` | Charset de la base de datos | `utf8mb4` |
| `SMTP_HOST` | Host de SMTP | - |
| `SMTP_USER` | Usuario de SMTP | - |
| `SMTP_PASS` | Contraseña de SMTP | - |
| `SMTP_PORT` | Puerto de SMTP | `587` |
| `SMTP_SECURE` | Seguridad de SMTP (tls/ssl) | `tls` |
| `FTP_HOST` | Host de FTP/SFTP | - |
| `FTP_USER` | Usuario de FTP/SFTP | - |
| `FTP_PASS` | Contraseña de FTP/SFTP | - |
| `FTP_PORT` | Puerto de FTP/SFTP | `21` |
| `HASH_COST` | Coste del hash de contraseñas | `12` |
| `SESSION_LIFETIME` | Tiempo de vida de sesión (seg.) | `7200` |
| `TIMEZONE` | Zona horaria | `Europe/Madrid` |
| `SISTEMA_VERSION` | Versión del sistema | `1.2` |
