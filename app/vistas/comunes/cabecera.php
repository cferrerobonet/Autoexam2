<?php
/**
 * Cabecera común para todas las vistas
 */

// Simular sesión de admin para desarrollo si no existe
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['id_usuario'] = 1;
    $_SESSION['rol'] = 'admin';
    $_SESSION['nombre'] = 'Admin';
    $_SESSION['apellidos'] = 'Desarrollo';
}

// Incluir head según el rol
$rol = $_SESSION['rol'] ?? 'admin';

// Asegurar que siempre tengamos datos de usuario disponibles
if (!isset($datos) || !isset($datos['usuario'])) {
    $datos = $datos ?? [];
    $datos['usuario'] = [
        'nombre' => $_SESSION['nombre'] ?? 'Usuario',
        'apellidos' => $_SESSION['apellidos'] ?? '',
        'correo' => $_SESSION['correo'] ?? '',
        'rol' => $rol
    ];
}

if ($rol === 'admin') {
    require_once __DIR__ . '/../parciales/head_admin.php';
    require_once __DIR__ . '/../parciales/navbar_admin.php';
} elseif ($rol === 'profesor') {
    require_once __DIR__ . '/../parciales/head_profesor.php';
    require_once __DIR__ . '/../parciales/navbar_profesor.php';
} else {
    require_once __DIR__ . '/../parciales/head_alumno.php';
    require_once __DIR__ . '/../parciales/navbar_alumno.php';
}
?>
<main class="content">
