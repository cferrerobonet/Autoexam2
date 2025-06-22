<?php
/**
 * Test directo de acceso a exámenes
 */
session_start();

// Configurar el entorno
define('ROOT_PATH', __DIR__ . '/../..');
define('APP_PATH', ROOT_PATH . '/app');
define('BASE_URL', 'http://localhost/AUTOEXAM2');

require_once ROOT_PATH . '/config/config.php';

echo "<h1>Test de Acceso Directo a Exámenes</h1>";
echo "<p><a href='index.php'>← Volver</a></p>";

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    echo "<div style='color: red;'>No hay sesión activa. <a href='$BASE_URL/autenticacion/login'>Iniciar sesión</a></div>";
    exit;
}

echo "<h3>Información de Sesión:</h3>";
echo "<pre>";
echo "ID Usuario: " . ($_SESSION['id_usuario'] ?? 'No definido') . "\n";
echo "Rol: " . ($_SESSION['rol'] ?? 'No definido') . "\n";
echo "Nombre: " . ($_SESSION['nombre'] ?? 'No definido') . "\n";
echo "</pre>";

// Verificar rol
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'profesor') {
    echo "<div style='color: red;'>Rol no permitido para acceder a exámenes: " . $_SESSION['rol'] . "</div>";
    exit;
}

echo "<h3>Test de Controlador:</h3>";

try {
    // Simular la carga del controlador como lo haría el ruteador
    require_once APP_PATH . '/controladores/examenes_controlador.php';
    echo "<div style='color: green;'>✓ Controlador cargado exitosamente</div>";
    
    $controlador = new ExamenesControlador();
    echo "<div style='color: green;'>✓ Instancia de controlador creada</div>";
    
    // Verificar métodos
    $metodos = ['index', 'crear', 'editar', 'eliminar'];
    foreach ($metodos as $metodo) {
        if (method_exists($controlador, $metodo)) {
            echo "<div style='color: green;'>✓ Método $metodo disponible</div>";
        } else {
            echo "<div style='color: red;'>✗ Método $metodo NO disponible</div>";
        }
    }
    
    echo "<h3>Test de Ejecución del Método index():</h3>";
    
    // Capturar la salida
    ob_start();
    try {
        $controlador->index();
        $salida = ob_get_contents();
        ob_end_clean();
        
        echo "<div style='color: green;'>✓ Método index() ejecutado sin errores</div>";
        echo "<div style='color: blue;'>Longitud de salida: " . strlen($salida) . " caracteres</div>";
        
        // Verificar si contiene HTML válido
        if (strpos($salida, '<!DOCTYPE html') !== false) {
            echo "<div style='color: green;'>✓ Salida contiene HTML válido</div>";
        } else {
            echo "<div style='color: orange;'>⚠ Salida no contiene DOCTYPE HTML</div>";
        }
        
        // Verificar si contiene elementos esperados
        if (strpos($salida, 'examenes') !== false) {
            echo "<div style='color: green;'>✓ Salida contiene referencias a exámenes</div>";
        } else {
            echo "<div style='color: orange;'>⚠ Salida no contiene referencias a exámenes</div>";
        }
        
        echo "<h4>Muestra de la salida (primeros 500 caracteres):</h4>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars(substr($salida, 0, 500)) . (strlen($salida) > 500 ? '...' : '');
        echo "</pre>";
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "<div style='color: red;'>✗ Error al ejecutar index(): " . $e->getMessage() . "</div>";
        echo "<div style='color: red;'>Archivo: " . $e->getFile() . "</div>";
        echo "<div style='color: red;'>Línea: " . $e->getLine() . "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Error al cargar controlador: " . $e->getMessage() . "</div>";
    echo "<div style='color: red;'>Archivo: " . $e->getFile() . "</div>";
    echo "<div style='color: red;'>Línea: " . $e->getLine() . "</div>";
}

echo "<h3>Enlaces de Prueba:</h3>";
echo "<p><a href='$BASE_URL/examenes' target='_blank'>Ir a Gestión de Exámenes</a></p>";
echo "<p><a href='$BASE_URL/examenes/crear' target='_blank'>Crear Nuevo Examen</a></p>";
?>
