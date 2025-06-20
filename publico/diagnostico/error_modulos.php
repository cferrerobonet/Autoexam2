<?php
/**
 * Diagnóstico de error en módulos - AUTOEXAM2
 * Verifica qué está causando el error 500 en el menú de módulos
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

echo "<h1>Diagnóstico de Módulos - AUTOEXAM2</h1>";
echo "<p><a href='index.php'>← Volver al índice de diagnósticos</a></p>";

// Función para mostrar resultados
function mostrar_resultado($nombre, $resultado, $detalle = '') {
    $color = $resultado ? 'green' : 'red';
    $icono = $resultado ? '✓' : '✗';
    echo "<p style='color: $color;'><strong>$icono $nombre:</strong> " . ($resultado ? 'OK' : 'ERROR') . "</p>";
    if (!empty($detalle)) {
        echo "<p style='margin-left: 20px; color: #666;'>$detalle</p>";
    }
}

// 1. Verificar constantes de base de datos
echo "<h2>1. Verificación de constantes de BD</h2>";
$constantes_bd = [
    'DB_HOST' => defined('DB_HOST') ? DB_HOST : 'No definida',
    'DB_NAME' => defined('DB_NAME') ? DB_NAME : 'No definida',
    'DB_USER' => defined('DB_USER') ? DB_USER : 'No definida',
    'DB_PASS' => defined('DB_PASS') ? (DB_PASS ? '[DEFINIDA]' : 'Vacía') : 'No definida',
    'DB_PORT' => defined('DB_PORT') ? DB_PORT : 'No definida',
    'DB_CHARSET' => defined('DB_CHARSET') ? DB_CHARSET : 'No definida'
];

foreach ($constantes_bd as $constante => $valor) {
    $ok = strpos($valor, 'No definida') === false && strpos($valor, 'Vacía') === false;
    mostrar_resultado($constante, $ok, $valor);
}

// 2. Verificar conexión a base de datos
echo "<h2>2. Verificación de conexión a BD</h2>";
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($mysqli->connect_error) {
        throw new Exception('Error de conexión: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset(DB_CHARSET);
    mostrar_resultado('Conexión MySQLi', true, "Conectado a " . DB_HOST . ":" . DB_PORT);
    
    // Verificar tablas necesarias
    echo "<h3>2.1 Verificación de tablas</h3>";
    $tablas_necesarias = ['modulos', 'usuarios', 'examenes'];
    foreach ($tablas_necesarias as $tabla) {
        $result = $mysqli->query("SHOW TABLES LIKE '$tabla'");
        $existe = $result && $result->num_rows > 0;
        mostrar_resultado("Tabla '$tabla'", $existe);
    }
    
    $mysqli->close();
} catch (Exception $e) {
    mostrar_resultado('Conexión MySQLi', false, $e->getMessage());
}

// 3. Verificar modelo de módulos
echo "<h2>3. Verificación del modelo ModuloModelo</h2>";
try {
    require_once APP_PATH . '/modelos/modulo_modelo.php';
    $modelo = new ModuloModelo();
    mostrar_resultado('Instanciar ModuloModelo', true);
    
    // Probar método obtenerTodos
    try {
        $resultado = $modelo->obtenerTodos(5, 1);
        $tiene_estructura = isset($resultado['modulos']) && isset($resultado['total']) && isset($resultado['paginas']);
        mostrar_resultado('Método obtenerTodos()', $tiene_estructura, 
            "Módulos encontrados: " . count($resultado['modulos'] ?? []));
    } catch (Exception $e) {
        mostrar_resultado('Método obtenerTodos()', false, $e->getMessage());
    }
    
} catch (Exception $e) {
    mostrar_resultado('Instanciar ModuloModelo', false, $e->getMessage());
}

// 4. Verificar controlador
echo "<h2>4. Verificación del controlador ModulosControlador</h2>";
try {
    // Simular sesión mínima
    session_start();
    $_SESSION['id_usuario'] = 1;
    $_SESSION['rol'] = 'admin';
    $_SESSION['autenticado'] = true;
    
    require_once APP_PATH . '/utilidades/sesion.php';
    require_once APP_PATH . '/controladores/modulos_controlador.php';
    
    $controlador = new ModulosControlador();
    mostrar_resultado('Instanciar ModulosControlador', true);
    
} catch (Exception $e) {
    mostrar_resultado('Instanciar ModulosControlador', false, $e->getMessage());
}

// 5. Verificar archivos de vista
echo "<h2>5. Verificación de archivos de vista</h2>";
$vistas_necesarias = [
    'app/vistas/admin/modulos/listar.php',
    'app/vistas/profesor/modulos/listar.php',
    'app/vistas/parciales/head_admin.php',
    'app/vistas/parciales/navbar_admin.php'
];

foreach ($vistas_necesarias as $vista) {
    $archivo = ROOT_PATH . '/' . $vista;
    $existe = file_exists($archivo);
    mostrar_resultado("Vista '$vista'", $existe, $existe ? 'Existe' : 'No encontrada');
}

echo "<hr>";
echo "<p><strong>Diagnóstico completado:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='index.php'>← Volver al índice de diagnósticos</a></p>";
?>
