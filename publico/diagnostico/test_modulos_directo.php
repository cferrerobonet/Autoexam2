<?php
/**
 * Test directo del controlador de módulos - AUTOEXAM2
 * Prueba directa sin pasar por el ruteador
 */

// Configurar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Definir constantes necesarias
define('ROOT_PATH', dirname(__DIR__, 1));
define('APP_PATH', ROOT_PATH . '/app');

// Cargar configuración
require_once ROOT_PATH . '/config/config.php';

echo "<h1>Test Directo - Controlador de Módulos</h1>";
echo "<p><a href='index.php'>← Volver al índice de diagnósticos</a></p>";

// Iniciar sesión simulada
session_start();
$_SESSION['id_usuario'] = 1;
$_SESSION['rol'] = 'admin';
$_SESSION['autenticado'] = true;
$_SESSION['nombre'] = 'Admin Test';

echo "<p><strong>Sesión iniciada como:</strong> Admin (ID: 1)</p>";

// Capturar salida para evitar headers
ob_start();

try {
    echo "<h2>1. Cargando controlador de módulos...</h2>";
    
    // Cargar dependencias
    require_once APP_PATH . '/modelos/modulo_modelo.php';
    require_once APP_PATH . '/utilidades/sesion.php';
    require_once APP_PATH . '/controladores/modulos_controlador.php';
    
    echo "<p style='color: green;'>✓ Archivos cargados correctamente</p>";
    
    echo "<h2>2. Instanciando controlador...</h2>";
    $controlador = new ModulosControlador();
    echo "<p style='color: green;'>✓ Controlador instanciado</p>";
    
    echo "<h2>3. Probando método index()...</h2>";
    
    // Limpiar buffer anterior
    ob_clean();
    
    // Ejecutar método index
    $controlador->index();
    
    echo "<p style='color: green;'>✓ Método index() ejecutado sin errores</p>";
    
} catch (Exception $e) {
    // Limpiar buffer si hay error
    ob_clean();
    
    echo "<h2>Error capturado:</h2>";
    echo "<div style='background: #ffcccc; padding: 10px; border: 1px solid #ff0000; margin: 10px 0;'>";
    echo "<strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Trace:</strong><br><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

// Capturar y mostrar cualquier salida del controlador
$salida = ob_get_clean();
if (!empty($salida)) {
    echo "<h2>Salida del controlador:</h2>";
    echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc; margin: 10px 0;'>";
    echo "<pre>" . htmlspecialchars($salida) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Test completado:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='index.php'>← Volver al índice de diagnósticos</a></p>";
?>
