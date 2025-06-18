<?php
/**
 * Vista de éxito en el restablecimiento de contraseña - AUTOEXAM2
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $datos['titulo'] ?? 'Restablecimiento Exitoso' ?> - <?= SYSTEM_NAME ?></title>
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
        .success-icon {
            font-size: 4rem;
            color: #198754;
            margin-bottom: 1rem;
        }
        .countdown {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0d6efd;
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
                error_log("Restablecer_exito.php - Intentando cargar logo desde: " . $logoPath . " (Producción: " . ($isProduction ? "Sí" : "No") . ")");
            ?>
                <img src="<?= $logoPath ?>" alt="<?= SYSTEM_NAME ?> Logo" class="logo" 
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iIzAwN2JmZiIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkFVVE9FWEFNPC90ZXh0Pjwvc3ZnPg=='; this.classList.add('default-logo'); console.log('Error al cargar el logo desde: ' + this.src);"
                >
            
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>¡Contraseña Restablecida!</h2>
            <p class="text-muted">Su contraseña ha sido actualizada correctamente</p>
        </div>

        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= $datos['mensaje'] ?? 'Su contraseña ha sido cambiada exitosamente.' ?>
        </div>

        <div class="card mt-4 mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>¿Qué sigue ahora?</h5>
                <p class="card-text">Será redirigido automáticamente a la página de inicio de sesión en <span id="countdown" class="countdown">10</span> segundos.</p>
            </div>
        </div>

        <div class="d-grid">
            <a href="<?= BASE_URL ?>/autenticacion/login" class="btn btn-primary" id="loginButton">
                <i class="fas fa-sign-in-alt me-2"></i>Ir a Inicio de Sesión
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Contador de redirección
        let seconds = 10;
        const countdownElement = document.getElementById('countdown');
        const loginButton = document.getElementById('loginButton');
        
        const countdown = setInterval(function() {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = '<?= BASE_URL ?>/autenticacion/login';
            }
        }, 1000);
        
        // Si el usuario hace clic en el botón, cancelar la redirección automática
        loginButton.addEventListener('click', function() {
            clearInterval(countdown);
        });
    </script>
</body>
</html>
