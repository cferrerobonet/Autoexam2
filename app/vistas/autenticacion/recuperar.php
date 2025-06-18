<?php
/**
 * Vista de recuperación de contraseña - AUTOEXAM2
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $datos['titulo'] ?? 'Recuperar Contraseña' ?> - <?= SYSTEM_NAME ?></title>
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
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="text-center mb-4">
            <?php 
                // Usar la función auxiliar para obtener la URL del logo
                $logoPath = get_logo_url();
                $logoFallback = get_logo_fallback_svg();
                $logoAlt = SYSTEM_NAME . ' Logo';
                
                // Debug: Mostrar información sobre la ruta
                error_log("Recuperar.php - Intentando cargar logo desde: " . $logoPath);
                
                // Verificar si estamos en producción o desarrollo para diagnóstico
                $isProduction = !function_exists('is_development_environment') || !is_development_environment();
                error_log("Recuperar.php - Entorno: " . ($isProduction ? 'Producción' : 'Desarrollo'));
                
                // Verificar si el archivo existe físicamente
                $logoExists = verify_logo_file_exists();
                error_log("Recuperar.php - Archivo logo existe físicamente: " . ($logoExists ? 'Sí' : 'No'));
            ?>
                <!-- Si el logo existe físicamente, añadir información adicional -->
                <?php if ($logoExists): ?>
                <img src="<?= $logoPath ?>" alt="<?= $logoAlt ?>" class="logo" 
                     onerror="this.src='<?= $logoFallback ?>'; this.classList.add('default-logo'); console.error('Error al cargar el logo desde: ' + this.src);" 
                     onload="console.log('Logo cargado correctamente desde: ' + this.src);" />
                <?php else: ?>
                <!-- Si el logo no existe, usar directamente el fallback -->
                <img src="<?= $logoFallback ?>" alt="<?= $logoAlt ?>" class="logo default-logo" />
                <?php endif; ?>
            
            <h2>Recuperar Contraseña</h2>
            <p class="text-muted">Ingrese su correo para recibir instrucciones</p>
        </div>

        <?php if (isset($datos['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= $datos['error'] ?>
            </div>
        <?php endif; ?>

        <?php if (isset($datos['mensaje'])): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= $datos['mensaje'] ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/autenticacion/recuperar">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
            
            <div class="mb-3">
                <label for="correo" class="form-label">
                    <i class="fas fa-envelope me-2"></i>Correo Electrónico
                </label>
                <input type="email" class="form-control" id="correo" name="correo" required
                       placeholder="correo@ejemplo.com" autocomplete="email">
                <div class="form-text text-muted">
                    Ingrese el correo electrónico con el que se registró en el sistema.
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Enviar Instrucciones
                </button>
            </div>
            
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/autenticacion/login" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Volver al inicio de sesión
                </a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>