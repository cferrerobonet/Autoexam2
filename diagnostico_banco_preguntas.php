<?php
/**
 * Script de diagnóstico para el banco de preguntas del profesor
 * Este archivo se elimina después de la prueba
 */

session_start();

// Simular sesión de profesor para pruebas
$_SESSION['id_usuario'] = 1;
$_SESSION['rol'] = 'profesor';
$_SESSION['nombre'] = 'Profesor Test';

// Cargar configuración
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/modelos/pregunta_banco_modelo.php';

try {
    echo "=== Diagnóstico del Banco de Preguntas para Profesor ===\n\n";
    
    $pregunta_banco = new PreguntaBanco();
    
    echo "1. Verificando conexión a la base de datos...\n";
    if (isset($GLOBALS['db']) && $GLOBALS['db']->ping()) {
        echo "✓ Conexión a BD exitosa\n\n";
    } else {
        echo "✗ Error de conexión a BD\n\n";
        exit(1);
    }
    
    echo "2. Probando obtener preguntas para profesor (ID: 1)...\n";
    $preguntas = $pregunta_banco->obtenerPorProfesor(1, []);
    
    if ($preguntas !== false) {
        echo "✓ Método obtenerPorProfesor funciona correctamente\n";
        echo "   Total de preguntas obtenidas: " . count($preguntas) . "\n\n";
        
        if (count($preguntas) > 0) {
            echo "3. Muestra de datos de la primera pregunta:\n";
            $primera = $preguntas[0];
            echo "   ID: " . $primera['id_pregunta'] . "\n";
            echo "   Tipo: " . $primera['tipo'] . "\n";
            echo "   Categoría: " . $primera['categoria'] . "\n";
            echo "   Enunciado: " . substr($primera['enunciado'], 0, 50) . "...\n\n";
        }
    } else {
        echo "✗ Error al obtener preguntas\n\n";
    }
    
    echo "4. Verificando estructura de tabla preguntas_banco...\n";
    $query = "DESCRIBE preguntas_banco";
    $result = $GLOBALS['db']->query($query);
    
    if ($result) {
        echo "✓ Tabla preguntas_banco existe\n";
        echo "   Campos disponibles:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   - " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "✗ Error al acceder a la tabla preguntas_banco\n";
    }
    
    echo "\n=== Fin del diagnóstico ===\n";
    
} catch (Exception $e) {
    echo "Error durante el diagnóstico: " . $e->getMessage() . "\n";
    error_log("Error en diagnóstico banco preguntas: " . $e->getMessage());
}
?>
