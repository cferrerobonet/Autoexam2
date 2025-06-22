<?php
// Script temporal para verificar estructura de tabla usuarios
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');

// Cargar configuración
require_once ROOT_PATH . '/config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    
    echo "Conectado a la base de datos: " . DB_NAME . "\n\n";
    
    // Verificar estructura de tabla usuarios
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Estructura de la tabla 'usuarios':\n";
    echo str_repeat("=", 50) . "\n";
    foreach ($columns as $column) {
        echo sprintf("%-20s %-15s %-10s %-5s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Default']
        );
    }
    
    // Verificar si existe la columna fecha_registro
    $columnas = array_column($columns, 'Field');
    if (in_array('fecha_registro', $columnas)) {
        echo "\n✓ La columna 'fecha_registro' existe\n";
    } else {
        echo "\n✗ La columna 'fecha_registro' NO existe - Esto es la causa del error\n";
        echo "Es necesario agregar esta columna para las estadísticas\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
