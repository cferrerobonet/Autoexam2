<?php
// filepath: /Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/config/config.php

/**
 * Configuración global de AUTOEXAM2
 * 
 * Este archivo carga la configuración desde el archivo .env
 * y define las constantes necesarias para el sistema.
 */

// Cargar la biblioteca para gestionar variables de entorno
require_once ROOT_PATH . '/app/utilidades/env.php';

// Cargar variables de entorno desde el archivo .env
$env_path = ROOT_PATH . '/.env';
if (!Env::cargar($env_path)) {
    error_log("Error: No se pudo cargar el archivo .env desde: $env_path");
    die('Error de configuración del sistema. Contacte al administrador.');
}

// Base URL del sistema (sin slash final)
// Detectar automáticamente en entornos de desarrollo o usar .env en producción
$base_url_env = Env::obtener('BASE_URL');

/**
 * Función para determinar si la aplicación se está ejecutando en un entorno de desarrollo local
 * 
 * @return bool True si es entorno de desarrollo, False si es producción
 */
function is_development_environment() {
    return (isset($_SERVER['HTTP_HOST']) && 
           (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
            strpos($_SERVER['HTTP_HOST'], '::1') !== false ||
            preg_match('/:\d+$/', $_SERVER['HTTP_HOST'])));
}

// Detectar si estamos en un entorno de desarrollo local
$is_local_dev = is_development_environment();

if ($is_local_dev || empty($base_url_env)) {
    // En desarrollo o si no hay BASE_URL definida, detectar automáticamente
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_path = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // Detectar si estamos ejecutando desde el directorio publico
    $is_from_public = strpos($script_path, '/publico/') !== false || strpos(__DIR__, '/publico') !== false;
    
    if ($is_from_public) {
        // Si estamos en publico, la base URL es simplemente el host
        $base_url_env = $protocol . '://' . $host;
    } else {
        // Si estamos en la raíz del proyecto, mantener la ruta actual
        $script_dir = dirname($script_path);
        $base_path = $script_dir !== '/' ? $script_dir : '';
        $base_url_env = $protocol . '://' . $host . $base_path;
    }
}
define('BASE_URL', $base_url_env);

// Configuración de la base de datos
define('DB_HOST', Env::obtener('DB_HOST', 'localhost')); // IONOS no permite conexiones remotas
define('DB_NAME', Env::obtener('DB_NAME', 'autoexam2'));
define('DB_USER', Env::obtener('DB_USER'));
define('DB_PASS', Env::obtener('DB_PASS'));
define('DB_PORT', Env::obtener('DB_PORT', '3306'));
define('DB_CHARSET', Env::obtener('DB_CHARSET', 'utf8mb4'));

// Configuración SMTP
define('SMTP_HOST', Env::obtener('SMTP_HOST'));
define('SMTP_USER', Env::obtener('SMTP_USER'));
define('SMTP_PASS', Env::obtener('SMTP_PASS'));
define('SMTP_PORT', Env::obtener('SMTP_PORT', '587'));
define('SMTP_SECURE', Env::obtener('SMTP_SECURE', 'tls')); // tls o ssl
// Usar dominio de producción para construir el correo por defecto
$default_domain = Env::obtener('DOMINIO_PRODUCCION', '');
$default_email = !empty($default_domain) ? 'no-reply@' . $default_domain : 'no-reply@example.com';
define('SMTP_FROM', Env::obtener('SMTP_FROM', $default_email));
define('SMTP_FROM_NAME', Env::obtener('SMTP_FROM_NAME', Env::obtener('SYSTEM_NAME', 'AUTOEXAM2')));

// Configuración SFTP/FTP
define('FTP_HOST', Env::obtener('FTP_HOST'));
define('FTP_USER', Env::obtener('FTP_USER'));
define('FTP_PASS', Env::obtener('FTP_PASS'));
define('FTP_PORT', Env::obtener('FTP_PORT', '21'));
define('FTP_PATH', Env::obtener('FTP_PATH', '/archivos/')); // Ruta base
define('FTP_SECURE', Env::obtener('FTP_SECURE', false)); // true para SFTP, false para FTP

// Modo de mantenimiento
define('MODO_MANTENIMIENTO', Env::obtener('MODO_MANTENIMIENTO', false));

// Configuración de seguridad
define('HASH_COST', Env::obtener('HASH_COST', 12)); // Coste del algoritmo de hash para contraseñas
define('SESSION_LIFETIME', Env::obtener('SESSION_LIFETIME', 7200)); // Tiempo de vida de sesión en segundos (2 horas)

// Configuración de protección contra fuerza bruta
define('FB_MAX_INTENTOS', Env::obtener('FB_MAX_INTENTOS', 5)); // Número máximo de intentos fallidos antes del bloqueo
define('FB_TIEMPO_BLOQUEO', Env::obtener('FB_TIEMPO_BLOQUEO', 30)); // Tiempo de bloqueo en minutos

// Configuración de administrador y credenciales de fallback
// Usar el dominio de producción para construir el email por defecto
$default_domain = Env::obtener('DOMINIO_PRODUCCION', '');
$default_admin_email = !empty($default_domain) ? 'admin@' . $default_domain : 'admin@example.com';
define('ADMIN_EMAIL', Env::obtener('ADMIN_EMAIL', $default_admin_email));
// Generar una contraseña aleatoria por defecto si no se proporciona
$default_password = Env::obtener('ADMIN_PASSWORD', '');
if (empty($default_password)) {
    $default_password = bin2hex(random_bytes(8)); // 16 caracteres aleatorios
    error_log("ALERTA: Se ha generado una contraseña de administrador aleatoria. Debería establecer una contraseña permanente en el archivo .env");
}
define('ADMIN_PASSWORD', $default_password);

// Configuración para entorno de producción
define('DOMINIO_PRODUCCION', Env::obtener('DOMINIO_PRODUCCION', 'autoexam.epla.es'));

// Configuración de tiempos de sesión
define('SESSION_REGENERATION_TIME', Env::obtener('SESSION_REGENERATION_TIME', 1800)); // 30 minutos por defecto
define('TOKEN_VALIDITY_TIME', Env::obtener('TOKEN_VALIDITY_TIME', 3600)); // 60 minutos por defecto

// Configuración de diagnóstico
define('ADMIN_OVERRIDE_TOKEN', Env::obtener('ADMIN_OVERRIDE_TOKEN', 'admin_override'));
define('ADMIN_NOMBRE', Env::obtener('ADMIN_NOMBRE', 'Administrador'));
define('ADMIN_APELLIDOS', Env::obtener('ADMIN_APELLIDOS', 'Sistema'));
define('ADMIN_ROL', Env::obtener('ADMIN_ROL', 'admin'));

// Nombre del sistema
define('SYSTEM_NAME', Env::obtener('SYSTEM_NAME', 'AUTOEXAM2'));
define('SYSTEM_EMAIL_PREFIX', Env::obtener('SYSTEM_EMAIL_PREFIX', 'AUTOEXAM2 -'));

// Configuración de archivos
define('MAX_UPLOAD_SIZE', Env::obtener('MAX_UPLOAD_SIZE', 5242880)); // Tamaño máximo de subida (5MB)
define('ALLOWED_EXTENSIONS', Env::obtener('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx')); // Extensiones permitidas

// Inicialización del sistema
error_reporting(E_ALL);
$debug_mode = Env::obtener('DEBUG', false);
ini_set('display_errors', $debug_mode ? 1 : 0); // Mostrar errores solo en modo debug
ini_set('log_errors', 1);
// Usar rutas de almacenamiento centralizadas
require_once ROOT_PATH . '/config/storage.php'; 
ini_set('error_log', ERROR_LOGS_PATH . '/php_errors.log');

// Configurar zona horaria
$timezone = Env::obtener('TIMEZONE', 'Europe/Madrid');
date_default_timezone_set($timezone);

// Versión del sistema (desde .env o valores por defecto)
define('SISTEMA_VERSION', Env::obtener('SISTEMA_VERSION', '1.2'));
// Usar la fecha actual si no se proporciona una fecha específica
$fecha_por_defecto = date('Y-m-d');
define('SISTEMA_FECHA', Env::obtener('SISTEMA_FECHA', $fecha_por_defecto));

/**
 * Función para registrar actividad en el sistema
 * Esta función será reemplazada por una implementación real
 * cuando se configure la base de datos
 */
function registrarActividad($idUsuario, $tipoAccion, $descripcion, $moduloAfectado = null, $elementoAfectado = null) {
    // Implementar cuando se tenga acceso a la base de datos
    // Por ahora, registramos en un archivo de log usando la función de logging centralizada
    ensure_directory(SYSTEM_LOGS_PATH);
    
    $fecha = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
    
    $mensaje = "$fecha | Usuario ID: $idUsuario | $tipoAccion | $descripcion | Módulo: $moduloAfectado | IP: $ip\n";
    
    file_put_contents(
        SYSTEM_LOGS_PATH . '/registro_actividad.log',
        $mensaje,
        FILE_APPEND
    );
}

// Establecer conexión a la base de datos
require_once __DIR__ . '/database.php';
