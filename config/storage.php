<?php
/**
 * Configuración de Almacenamiento - AUTOEXAM2
 * 
 * Define las rutas y configuración centralizada para el almacenamiento de archivos
 * 
 * @author Github Copilot
 * @version 1.0
 * @date 13/06/2025
 */

// Directorio raíz para todo el almacenamiento
define('STORAGE_PATH', ROOT_PATH . '/almacenamiento');

// Directorio para archivos de configuración (almacenamiento)
define('STORAGE_CONFIG_PATH', STORAGE_PATH . '/config');

// Directorios de logs
define('LOGS_PATH', STORAGE_PATH . '/logs');
define('APP_LOGS_PATH', LOGS_PATH . '/app');
define('ERROR_LOGS_PATH', LOGS_PATH . '/errores');
define('ACCESS_LOGS_PATH', LOGS_PATH . '/acceso');
define('SYSTEM_LOGS_PATH', LOGS_PATH . '/sistema');

// Directorios de caché
define('CACHE_PATH', STORAGE_PATH . '/cache');
define('APP_CACHE_PATH', CACHE_PATH . '/app');
define('VIEW_CACHE_PATH', CACHE_PATH . '/vistas');
define('DATA_CACHE_PATH', CACHE_PATH . '/datos');

// Directorios temporales
define('TMP_PATH', STORAGE_PATH . '/tmp');
define('TEMP_UPLOADS_PATH', TMP_PATH . '/uploads');
define('SESSION_PATH', TMP_PATH . '/sesiones');

// Directorios de subidas
define('UPLOADS_PATH', STORAGE_PATH . '/subidas');
define('IMAGES_PATH', UPLOADS_PATH . '/imagenes');
define('DOCUMENTS_PATH', UPLOADS_PATH . '/documentos');
define('EXAMS_PATH', UPLOADS_PATH . '/examenes');

// Rutas para avatares de usuario (públicas)
// Esta es la subruta relativa al directorio 'publico' donde se guardarán los avatares.
define('AVATARS_PUBLIC_SUBPATH', 'recursos/subidas/avatares');
// Esta es la ruta física completa en el servidor donde se almacenarán los avatares.
define('AVATARS_STORAGE_DIR', ROOT_PATH . '/publico/' . AVATARS_PUBLIC_SUBPATH);

// Directorios de copias de seguridad
define('BACKUP_PATH', STORAGE_PATH . '/copias');
define('DB_BACKUP_PATH', BACKUP_PATH . '/db');
define('SYSTEM_BACKUP_PATH', BACKUP_PATH . '/sistema');

/**
 * Asegura que existe un directorio y tiene los permisos adecuados
 * 
 * @param string $path Ruta del directorio a verificar/crear
 * @param int $permissions Permisos a aplicar (octal)
 * @return bool True si el directorio existe y tiene permisos adecuados
 */
function ensure_directory($path, $permissions = 0755) {
    if (!file_exists($path)) {
        if (!@mkdir($path, $permissions, true)) {
            error_log("Error: No se pudo crear el directorio: $path");
            return false;
        }
    } elseif (!is_dir($path)) {
        error_log("Error: La ruta existe pero no es un directorio: $path");
        return false;
    }
    
    if (!is_writable($path)) {
        if (!@chmod($path, $permissions)) {
            error_log("Error: No se pudieron establecer permisos en: $path");
            return false;
        }
    }
    
    return true;
}

/**
 * Inicializa la estructura de directorios de almacenamiento
 * 
 * @return bool True si la estructura se creó correctamente
 */
function initialize_storage_structure() {
    $directories = [
        LOGS_PATH,
        APP_LOGS_PATH,
        ERROR_LOGS_PATH,
        ACCESS_LOGS_PATH,
        SYSTEM_LOGS_PATH,
        CACHE_PATH,
        APP_CACHE_PATH,
        VIEW_CACHE_PATH,
        DATA_CACHE_PATH,
        TMP_PATH,
        TEMP_UPLOADS_PATH,
        SESSION_PATH,
        UPLOADS_PATH,
        IMAGES_PATH,
        DOCUMENTS_PATH,
        EXAMS_PATH,
        BACKUP_PATH,
        DB_BACKUP_PATH,
        SYSTEM_BACKUP_PATH,
        AVATARS_STORAGE_DIR
    ];
    
    $success = true;
    
    foreach ($directories as $directory) {
        if (!ensure_directory($directory)) {
            $success = false;
        }
    }
    
    // Crear archivos .gitkeep para mantener estructura en git
    foreach ($directories as $directory) {
        $gitkeep = "$directory/.gitkeep";
        if (!file_exists($gitkeep)) {
            @file_put_contents($gitkeep, '# Este archivo existe para mantener la estructura de directorios en Git');
        }
    }
    
    return $success;
}

/**
 * Obtiene la ruta completa para un archivo de log específico
 * 
 * @param string $name Nombre base del archivo de log
 * @param string $type Tipo de log (app, error, access, system)
 * @return string Ruta completa del archivo
 */
function get_log_path($name, $type = 'app') {
    $base_path = APP_LOGS_PATH;
    
    switch ($type) {
        case 'error':
            $base_path = ERROR_LOGS_PATH;
            break;
        case 'access':
            $base_path = ACCESS_LOGS_PATH;
            break;
        case 'system':
            $base_path = SYSTEM_LOGS_PATH;
            break;
    }
    
    ensure_directory($base_path);
    return $base_path . '/' . $name . '.log';
}

/**
 * Registra un mensaje en un archivo de log
 * 
 * @param string $message Mensaje a registrar
 * @param string $log_name Nombre del archivo de log
 * @param string $type Tipo de log (app, error, access, system)
 * @return bool True si se registró correctamente
 */
function log_message($message, $log_name = 'app', $type = 'app') {
    $log_path = get_log_path($log_name, $type);
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $log_entry = "[$timestamp][$ip] $message" . PHP_EOL;
    
    return @file_put_contents($log_path, $log_entry, FILE_APPEND) !== false;
}

// Inicializar la estructura de almacenamiento al cargar este archivo
initialize_storage_structure();
