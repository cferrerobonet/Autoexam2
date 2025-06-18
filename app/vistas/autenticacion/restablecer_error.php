<?php
/**
 * Vista de error en el restablecimiento de contraseña - AUTOEXAM2
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $datos['titulo'] ?? 'Error de Restablecimiento' ?> - <?= SYSTEM_NAME ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/recursos/estilos.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .recovery-container {
            max-width: 450px;
            padding: 2rem;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .logo {
            max-height: 80px;
            max-width: 100%;
            height: auto;
            width: auto;
            object-fit: contain;
            margin-bottom: 1.5rem;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="text-center mb-4">
            <?php 
                // Verificar si existe el archivo del logo
                $mainLogoPath = ROOT_PATH . '/publico/recursos/logo.png';
                
                // Detectar si estamos en producción (no localhost)
                $isProduction = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') === false && 
                                strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false;
                
                // En producción usamos /recursos/logo.png, en desarrollo /publico/recursos/logo.png
                $logoPath = $isProduction 
                    ? BASE_URL . '/recursos/logo.png' 
                    : BASE_URL . '/publico/recursos/logo.png';
                    
                // Debug: Registrar información sobre la carga del logo
                error_log("Restablecer_error.php - Intentando cargar logo desde: " . $logoPath . " (Producción: " . ($isProduction ? "Sí" : "No") . ")");
            ?>
                <img src="<?= $logoPath ?>" alt="<?= SYSTEM_NAME ?> Logo" class="logo" 
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iIzAwN2JmZiIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkFVVE9FWEFNPC90ZXh0Pjwvc3ZnPg=='; this.classList.add('default-logo'); console.log('Error al cargar el logo desde: ' + this.src);"
                >
            
            <div class="error-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h2>Error de Restablecimiento</h2>
            <p class="text-muted">No se pudo procesar su solicitud</p>
        </div>

        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= $datos['error'] ?? 'El enlace de restablecimiento es inválido o ha expirado.' ?>
        </div>

        <div class="card mt-4 mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>¿Qué puedo hacer?</h5>
                <p class="card-text">Puede solicitar un nuevo enlace de restablecimiento de contraseña.</p>
            </div>
        </div>

        <div class="d-grid gap-2">
            <a href="<?= BASE_URL ?>/autenticacion/recuperar" class="btn btn-primary">
                <i class="fas fa-redo me-2"></i>Solicitar Nuevo Enlace
            </a>
            <a href="<?= BASE_URL ?>/autenticacion/login" class="btn btn-outline-secondary mt-2">
                <i class="fas fa-home me-2"></i>Volver al Inicio de Sesión
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
