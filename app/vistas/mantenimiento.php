<?php
// filepath: /Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/app/vistas/mantenimiento.php
/**
 * Vista de mantenimiento - AUTOEXAM2
 * Mostrada cuando el sistema se encuentra en modo mantenimiento
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema en Mantenimiento - AUTOEXAM2</title>
    <meta http-equiv="refresh" content="60">
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
        .maintenance-container {
            max-width: 600px;
            padding: 3rem;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            text-align: center;
        }
        .maintenance-icon {
            font-size: 5rem;
            color: #ffc107;
            margin-bottom: 1.5rem;
        }
        .logo {
            max-height: 100px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <img src="<?= BASE_URL ?>/publico/recursos/logo.png" alt="AUTOEXAM2 Logo" class="logo">
        <div class="maintenance-icon">
            <i class="fas fa-wrench"></i>
        </div>
        <h1>Sistema en Mantenimiento</h1>
        <p class="lead mb-4">
            Estamos realizando mejoras en el sistema. Por favor, inténtelo nuevamente más tarde.
        </p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="javascript:window.location.reload()" class="btn btn-primary">
                <i class="fas fa-sync-alt me-2"></i>Actualizar página
            </a>
            <?php if (isset($_SESSION['id_usuario'])): ?>
            <a href="<?= BASE_URL ?>/autenticacion/logout" class="btn btn-outline-secondary">
                <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
            </a>
            <?php endif; ?>
        </div>
        
        <div class="mt-4 text-muted small">
            <p>
                Esta página se actualizará automáticamente cada minuto.
                <br>
                Si necesita asistencia, contacte con el administrador.
            </p>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
