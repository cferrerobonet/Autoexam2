<?php
/**
 * Simulación de acceso a módulos - AUTOEXAM2
 * Reproduce el flujo completo desde la URL
 */

// Configurar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Simular el acceso real
$_GET['url'] = '../modulos';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "<h1>Simulación de acceso a módulos</h1>";
echo "<p><a href='index.php'>← Volver al índice de diagnósticos</a></p>";

// Definir constantes como en el index.php real
define('ROOT_PATH', dirname(__DIR__, 1));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/publico');

// Cargar configuración
require_once ROOT_PATH . '/config/config.php';

// Iniciar sesión
session_start();

// Simular usuario autenticado
$_SESSION['id_usuario'] = 1;
$_SESSION['rol'] = 'admin';
$_SESSION['autenticado'] = true;
$_SESSION['nombre'] = 'Admin Test';

echo "<p><strong>Sesión simulada:</strong> Admin (ID: 1)</p>";
echo "<p><strong>URL solicitada:</strong> " . ($_GET['url'] ?? 'No definida') . "</p>";

try {
    echo "<h2>Cargando ruteador...</h2>";
    
    // Cargar ruteador
    require_once APP_PATH . '/controladores/ruteador.php';
    
    $ruteador = new Ruteador();
    
    echo "<p style='color: green;'>✓ Ruteador cargado</p>";
    
    echo "<h2>Procesando petición...</h2>";
    
    // Capturar salida
    ob_start();
    
    // Procesar petición
    $ruteador->procesarPeticion();
    
    // Obtener salida
    $salida = ob_get_clean();
    
    echo "<p style='color: green;'>✓ Petición procesada sin errores</p>";
    echo "<p><strong>Salida generada:</strong> " . strlen($salida) . " caracteres</p>";
    
    // Mostrar solo el inicio de la salida para diagnóstico
    if (strlen($salida) > 0) {
        echo "<h3>Primeros 500 caracteres de la salida:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow-x: auto;'>";
        echo htmlspecialchars(substr($salida, 0, 500));
        if (strlen($salida) > 500) {
            echo "\n... [resto de la salida truncado]";
        }
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error capturado:</h2>";
    echo "<div style='background: #ffcccc; padding: 10px; border: 1px solid #ff0000; margin: 10px 0;'>";
    echo "<strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Simulación completada:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='index.php'>← Volver al índice de diagnósticos</a></p>";
?>
