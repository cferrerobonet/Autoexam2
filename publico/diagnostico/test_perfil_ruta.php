<?php
/**
 * Test directo del controlador de perfil
 */

// Simular el entorno
$_GET['url'] = 'perfil/cambiar-contrasena';

// Incluir configuración básica
require_once '../config/config.php';
require_once '../app/utilidades/env.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Test de ruta perfil/cambiar-contrasena</h1>";
echo "<p>URL simulada: " . $_GET['url'] . "</p>";

// Verificar que el controlador existe
$controlador_path = '../app/controladores/perfil_controlador.php';
echo "<p>Controlador existe: " . (file_exists($controlador_path) ? 'SÍ' : 'NO') . "</p>";

if (file_exists($controlador_path)) {
    require_once $controlador_path;
    
    // Verificar que la clase existe
    if (class_exists('PerfilControlador')) {
        echo "<p>Clase PerfilControlador existe: SÍ</p>";
        
        // Verificar métodos
        $reflection = new ReflectionClass('PerfilControlador');
        $metodos = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        echo "<h3>Métodos públicos disponibles:</h3><ul>";
        foreach ($metodos as $metodo) {
            echo "<li>" . $metodo->getName() . "()</li>";
        }
        echo "</ul>";
        
        // Verificar si __call existe
        if ($reflection->hasMethod('__call')) {
            echo "<p>Método mágico __call existe: SÍ</p>";
        }
        
        // Verificar cambiarContrasena
        if ($reflection->hasMethod('cambiarContrasena')) {
            echo "<p>Método cambiarContrasena existe: SÍ</p>";
        } else {
            echo "<p>Método cambiarContrasena existe: NO</p>";
        }
        
    } else {
        echo "<p>Clase PerfilControlador existe: NO</p>";
    }
}

echo "<br><a href='index.php'>Volver</a>";
?>
