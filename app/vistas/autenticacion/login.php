<?php
// filepath: /Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/app/vistas/autenticacion/login.php
/**
 * Vista de login - AUTOEXAM2
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $datos['titulo'] ?? 'Iniciar Sesión' ?> - <?= SYSTEM_NAME ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/estilos/formulario.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            padding: 2rem;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .logo {
            max-height: 80px; /* Altura ligeramente menor para evitar problemas */
            max-width: 100%; /* Asegura que no se desborde del contenedor */
            height: auto; /* Mantiene la proporción */
            width: auto; /* Mantiene la proporción */
            object-fit: contain; /* Asegura que la imagen se muestre completa */
            margin-bottom: 1.5rem;
            display: block; /* Elimina espacio extra bajo la imagen */
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="text-center mb-4">
            <?php 
                // Usar la función auxiliar para obtener la URL del logo
                $logoPath = get_logo_url();
                $logoFallback = get_logo_fallback_svg();
                
                // Debug: Registrar información sobre la carga del logo
                error_log("Login.php - Intentando cargar logo desde: " . $logoPath);
            ?>
            <div class="logo-container" style="height: 80px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                <img 
                    src="<?= $logoPath ?>" 
                    alt="<?= SYSTEM_NAME ?> Logo" 
                    class="logo" 
                    onerror="this.src='<?= $logoFallback ?>'; this.classList.add('default-logo'); console.log('Error al cargar el logo desde: ' + this.src);"
                >
            </div>
            
            <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
            <div class="small text-muted mb-2 text-center">
                Logo: <?= $logoPath ?><br>
                Existe logo principal: <?= file_exists($mainLogoPath) ? 'Sí' : 'No' ?>
            </div>
            <?php endif; ?>
            <h2>Iniciar Sesión</h2>
            <p class="text-muted">Ingrese sus credenciales para acceder</p>
        </div>

        <?php if (isset($datos['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= $datos['error'] ?>
            </div>
            
            <?php if (isset($datos['mostrar_recuperacion']) && $datos['mostrar_recuperacion']): ?>
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>¿No puede esperar?</strong> Puede usar la opción de recuperar contraseña para acceder inmediatamente.
                <div class="mt-2">
                    <a href="<?= BASE_URL ?>/autenticacion/recuperar" class="btn btn-info btn-sm">
                        <i class="fas fa-key me-1"></i>Recuperar Contraseña
                    </a>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/autenticacion/login">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
            
            <div class="mb-3">
                <label for="correo" class="form-label">
                    <i class="fas fa-envelope me-2"></i>Correo Electrónico
                </label>
                <input type="email" class="form-control" id="correo" name="correo" required
                       placeholder="correo@ejemplo.com" autocomplete="email">
            </div>

            <div class="mb-3">
                <label for="contrasena" class="form-label">
                    <i class="fas fa-lock me-2"></i>Contraseña
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="contrasena" name="contrasena" 
                           required placeholder="Ingrese su contraseña" autocomplete="current-password">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="sesion_unica" name="sesion_unica" value="1">
                <label class="form-check-label" for="sesion_unica">
                    <i class="fas fa-user-shield me-1"></i>Cerrar mis otras sesiones activas
                </label>
                <small class="form-text text-muted d-block">
                    <i class="fas fa-info-circle me-1"></i>Si activa esta opción, se cerrarán todas sus sesiones en otros dispositivos.
                </small>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                </button>
            </div>
            
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/autenticacion/recuperar" class="text-decoration-none">
                    <i class="fas fa-key me-1"></i>¿Olvidó su contraseña?
                </a>
            </div>
        </form>
    </div>

    <!-- Bootstrap 5 Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para mostrar/ocultar contraseña -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('contrasena');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
