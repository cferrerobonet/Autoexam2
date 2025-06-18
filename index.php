<?php
// AUTOEXAM2 - Entrada principal del sistema (MVC activado)
// El instalador web automático está accesible en /instalador/ (la carpeta 'publico' es la raíz del dominio)
// Redirige a controlador principal o instalador si no está configurado

// Definir constante de la raíz del proyecto
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/publico');

// Cargar funciones auxiliares
require_once APP_PATH . '/utilidades/helpers.php';

// Cargar configuración de almacenamiento
require_once CONFIG_PATH . '/storage.php';

// Función simple para registrar eventos importantes del sistema
function log_sistema($mensaje, $tipo = 'info') {
    $log_message = "[$tipo] $mensaje";
    log_message($log_message, 'sistema', 'system');
}

// Intentar cargar configuración personalizada de PHP
$php_ini_custom = CONFIG_PATH . '/php.ini';
if (file_exists($php_ini_custom)) {
    @ini_set('memory_limit', '256M');
    @ini_set('post_max_size', '64M');
    @ini_set('upload_max_filesize', '32M');
    @ini_set('max_execution_time', '300');
    @ini_set('max_input_time', '300');
}

// Iniciar sesión si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si se está accediendo con parámetros de limpieza de caché, agregar headers anti-caché
if (isset($_GET['nocache']) || isset($_GET['force_reload'])) {
    header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    
    // Limpiar el buffer de salida si existe
    if (ob_get_level()) {
        ob_clean();
    }
}

// Verificar si el sistema está instalado
$env_file = ROOT_PATH . '/.env';
$lock_file = PUBLIC_PATH . '/instalador/.lock';
$config_file = CONFIG_PATH . '/config.php';

if (!file_exists($env_file) || !file_exists($lock_file) || !file_exists($config_file)) {
    // Registrar qué archivos están faltando
    $faltantes = [];
    if (!file_exists($env_file)) $faltantes[] = '.env';
    if (!file_exists($lock_file)) $faltantes[] = 'instalador/.lock';
    if (!file_exists($config_file)) $faltantes[] = 'config/config.php';
    
    log_sistema('Instalación incompleta detectada. Archivos faltantes: ' . implode(', ', $faltantes) . '. Redirigiendo al instalador.', 'warning');
    
    // Sistema no instalado completamente, redirigir al instalador utilizando URL absoluta
    $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocolo . '://' . $host;
    
    // Detectar si estamos ejecutando desde el directorio publico
    // Verificamos si existe el archivo .env en el directorio actual (significa que estamos en la raíz)
    // Si no existe aquí pero existe en el padre, significa que estamos en publico
    $is_from_public = !file_exists(ROOT_PATH . '/.env') && file_exists(dirname(ROOT_PATH) . '/.env');
    
    // Si no podemos determinar por archivos, usar la detección por DOCUMENT_ROOT
    if (!$is_from_public && isset($_SERVER['DOCUMENT_ROOT'])) {
        $doc_root = realpath($_SERVER['DOCUMENT_ROOT']);
        $current_dir = realpath(ROOT_PATH);
        $is_from_public = ($doc_root === $current_dir . '/publico');
    }
    
    // Construir la URL del instalador según el contexto
    if ($is_from_public) {
        // Si ejecutamos desde publico, el instalador está en /instalador/
        $instalador_url = $base_url . '/instalador/';
    } else {
        // Si ejecutamos desde la raíz del proyecto, necesitamos /publico/instalador/
        $script_name = dirname($_SERVER['SCRIPT_NAME']);
        $base_path = $script_name !== '/' ? $script_name : '';
        $instalador_url = $base_url . $base_path . '/publico/instalador/';
    }
    
    // Redireccionar con URL absoluta para evitar bucles
    header('Location: ' . $instalador_url);
    exit;
}

// Incluir archivo de configuración
require_once CONFIG_PATH . '/config.php';

// Comprobar modo mantenimiento
if (defined('MODO_MANTENIMIENTO') && MODO_MANTENIMIENTO == true && 
    (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin')) {
    // Redirigir a página de mantenimiento
    require_once APP_PATH . '/vistas/mantenimiento.php';
    exit;
}

// Incluir ruteador principal
require_once APP_PATH . '/controladores/ruteador.php';

// Inicializar el sistema MVC
$ruteador = new Ruteador();
$ruteador->procesarPeticion();
