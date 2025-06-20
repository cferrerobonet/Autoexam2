<?php
/**
 * Test simplificado de módulos - AUTOEXAM2
 * Prueba solo la funcionalidad básica sin vistas
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

echo "<h1>Test Simplificado - Módulos</h1>";
echo "<p><a href='index.php'>← Volver al índice de diagnósticos</a></p>";

// Iniciar sesión simulada
session_start();
$_SESSION['id_usuario'] = 1;
$_SESSION['rol'] = 'admin';
$_SESSION['autenticado'] = true;

try {
    echo "<h2>1. Probando modelo ModuloModelo</h2>";
    
    require_once APP_PATH . '/modelos/modulo_modelo.php';
    $modelo = new ModuloModelo();
    
    echo "<p style='color: green;'>✓ Modelo instanciado correctamente</p>";
    
    echo "<h2>2. Probando obtenerTodos()</h2>";
    $resultado = $modelo->obtenerTodos(5, 1);
    
    echo "<p style='color: green;'>✓ Método ejecutado sin errores</p>";
    echo "<p><strong>Total registros:</strong> " . $resultado['total'] . "</p>";
    echo "<p><strong>Módulos encontrados:</strong> " . count($resultado['modulos']) . "</p>";
    
    if (count($resultado['modulos']) > 0) {
        echo "<h3>Módulos:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Título</th><th>Profesor</th><th>Estado</th></tr>";
        foreach ($resultado['modulos'] as $modulo) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($modulo['id_modulo'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($modulo['titulo'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars(($modulo['nombre'] ?? '') . ' ' . ($modulo['apellidos'] ?? '')) . "</td>";
            echo "<td>" . ($modulo['activo'] ? 'Activo' : 'Inactivo') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>3. Probando obtenerProfesores()</h2>";
    $profesores = $modelo->obtenerProfesores();
    echo "<p><strong>Profesores encontrados:</strong> " . count($profesores) . "</p>";
    
    if (count($profesores) > 0) {
        echo "<ul>";
        foreach ($profesores as $profesor) {
            echo "<li>" . htmlspecialchars($profesor['nombre']) . " " . htmlspecialchars($profesor['apellidos']) . "</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error capturado:</h2>";
    echo "<div style='background: #ffcccc; padding: 10px; border: 1px solid #ff0000; margin: 10px 0;'>";
    echo "<strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Trace:</strong><br><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Test completado:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='index.php'>← Volver al índice de diagnósticos</a></p>";
?>
