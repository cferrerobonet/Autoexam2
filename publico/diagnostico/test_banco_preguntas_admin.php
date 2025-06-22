<?php
/**
 * Diagnóstico de acceso al Banco de Preguntas para Admin
 * AUTOEXAM2
 */

// Configuración básica
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/utilidades/sesion.php';

echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Banco de Preguntas - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-question-circle me-2"></i>Diagnóstico: Banco de Preguntas - Admin</h3>
                    </div>
                    <div class="card-body">';

$problemas = [];
$verificaciones = [];

// 1. Verificar sesión activa
echo '<h4 class="mt-4"><i class="fas fa-user-shield me-2 text-info"></i>Estado de la Sesión</h4>';
if (isset($_SESSION['usuario_logueado']) && $_SESSION['usuario_logueado']) {
    $verificaciones[] = "Usuario logueado: " . ($_SESSION['email'] ?? 'N/A');
    echo '<div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Usuario logueado:</strong> ' . htmlspecialchars($_SESSION['email'] ?? 'N/A') . '
    </div>';
    
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
        $verificaciones[] = "Rol correcto: admin";
        echo '<div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Rol:</strong> admin (acceso permitido)
        </div>';
    } else {
        $problemas[] = "Rol incorrecto: " . ($_SESSION['rol'] ?? 'sin rol');
        echo '<div class="alert alert-danger">
            <i class="fas fa-times-circle me-2"></i>
            <strong>Rol:</strong> ' . htmlspecialchars($_SESSION['rol'] ?? 'sin rol') . ' (acceso denegado)
        </div>';
    }
} else {
    $problemas[] = "No hay sesión activa";
    echo '<div class="alert alert-danger">
        <i class="fas fa-times-circle me-2"></i>
        <strong>Estado:</strong> Sin sesión activa
    </div>';
}

// 2. Verificar archivos del sistema
echo '<h4 class="mt-4"><i class="fas fa-file-code me-2 text-info"></i>Verificación de Archivos</h4>';

$archivos_necesarios = [
    'Controlador' => '/app/controladores/banco_preguntas_controlador.php',
    'Modelo Pregunta' => '/app/modelos/pregunta_banco_modelo.php',
    'Modelo Respuesta' => '/app/modelos/respuesta_banco_modelo.php',
    'Vista Principal' => '/app/vistas/profesor/banco_preguntas.php',
    'Vista Crear' => '/app/vistas/profesor/nueva_pregunta_banco.php'
];

foreach ($archivos_necesarios as $nombre => $ruta) {
    $ruta_completa = __DIR__ . '/../..' . $ruta;
    if (file_exists($ruta_completa)) {
        $verificaciones[] = "$nombre existe";
        echo '<div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>' . $nombre . ':</strong> ✓ Existe
        </div>';
    } else {
        $problemas[] = "$nombre no existe en $ruta";
        echo '<div class="alert alert-danger">
            <i class="fas fa-times-circle me-2"></i>
            <strong>' . $nombre . ':</strong> ✗ No encontrado en ' . htmlspecialchars($ruta) . '
        </div>';
    }
}

// 3. Verificar ruteador
echo '<h4 class="mt-4"><i class="fas fa-route me-2 text-info"></i>Verificación del Ruteador</h4>';

$ruteador_path = __DIR__ . '/../../app/controladores/ruteador.php';
if (file_exists($ruteador_path)) {
    $ruteador_content = file_get_contents($ruteador_path);
    
    if (strpos($ruteador_content, 'banco-preguntas') !== false) {
        $verificaciones[] = "Ruta banco-preguntas configurada en ruteador";
        echo '<div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Ruteador:</strong> ✓ Ruta "banco-preguntas" configurada
        </div>';
    } else {
        $problemas[] = "Ruta banco-preguntas no encontrada en ruteador";
        echo '<div class="alert alert-danger">
            <i class="fas fa-times-circle me-2"></i>
            <strong>Ruteador:</strong> ✗ Ruta "banco-preguntas" no configurada
        </div>';
    }
} else {
    $problemas[] = "Archivo ruteador.php no encontrado";
    echo '<div class="alert alert-danger">
        <i class="fas fa-times-circle me-2"></i>
        <strong>Ruteador:</strong> ✗ Archivo no encontrado
    </div>';
}

// 4. Verificar navbar admin
echo '<h4 class="mt-4"><i class="fas fa-bars me-2 text-info"></i>Verificación del Menú Admin</h4>';

$navbar_path = __DIR__ . '/../../app/vistas/parciales/navbar_admin.php';
if (file_exists($navbar_path)) {
    $navbar_content = file_get_contents($navbar_path);
    
    if (strpos($navbar_content, 'banco-preguntas') !== false) {
        $verificaciones[] = "Enlace banco-preguntas en navbar admin";
        echo '<div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Navbar Admin:</strong> ✓ Enlace "Banco de Preguntas" presente
        </div>';
    } else {
        $problemas[] = "Enlace banco-preguntas no encontrado en navbar admin";
        echo '<div class="alert alert-danger">
            <i class="fas fa-times-circle me-2"></i>
            <strong>Navbar Admin:</strong> ✗ Enlace "Banco de Preguntas" no encontrado
        </div>';
    }
} else {
    $problemas[] = "Archivo navbar_admin.php no encontrado";
    echo '<div class="alert alert-danger">
        <i class="fas fa-times-circle me-2"></i>
        <strong>Navbar Admin:</strong> ✗ Archivo no encontrado
    </div>';
}

// 5. Probar URL directa
echo '<h4 class="mt-4"><i class="fas fa-link me-2 text-info"></i>Prueba de Acceso Directo</h4>';

if (isset($_SESSION['usuario_logueado']) && $_SESSION['usuario_logueado'] && 
    isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    
    echo '<div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Prueba manual:</strong> 
        <a href="' . BASE_URL . '/banco-preguntas" class="btn btn-primary btn-sm ms-2" target="_blank">
            <i class="fas fa-external-link-alt me-1"></i>Acceder al Banco de Preguntas
        </a>
    </div>';
} else {
    echo '<div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Prueba manual:</strong> No se puede probar (requiere sesión admin activa)
    </div>';
}

// 6. Resumen
echo '<h4 class="mt-4"><i class="fas fa-clipboard-check me-2 text-info"></i>Resumen del Diagnóstico</h4>';

if (empty($problemas)) {
    echo '<div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <strong>✓ Todo correcto:</strong> El acceso al Banco de Preguntas debería funcionar correctamente.
    </div>';
} else {
    echo '<div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Se encontraron ' . count($problemas) . ' problema(s):</strong>
        <ul class="mb-0 mt-2">';
    foreach ($problemas as $problema) {
        echo '<li>' . htmlspecialchars($problema) . '</li>';
    }
    echo '</ul>
    </div>';
}

echo '<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Verificaciones exitosas (' . count($verificaciones) . '):</strong>
    <ul class="mb-0 mt-2">';
foreach ($verificaciones as $verificacion) {
    echo '<li>' . htmlspecialchars($verificacion) . '</li>';
}
echo '</ul>
</div>';

// 7. Enlace de retorno
echo '<div class="mt-4">
    <a href="' . BASE_URL . '/publico/diagnostico/index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver al índice de diagnósticos
    </a>
</div>';

echo '                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
?>
