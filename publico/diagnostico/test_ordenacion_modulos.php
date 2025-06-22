<?php
/**
 * Test de Ordenación de Módulos - AUTOEXAM2
 * Verificación específica del problema mencionado por el usuario
 */

// Configuración inicial
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/utilidades/helpers.php';

// Iniciar sesión si no está activa
if (!isset($_SESSION)) {
    session_start();
}

echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Ordenación de Módulos - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .diagnostico-header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 2rem 0; margin-bottom: 2rem; }
        .check-item { padding: 0.75rem; margin: 0.5rem 0; border-radius: 0.375rem; border: 1px solid #e9ecef; }
        .check-success { background-color: #d4edda; border-color: #c3e6cb; }
        .check-warning { background-color: #fff3cd; border-color: #ffeaa7; }
        .check-error { background-color: #f8d7da; border-color: #f5c6cb; }
        .resultado-container { background: white; border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); padding: 2rem; margin: 1rem 0; }
        .access-test { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="diagnostico-header">
        <div class="container">
            <h1><i class="fas fa-sort-amount-up me-3"></i>Test de Ordenación de Módulos</h1>
            <p class="mb-0">Verificación específica del problema de acceso a gestión de exámenes</p>
        </div>
    </div>
    
    <div class="container">
        <div class="resultado-container">
';

// Resultados de verificación
$problemas_encontrados = [];
$verificaciones_exitosas = [];

// 1. Verificar el ruteador actualizado
echo '<h3><i class="fas fa-route me-2 text-primary"></i>Verificación del Ruteador</h3>';

$ruteador_path = __DIR__ . '/../../app/controladores/ruteador.php';
$ruteador_content = file_get_contents($ruteador_path);

// Buscar la línea específica que añadimos
if (strpos($ruteador_content, "examenes' && file_exists") !== false) {
    $verificaciones_exitosas[] = "Ruteador configurado para examenes";
    echo '<div class="check-item check-success">
        <i class="fas fa-check-circle me-2 text-success"></i>
        <strong>Ruteador:</strong> Configurado correctamente para el controlador de exámenes
        <small class="d-block text-muted">Línea encontrada: elseif ($url[0] === \'examenes\' && file_exists...)</small>
    </div>';
} else {
    $problemas_encontrados[] = "Ruteador no configurado para examenes";
    echo '<div class="check-item check-error">
        <i class="fas fa-times-circle me-2 text-danger"></i>
        <strong>Ruteador:</strong> No configurado para el controlador de exámenes
    </div>';
}

// Verificar también preguntas
if (strpos($ruteador_content, "preguntas' && file_exists") !== false) {
    $verificaciones_exitosas[] = "Ruteador configurado para preguntas";
    echo '<div class="check-item check-success">
        <i class="fas fa-check-circle me-2 text-success"></i>
        <strong>Ruteador:</strong> Configurado correctamente para el controlador de preguntas
    </div>';
} else {
    $problemas_encontrados[] = "Ruteador no configurado para preguntas";
    echo '<div class="check-item check-error">
        <i class="fas fa-times-circle me-2 text-danger"></i>
        <strong>Ruteador:</strong> No configurado para el controlador de preguntas
    </div>';
}

// 2. Verificar navbar actualizado
echo '<h3 class="mt-4"><i class="fas fa-bars me-2 text-primary"></i>Verificación del Menú de Navegación</h3>';

$navbar_path = __DIR__ . '/../../app/vistas/parciales/navbar_admin.php';
$navbar_content = file_get_contents($navbar_path);

// Verificar enlace a exámenes
if (strpos($navbar_content, 'href="<?= BASE_URL ?>/examenes"') !== false) {
    $verificaciones_exitosas[] = "Enlace a exámenes en navbar";
    echo '<div class="check-item check-success">
        <i class="fas fa-check-circle me-2 text-success"></i>
        <strong>Navbar:</strong> Enlace a exámenes presente en el menú
    </div>';
} else {
    $problemas_encontrados[] = "Enlace a exámenes falta en navbar";
    echo '<div class="check-item check-error">
        <i class="fas fa-times-circle me-2 text-danger"></i>
        <strong>Navbar:</strong> Enlace a exámenes no encontrado
    </div>';
}

// Verificar enlace a banco de preguntas
if (strpos($navbar_content, 'href="<?= BASE_URL ?>/banco-preguntas"') !== false) {
    $verificaciones_exitosas[] = "Enlace a banco de preguntas en navbar";
    echo '<div class="check-item check-success">
        <i class="fas fa-check-circle me-2 text-success"></i>
        <strong>Navbar:</strong> Enlace a banco de preguntas presente en el menú
    </div>';
} else {
    $problemas_encontrados[] = "Enlace a banco de preguntas falta en navbar";
    echo '<div class="check-item check-warning">
        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
        <strong>Navbar:</strong> Enlace a banco de preguntas no encontrado (opcional)
    </div>';
}

// 3. Verificar existencia de archivos
echo '<h3 class="mt-4"><i class="fas fa-file-code me-2 text-primary"></i>Verificación de Archivos</h3>';

$archivos_clave = [
    'Controlador Exámenes' => __DIR__ . '/../../app/controladores/examenes_controlador.php',
    'Controlador Preguntas' => __DIR__ . '/../../app/controladores/preguntas_controlador.php',
    'Controlador Banco Preguntas' => __DIR__ . '/../../app/controladores/banco_preguntas_controlador.php',
    'Vista Admin Exámenes' => __DIR__ . '/../../app/vistas/admin/examenes.php',
    'Vista Profesor Exámenes' => __DIR__ . '/../../app/vistas/profesor/examenes.php'
];

foreach ($archivos_clave as $nombre => $archivo) {
    if (file_exists($archivo)) {
        $verificaciones_exitosas[] = "$nombre archivo existe";
        echo '<div class="check-item check-success">
            <i class="fas fa-check-circle me-2 text-success"></i>
            <strong>' . $nombre . ':</strong> Archivo encontrado
            <small class="d-block text-muted">' . basename($archivo) . '</small>
        </div>';
    } else {
        $problemas_encontrados[] = "$nombre archivo falta";
        echo '<div class="check-item check-error">
            <i class="fas fa-times-circle me-2 text-danger"></i>
            <strong>' . $nombre . ':</strong> Archivo no encontrado
            <small class="d-block text-muted">' . $archivo . '</small>
        </div>';
    }
}

// 4. Test de acceso directo
echo '<h3 class="mt-4"><i class="fas fa-globe me-2 text-primary"></i>Test de Acceso Directo</h3>';

echo '<div class="access-test">
    <h5><i class="fas fa-external-link-alt me-2"></i>Enlaces de Prueba</h5>
    <p>Haz clic en estos enlaces para verificar que ahora funcionan correctamente:</p>
    <div class="d-grid gap-2 d-md-flex">
        <a href="' . BASE_URL . '/examenes" class="btn btn-light btn-lg" target="_blank">
            <i class="fas fa-file-alt me-2"></i>Gestión de Exámenes
        </a>
        <a href="' . BASE_URL . '/banco-preguntas" class="btn btn-light btn-lg" target="_blank">
            <i class="fas fa-question-circle me-2"></i>Banco de Preguntas
        </a>
        <a href="' . BASE_URL . '/preguntas" class="btn btn-light btn-lg" target="_blank">
            <i class="fas fa-list me-2"></i>Gestión de Preguntas
        </a>
    </div>
</div>';

// 5. Resumen final
echo '<h3 class="mt-4"><i class="fas fa-chart-bar me-2 text-primary"></i>Resumen de Correcciones</h3>';

$total_verificaciones = count($verificaciones_exitosas) + count($problemas_encontrados);
$porcentaje = $total_verificaciones > 0 ? round((count($verificaciones_exitosas) / $total_verificaciones) * 100) : 0;

echo '<div class="row">
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-body text-center">
                <h3 class="text-success">' . count($verificaciones_exitosas) . '</h3>
                <p class="card-text">Verificaciones Exitosas</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h3 class="text-danger">' . count($problemas_encontrados) . '</h3>
                <p class="card-text">Problemas Encontrados</p>
            </div>
        </div>
    </div>
</div>';

echo '<div class="mt-3">
    <div class="progress" style="height: 30px;">
        <div class="progress-bar bg-success" role="progressbar" style="width: ' . $porcentaje . '%" aria-valuenow="' . $porcentaje . '" aria-valuemin="0" aria-valuemax="100">
            <strong>' . $porcentaje . '% Completado</strong>
        </div>
    </div>
</div>';

if (count($problemas_encontrados) == 0) {
    echo '<div class="alert alert-success mt-4">
        <h4><i class="fas fa-check-circle me-2"></i>¡Corrección Exitosa!</h4>
        <p><strong>El problema ha sido resuelto.</strong> Los cambios aplicados fueron:</p>
        <ul>
            <li>✅ Añadido soporte para <code>/examenes</code> en el ruteador</li>
            <li>✅ Añadido soporte para <code>/preguntas</code> en el ruteador</li>
            <li>✅ Añadido enlace "Banco de Preguntas" en el menú académico</li>
        </ul>
        <hr>
        <p class="mb-0"><strong>Ahora deberías poder:</strong></p>
        <ul class="mb-0">
            <li>Acceder a la gestión de exámenes desde el menú lateral</li>
            <li>Usar la URL directa <code>/examenes</code></li>
            <li>Acceder al banco de preguntas desde el menú</li>
        </ul>
    </div>';
} else {
    echo '<div class="alert alert-warning mt-4">
        <h4><i class="fas fa-exclamation-triangle me-2"></i>Problemas Pendientes</h4>
        <p>Aún hay algunos problemas que requieren atención:</p>
        <ul>';
    foreach ($problemas_encontrados as $problema) {
        echo '<li>' . htmlspecialchars($problema) . '</li>';
    }
    echo '</ul>
    </div>';
}

echo '<div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver a Diagnósticos
    </a>
    <a href="' . BASE_URL . '/inicio" class="btn btn-primary">
        <i class="fas fa-home me-2"></i>Ir al Dashboard
    </a>
</div>';

echo '
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
?>
