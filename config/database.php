<?php
/**
 * Conexión a la base de datos
 * 
 * Este archivo establece la conexión global a la base de datos
 * utilizando MySQLi y la configuración definida en config.php
 * 
 * @package AUTOEXAM2
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar que las constantes de BD estén definidas
if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER')) {
    throw new Exception('Constantes de base de datos no definidas. Asegúrese de que config.php esté cargado.');
}

try {
    // Crear conexión MySQLi
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    // Verificar errores de conexión
    if ($mysqli->connect_error) {
        throw new Exception('Error de conexión MySQLi: ' . $mysqli->connect_error);
    }
    
    // Configurar charset
    $mysqli->set_charset(DB_CHARSET);
    
    // Establecer conexión global
    $GLOBALS['db'] = $mysqli;
    
    // Log de conexión exitosa (solo en desarrollo)
    if (defined('APP_DEBUG') && APP_DEBUG) {
        error_log('Conexión MySQLi a base de datos establecida correctamente');
    }
    
} catch (Exception $e) {
    // Log del error
    error_log('Error de conexión a base de datos: ' . $e->getMessage());
    
    // En producción, mostrar mensaje genérico
    if (!defined('APP_DEBUG') || !APP_DEBUG) {
        throw new Exception('Error de conexión a la base de datos. Contacte al administrador.');
    } else {
        // En desarrollo, mostrar error específico
        throw new Exception('Error de conexión a BD: ' . $e->getMessage());
    }
}

/**
 * Función helper para obtener la conexión de BD
 * 
 * @return mysqli Instancia de la conexión MySQLi
 */
function obtener_bd() {
    if (!isset($GLOBALS['db'])) {
        throw new Exception('Conexión a base de datos no inicializada');
    }
    return $GLOBALS['db'];
}

?>
