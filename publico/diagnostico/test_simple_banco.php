<?php
/**
 * Test simple para verificar acceso al banco de preguntas
 */

require_once __DIR__ . '/../../config/config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Banco Preguntas</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='p-4'>
    <div class='container'>
        <h1>Test Banco de Preguntas - Admin</h1>";

// Verificar que los archivos existen
$archivos = [
    'Controlador' => __DIR__ . '/../../app/controladores/banco_preguntas_controlador.php',
    'Navbar Admin' => __DIR__ . '/../../app/vistas/parciales/navbar_admin.php',
    'Ruteador' => __DIR__ . '/../../app/controladores/ruteador.php'
];

echo "<h3>Verificación de archivos:</h3>";
foreach ($archivos as $nombre => $archivo) {
    $existe = file_exists($archivo);
    $color = $existe ? 'success' : 'danger';
    $icono = $existe ? '✓' : '✗';
    echo "<div class='alert alert-{$color}'>{$icono} {$nombre}: " . ($existe ? 'OK' : 'NO ENCONTRADO') . "</div>";
}

// Verificar contenido del ruteador
echo "<h3>Verificación del ruteador:</h3>";
$ruteador = file_get_contents(__DIR__ . '/../../app/controladores/ruteador.php');
$tiene_banco = strpos($ruteador, 'banco-preguntas') !== false;
$color = $tiene_banco ? 'success' : 'danger';
$icono = $tiene_banco ? '✓' : '✗';
echo "<div class='alert alert-{$color}'>{$icono} Ruta banco-preguntas configurada: " . ($tiene_banco ? 'SÍ' : 'NO') . "</div>";

// Verificar navbar admin
echo "<h3>Verificación del navbar admin:</h3>";
$navbar = file_get_contents(__DIR__ . '/../../app/vistas/parciales/navbar_admin.php');
$tiene_enlace = strpos($navbar, 'banco-preguntas') !== false;
$color = $tiene_enlace ? 'success' : 'danger';
$icono = $tiene_enlace ? '✓' : '✗';
echo "<div class='alert alert-{$color}'>{$icono} Enlace en navbar: " . ($tiene_enlace ? 'SÍ' : 'NO') . "</div>";

echo "<div class='mt-4'>
        <a href='" . BASE_URL . "/banco-preguntas' class='btn btn-primary'>Probar acceso al Banco de Preguntas</a>
        <a href='" . BASE_URL . "/publico/diagnostico/index.php' class='btn btn-secondary ms-2'>Volver</a>
      </div>";

echo "</div>
</body>
</html>";
?>
