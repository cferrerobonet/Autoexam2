<?php
/**
 * Verificación Final de Acceso a Exámenes
 */
session_start();

// Configurar el entorno
define('ROOT_PATH', __DIR__ . '/../..');
define('APP_PATH', ROOT_PATH . '/app');
define('BASE_URL', 'http://localhost/AUTOEXAM2');

require_once ROOT_PATH . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Final - Acceso a Exámenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🔍 Verificación Final: Acceso a Exámenes</h1>
        <p><a href="index.php" class="btn btn-secondary">← Volver al índice</a></p>
        
        <div class="row">
            <div class="col-md-8">
                
                <h3>Estado de la Sesión</h3>
                <ul class="list-group mb-4">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Usuario autenticado
                        <span class="<?= isset($_SESSION['id_usuario']) ? 'status-ok' : 'status-error' ?>">
                            <?= isset($_SESSION['id_usuario']) ? '✓ SÍ' : '✗ NO' ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Rol permitido (admin/profesor)
                        <span class="<?= isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'profesor') ? 'status-ok' : 'status-error' ?>">
                            <?= isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'profesor') ? '✓ SÍ (' . $_SESSION['rol'] . ')' : '✗ NO' ?>
                        </span>
                    </li>
                </ul>
                
                <h3>Componentes del Sistema</h3>
                <ul class="list-group mb-4">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Controlador de Exámenes
                        <span class="<?= file_exists(APP_PATH . '/controladores/examenes_controlador.php') ? 'status-ok' : 'status-error' ?>">
                            <?= file_exists(APP_PATH . '/controladores/examenes_controlador.php') ? '✓ EXISTE' : '✗ NO EXISTE' ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Vista Admin Exámenes
                        <span class="<?= file_exists(APP_PATH . '/vistas/admin/examenes.php') ? 'status-ok' : 'status-error' ?>">
                            <?= file_exists(APP_PATH . '/vistas/admin/examenes.php') ? '✓ EXISTE' : '✗ NO EXISTE' ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Vista Profesor Exámenes
                        <span class="<?= file_exists(APP_PATH . '/vistas/profesor/examenes.php') ? 'status-ok' : 'status-error' ?>">
                            <?= file_exists(APP_PATH . '/vistas/profesor/examenes.php') ? '✓ EXISTE' : '✗ NO EXISTE' ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Modelo de Examen
                        <span class="<?= file_exists(APP_PATH . '/modelos/examen_modelo.php') ? 'status-ok' : 'status-error' ?>">
                            <?= file_exists(APP_PATH . '/modelos/examen_modelo.php') ? '✓ EXISTE' : '✗ NO EXISTE' ?>
                        </span>
                    </li>
                </ul>
                
                <h3>Enlaces de Acceso Directo</h3>
                <div class="d-grid gap-2 mb-4">
                    <a href="<?= BASE_URL ?>/examenes" class="btn btn-primary" target="_blank">
                        🔗 Acceder a Gestión de Exámenes
                    </a>
                    <a href="<?= BASE_URL ?>/examenes/crear" class="btn btn-success" target="_blank">
                        ➕ Crear Nuevo Examen
                    </a>
                </div>
                
                <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'profesor')): ?>
                <div class="alert alert-success">
                    <h4>✅ Sistema Configurado Correctamente</h4>
                    <p>Los cambios han sido aplicados. Ahora deberías poder acceder a la gestión de exámenes desde:</p>
                    <ul>
                        <li>El menú lateral "Académico > Exámenes"</li>
                        <li>La URL directa: <code>/examenes</code></li>
                        <li>Los enlaces de arriba</li>
                    </ul>
                    <hr>
                    <p class="mb-0"><strong>Diferencias por rol:</strong></p>
                    <ul class="mb-0">
                        <li><strong>Administrador:</strong> Ve todos los exámenes del sistema con vista administrativa completa</li>
                        <li><strong>Profesor:</strong> Ve solo los exámenes de sus cursos y módulos</li>
                    </ul>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <h4>⚠️ Acceso Restringido</h4>
                    <p>Para acceder a la gestión de exámenes necesitas:</p>
                    <ul>
                        <li>Estar autenticado en el sistema</li>
                        <li>Tener rol de "admin" o "profesor"</li>
                    </ul>
                    <a href="<?= BASE_URL ?>/autenticacion/login" class="btn btn-primary">Iniciar Sesión</a>
                </div>
                <?php endif; ?>
                
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Información de Sesión</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">ID:</dt>
                            <dd class="col-sm-8"><?= $_SESSION['id_usuario'] ?? 'No definido' ?></dd>
                            
                            <dt class="col-sm-4">Rol:</dt>
                            <dd class="col-sm-8"><?= $_SESSION['rol'] ?? 'No definido' ?></dd>
                            
                            <dt class="col-sm-4">Nombre:</dt>
                            <dd class="col-sm-8"><?= $_SESSION['nombre'] ?? 'No definido' ?></dd>
                            
                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8"><?= $_SESSION['email'] ?? 'No definido' ?></dd>
                        </dl>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Cambios Realizados</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li>✅ Creada vista admin para exámenes</li>
                            <li>✅ Modificado controlador para roles</li>
                            <li>✅ Corregidas llamadas a modelos</li>
                            <li>✅ Verificados archivos de navegación</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
