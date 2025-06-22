<?php
// Script para agregar la columna fecha_registro a la tabla usuarios
define('ROOT_PATH', __DIR__);

// Cargar configuración desde .env
require_once ROOT_PATH . '/app/utilidades/env.php';
$env_path = ROOT_PATH . '/.env';
if (!Env::cargar($env_path)) {
    die('Error: No se pudo cargar el archivo .env');
}

// Configuración de la base de datos desde .env
$db_config = [
    'host' => Env::obtener('DB_HOST'),
    'dbname' => Env::obtener('DB_NAME'),
    'user' => Env::obtener('DB_USER'),
    'pass' => Env::obtener('DB_PASS'),
    'charset' => Env::obtener('DB_CHARSET', 'utf8mb4')
];

try {
    // Conectar a la base de datos
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}", 
        $db_config['user'], 
        $db_config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "Conectado a la base de datos: {$db_config['dbname']}\n\n";
    
    // Verificar si existe la columna fecha_registro
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'fecha_registro'");
    $column_exists = $stmt->fetch() !== false;
    
    if ($column_exists) {
        echo "✓ La columna 'fecha_registro' ya existe\n";
    } else {
        echo "✗ La columna 'fecha_registro' no existe. Agregándola...\n";
        
        // Agregar la columna fecha_registro
        $sql = "ALTER TABLE usuarios ADD COLUMN fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP AFTER activo";
        $pdo->exec($sql);
        
        echo "✓ Columna 'fecha_registro' agregada exitosamente\n";
        
        // Actualizar registros existentes con una fecha de registro por defecto
        $sql = "UPDATE usuarios SET fecha_registro = CURRENT_TIMESTAMP WHERE fecha_registro IS NULL";
        $rows_affected = $pdo->exec($sql);
        
        echo "✓ Actualizados {$rows_affected} registros existentes con fecha de registro\n";
    }
    
    // Verificar estructura final
    echo "\nEstructura actual de la tabla usuarios:\n";
    echo str_repeat("=", 60) . "\n";
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo sprintf("%-20s %-20s %-10s %-15s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Default'] ?? 'NULL'
        );
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
