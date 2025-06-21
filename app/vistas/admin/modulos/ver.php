<?php
/**
 * Vista de detalles del módulo - AUTOEXAM2
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'profesor'])) {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}

$modulo = $datos['modulo'];
$examenes = $datos['examenes'];
?>

<?php require_once APP_PATH . '/vistas/parciales/head_' . $_SESSION['rol'] . '.php'; ?>

<body class="bg-light">
    <?php require_once APP_PATH . '/vistas/parciales/navbar_' . $_SESSION['rol'] . '.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Título -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-puzzle-piece text-primary me-2"></i>
                            <?= htmlspecialchars($modulo['titulo']) ?>
                        </h1>
                        <p class="text-muted mb-0">Detalles del módulo</p>
                    </div>
                    <div>
                        <a href="<?= BASE_URL ?>/modulos" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver al listado
                        </a>
                        <?php if ($_SESSION['rol'] === 'admin' || ($_SESSION['rol'] === 'profesor' && $modulo['id_profesor'] == $_SESSION['id_usuario'])): ?>
                            <a href="<?= BASE_URL ?>/modulos/editar/<?= $modulo['id_modulo'] ?>" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>Editar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mensajes -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="row">
                    <!-- Información del módulo -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Información del módulo
                                </h5>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-3">ID:</dt>
                                    <dd class="col-sm-9"><?= htmlspecialchars($modulo['id_modulo']) ?></dd>
                                    
                                    <dt class="col-sm-3">Título:</dt>
                                    <dd class="col-sm-9"><?= htmlspecialchars($modulo['titulo']) ?></dd>
                                    
                                    <dt class="col-sm-3">Descripción:</dt>
                                    <dd class="col-sm-9">
                                        <?php if (!empty($modulo['descripcion'])): ?>
                                            <?= htmlspecialchars($modulo['descripcion']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin descripción</span>
                                        <?php endif; ?>
                                    </dd>
                                    
                                    <dt class="col-sm-3">Profesor:</dt>
                                    <dd class="col-sm-9">
                                        <?php if (!empty($modulo['nombre'])): ?>
                                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle">
                                                <i class="fas fa-chalkboard-teacher me-1"></i>
                                                <?= htmlspecialchars($modulo['apellidos'] . ', ' . $modulo['nombre']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Sin asignar
                                            </span>
                                        <?php endif; ?>
                                    </dd>
                                    
                                    <dt class="col-sm-3">Estado:</dt>
                                    <dd class="col-sm-9">
                                        <?php if ($modulo['activo'] == 1): ?>
                                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle">
                                                <i class="fas fa-times-circle me-1"></i>
                                                Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Exámenes del módulo -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-alt me-2"></i>Exámenes del módulo
                                </h5>
                                <span class="badge bg-primary rounded-pill">
                                    <?= count($examenes) ?> examen<?= count($examenes) != 1 ? 'es' : '' ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($examenes)): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($examenes as $examen): ?>
                                            <div class="list-group-item border-0 px-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?= htmlspecialchars($examen['titulo']) ?></h6>
                                                        <?php if (!empty($examen['descripcion'])): ?>
                                                            <p class="mb-1 text-muted small"><?= htmlspecialchars($examen['descripcion']) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <?php if ($examen['activo'] == 1): ?>
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                                <i class="fas fa-check-circle me-1"></i>Activo
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                                <i class="fas fa-times-circle me-1"></i>Inactivo
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-file-alt text-muted" style="font-size: 3rem;"></i>
                                        <h5 class="text-muted mt-3">No hay exámenes</h5>
                                        <p class="text-muted">Este módulo aún no tiene exámenes asignados.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Panel lateral -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-cogs me-2"></i>Acciones
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <?php if ($_SESSION['rol'] === 'admin' || ($_SESSION['rol'] === 'profesor' && $modulo['id_profesor'] == $_SESSION['id_usuario'])): ?>
                                        <a href="<?= BASE_URL ?>/modulos/editar/<?= $modulo['id_modulo'] ?>" class="btn btn-primary">
                                            <i class="fas fa-edit me-1"></i>Editar módulo
                                        </a>
                                        
                                        <?php if ($modulo['activo'] == 1): ?>
                                            <a href="<?= BASE_URL ?>/modulos/desactivar/<?= $modulo['id_modulo'] ?>" 
                                               class="btn btn-warning"
                                               onclick="return confirm('¿Está seguro de desactivar este módulo?')">
                                                <i class="fas fa-toggle-off me-1"></i>Desactivar
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>/modulos/activar/<?= $modulo['id_modulo'] ?>" 
                                               class="btn btn-success">
                                                <i class="fas fa-toggle-on me-1"></i>Activar
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <a href="<?= BASE_URL ?>/modulos" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Volver al listado
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once APP_PATH . '/vistas/parciales/footer.php'; ?>
</body>
</html>
