<?php
// Script de prueba para verificar la funcionalidad de "Mis Alumnos"
// Este archivo se puede eliminar después de las pruebas

// Configuración básica
define('APP_PATH', __DIR__ . '/../../app');
require_once __DIR__ . '/../../config/config.php';

// Cargar modelo
require_once APP_PATH . '/modelos/curso_modelo.php';

try {
    $cursoModelo = new Curso();
    echo "✓ Modelo de curso cargado correctamente\n";
    
    // Verificar método
    if (method_exists($cursoModelo, 'obtenerAlumnosPorProfesor')) {
        echo "✓ Método obtenerAlumnosPorProfesor existe\n";
        
        // Probar con ID de profesor ficticio
        $alumnos = $cursoModelo->obtenerAlumnosPorProfesor(1);
        echo "✓ Método ejecutado sin errores\n";
        echo "Número de alumnos encontrados: " . count($alumnos) . "\n";
        
        if (count($alumnos) > 0) {
            echo "Primer alumno: " . print_r($alumnos[0], true) . "\n";
        }
    } else {
        echo "✗ Método obtenerAlumnosPorProfesor no existe\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
