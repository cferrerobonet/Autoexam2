<?php
// filepath: /Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/app/vistas/error/error500.php
/**
 * Vista de error 500 (Error Interno del Servidor) - AUTOEXAM2
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Interno - <?= SYSTEM_NAME ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            max-width: 500px;
            padding: 2rem;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            text-align: center;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-code {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-bug"></i>
        </div>
        <div class="error-code">Error 500</div>
        <h2>Error Interno del Servidor</h2>
        <p class="text-muted mb-4">Lo sentimos, ha ocurrido un error inesperado. El equipo técnico ha sido notificado.</p>
        
        <a href="<?= BASE_URL ?>" class="btn btn-primary">
            <i class="fas fa-home me-2"></i>Volver al inicio
        </a>
        
        <a href="javascript:history.back()" class="btn btn-outline-secondary ms-2">
            <i class="fas fa-arrow-left me-2"></i>Volver atrás
        </a>
        
        <?php if (defined('DEBUG_MODE') && DEBUG_MODE && isset($excepcion)): ?>
        <div class="mt-4 text-start">
            <hr>
            <h5>Información de depuración</h5>
            <div class="alert alert-danger">
                <strong>Error:</strong> <?= $excepcion->getMessage() ?><br>
                <strong>Archivo:</strong> <?= $excepcion->getFile() ?><br>
                <strong>Línea:</strong> <?= $excepcion->getLine() ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
