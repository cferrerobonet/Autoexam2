<?php
/**
 * Vista: Historial de Usuario - AUTOEXAM2
 */

require_once APP_PATH . '/vistas/parciales/head_admin.php';
?>

<body class="bg-light">
<?php require_once APP_PATH . '/vistas/parciales/navbar_admin.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Estilos personalizados -->
            <style>
                .bg-purple {
                    background-color: #8a5cd1 !important;
                }
                .text-purple {
                    color: #8a5cd1 !important;
                }
                .border-purple {
                    border-color: #8a5cd1 !important;
                }
                .bg-purple-subtle {
                    background-color: rgba(138, 92, 209, 0.1) !important;
                }
                .timeline-item {
                    position: relative;
                    padding-left: 30px;
                }
                .timeline-item::before {
                    content: '';
                    position: absolute;
                    left: 10px;
                    top: 0;
                    bottom: 0;
                    width: 2px;
                    background-color: #e9ecef;
                }
                .timeline-item::after {
                    content: '';
                    position: absolute;
                    left: 5px;
                    top: 20px;
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                    background-color: #8a5cd1;
                }
            </style>
            
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/inicio" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/usuarios" class="text-decoration-none">Usuarios</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($titulo ?? 'Historial') ?></li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1"><i class="fas fa-history me-2 text-primary"></i><?= htmlspecialchars($titulo ?? 'Historial de Usuario') ?></h1>
                    <p class="text-muted">Registro completo de cambios y actividades realizadas</p>
                </div>
                <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Usuarios
                </a>
            </div>

            <!-- Información del Usuario -->
            <?php if (isset($usuario)): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-user text-primary me-2"></i> Información del usuario
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <?php if (!empty($usuario['foto'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($usuario['foto']) ?>" 
                                     alt="Avatar" class="rounded-circle shadow-sm border border-2" 
                                     width="100" height="100" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm" 
                                     style="width: 100px; height: 100px;">
                                    <i class="fas fa-user fa-3x text-white"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Estado -->
                            <div class="mt-2">
                                <?php if ($usuario['activo']): ?>
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3">
                                        <i class="fas fa-check"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-3">
                                        <i class="fas fa-times"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <h4 class="mb-3"><?= htmlspecialchars($usuario['apellidos'] . ', ' . $usuario['nombre']) ?></h4>
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-muted me-2"><i class="fas fa-id-card-alt fa-fw"></i> ID:</span> 
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($usuario['id_usuario']) ?></span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-muted me-2"><i class="fas fa-envelope fa-fw"></i> Correo:</span> 
                                <span><?= htmlspecialchars($usuario['correo']) ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-2"><i class="fas fa-user-tag fa-fw"></i> Rol:</span> 
                                <?php
                                $rolClases = [
                                    'admin' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    'profesor' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'alumno' => 'bg-purple text-white'
                                ];
                                $rolIconos = [
                                    'admin' => 'fa-crown',
                                    'profesor' => 'fa-chalkboard-teacher',
                                    'alumno' => 'fa-user-graduate'
                                ];
                                ?>
                                <span class="badge rounded-pill <?= $rolClases[$usuario['rol']] ?> px-3">
                                    <i class="fas <?= $rolIconos[$usuario['rol']] ?>"></i>
                                    <?= ucfirst($usuario['rol']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Historial -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="fas fa-history text-primary me-2"></i> Historial de Actividades
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($historial)): ?>
                        <div class="timeline px-3">
                            <?php foreach ($historial as $actividad): ?>
                                <div class="timeline-item p-3 border-bottom">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="text-muted d-flex align-items-center">
                                                <i class="fas fa-calendar-alt me-1 text-primary"></i>
                                                <span><?= date('d/m/Y', strtotime($actividad['fecha'])) ?></span>
                                            </div>
                                            <div class="text-muted d-flex align-items-center">
                                                <i class="fas fa-clock me-1 text-primary"></i>
                                                <span><?= date('H:i:s', strtotime($actividad['fecha'])) ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <?php
                                            $accionClase = 'bg-primary';
                                            $accionIcono = 'fa-cog';
                                            
                                            switch (strtolower($actividad['accion'])) {
                                                case 'login':
                                                case 'ingreso':
                                                    $accionClase = 'bg-success-subtle text-success border border-success-subtle';
                                                    $accionIcono = 'fa-sign-in-alt';
                                                    break;
                                                case 'logout':
                                                case 'salida':
                                                    $accionClase = 'bg-info-subtle text-info border border-info-subtle';
                                                    $accionIcono = 'fa-sign-out-alt';
                                                    break;
                                                case 'crear':
                                                    $accionClase = 'bg-primary-subtle text-primary border border-primary-subtle';
                                                    $accionIcono = 'fa-plus';
                                                    break;
                                                case 'editar':
                                                case 'actualizar':
                                                    $accionClase = 'bg-warning-subtle text-warning border border-warning-subtle';
                                                    $accionIcono = 'fa-edit';
                                                    break;
                                                case 'eliminar':
                                                    $accionClase = 'bg-danger-subtle text-danger border border-danger-subtle';
                                                    $accionIcono = 'fa-trash';
                                                    break;
                                                case 'error':
                                                    $accionClase = 'bg-danger-subtle text-danger border border-danger-subtle';
                                                    $accionIcono = 'fa-exclamation-triangle';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge rounded-pill <?= $accionClase ?> px-3">
                                                <i class="fas <?= $accionIcono ?> me-1"></i>
                                                <?= htmlspecialchars($actividad['accion']) ?>
                                            </span>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><?= htmlspecialchars($actividad['descripcion']) ?></p>
                                            <?php if (!empty($actividad['nombre'])): ?>
                                                <small class="text-muted d-flex align-items-center">
                                                    <i class="fas fa-user me-1 text-primary"></i>
                                                    Por: <?= htmlspecialchars($actividad['apellidos'] . ', ' . $actividad['nombre']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="text-muted d-flex align-items-center">
                                                <i class="fas fa-globe me-1 text-primary"></i>
                                                IP: <?= htmlspecialchars($actividad['ip']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-4x text-muted mb-3 opacity-50"></i>
                            <h5 class="text-muted">No hay actividad registrada</h5>
                            <p class="text-muted mb-0">No se encontraron registros de actividad para este usuario</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-item:hover {
    background-color: rgba(138, 92, 209, 0.05);
}
    background-color: #f8f9fa;
}
.timeline-item:last-child {
    border-bottom: none !important;
}
</style>

<?php require_once APP_PATH . '/vistas/parciales/footer_admin.php'; ?>
