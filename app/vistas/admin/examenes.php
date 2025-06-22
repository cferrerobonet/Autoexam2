<?php
/**
 * Vista de gestión de exámenes para administradores - AUTOEXAM2
 */

// Verificar que estamos en el contexto correcto
if (!isset($examenes)) {
    header("Location: " . BASE_URL);
    exit;
}

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Incluir cabecera
require_once APP_PATH . '/vistas/parciales/head_admin.php';

// Incluir barra de navegación
require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
?>

<style>
.estado-badge {
    font-size: 0.8em;
}
.examen-card {
    transition: transform 0.2s;
    border-left: 4px solid #dee2e6;
}
.examen-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.examen-card.activo {
    border-left-color: #28a745;
}
.examen-card.borrador {
    border-left-color: #ffc107;
}
.examen-card.finalizado {
    border-left-color: #dc3545;
}
.btn-action {
    margin: 2px;
}
.filtros-container {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}
.admin-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 30px;
}
</style>

<!-- Encabezado específico para administradores -->
    <div class="admin-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    Gestión Global de Exámenes
                </h1>
                <p class="mb-0 opacity-75">Panel de administración - Vista completa del sistema</p>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/examenes/crear" class="btn btn-light btn-lg">
                    <i class="fas fa-plus-circle me-2"></i>
                    Nuevo Examen
                </a>
            </div>
        </div>
    </div>

    <!-- Mensajes de sistema -->
    <div class="container-fluid">
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['mensaje_exito']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje_exito']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($_SESSION['mensaje_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje_error']); ?>
        <?php endif; ?>
    </div>

    <!-- Estadísticas rápidas para admin -->
    <div class="container-fluid mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="h2 text-primary mb-2">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="mb-1"><?= count($examenes) ?></h3>
                        <p class="text-muted mb-0">Total Exámenes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="h2 text-success mb-2">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="mb-1">
                            <?= count(array_filter($examenes, function($e) { return $e['activo'] == 1; })) ?>
                        </h3>
                        <p class="text-muted mb-0">Activos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="h2 text-warning mb-2">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                        <h3 class="mb-1">
                            <?= count(array_filter($examenes, function($e) { return $e['activo'] == 0; })) ?>
                        </h3>
                        <p class="text-muted mb-0">Inactivos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="h2 text-info mb-2">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="mb-1">
                            <?= count(array_unique(array_column($examenes, 'nombre_profesor'))) ?>
                        </h3>
                        <p class="text-muted mb-0">Profesores</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros avanzados para admin -->
    <div class="container-fluid">
        <div class="filtros-container">
            <h5 class="mb-3">
                <i class="fas fa-filter me-2"></i>
                Filtros Avanzados
            </h5>
            <form method="GET" action="<?= BASE_URL ?>/examenes" id="filtrosForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filtro_curso" class="form-label">Curso</label>
                        <select class="form-select" id="filtro_curso" name="curso">
                            <option value="">Todos los cursos</option>
                            <?php if (isset($cursos) && is_array($cursos)): ?>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?= $curso['id_curso'] ?>" 
                                            <?= isset($_GET['curso']) && $_GET['curso'] == $curso['id_curso'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($curso['nombre_curso']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtro_modulo" class="form-label">Módulo</label>
                        <select class="form-select" id="filtro_modulo" name="modulo">
                            <option value="">Todos los módulos</option>
                            <?php if (isset($modulos) && is_array($modulos)): ?>
                                <?php foreach ($modulos as $modulo): ?>
                                    <option value="<?= $modulo['id_modulo'] ?>" 
                                            <?= isset($_GET['modulo']) && $_GET['modulo'] == $modulo['id_modulo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($modulo['titulo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filtro_estado" class="form-label">Estado</label>
                        <select class="form-select" id="filtro_estado" name="estado">
                            <option value="">Todos</option>
                            <option value="activo" <?= isset($_GET['estado']) && $_GET['estado'] == 'activo' ? 'selected' : '' ?>>
                                Activos
                            </option>
                            <option value="inactivo" <?= isset($_GET['estado']) && $_GET['estado'] == 'inactivo' ? 'selected' : '' ?>>
                                Inactivos
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filtro_profesor" class="form-label">Profesor</label>
                        <select class="form-select" id="filtro_profesor" name="profesor">
                            <option value="">Todos los profesores</option>
                            <?php 
                            $profesores_unicos = array_unique(array_filter(array_map(function($e) {
                                return $e['nombre_profesor'] ? $e['nombre_profesor'] . ' ' . $e['apellidos_profesor'] : null;
                            }, $examenes)));
                            foreach ($profesores_unicos as $profesor): 
                            ?>
                                <option value="<?= htmlspecialchars($profesor) ?>" 
                                        <?= isset($_GET['profesor']) && $_GET['profesor'] == $profesor ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($profesor) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                        <a href="<?= BASE_URL ?>/examenes" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Listado de exámenes -->
    <div class="container-fluid">
        <?php if (empty($examenes)): ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-file-alt fa-3x text-muted"></i>
                </div>
                <h3 class="text-muted">No hay exámenes disponibles</h3>
                <p class="text-muted">Comienza creando tu primer examen</p>
                <a href="<?= BASE_URL ?>/examenes/crear" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Crear Primer Examen
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($examenes as $examen): ?>
                    <div class="col-xl-4 col-lg-6 mb-4">
                        <div class="card examen-card shadow-sm h-100 
                                    <?= $examen['activo'] ? 'activo' : 'borrador' ?>">
                            <div class="card-header d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <?= htmlspecialchars($examen['titulo']) ?>
                                    </h5>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        <?= htmlspecialchars($examen['nombre_profesor'] ?? 'Sin asignar') ?>
                                        <?= htmlspecialchars($examen['apellidos_profesor'] ?? '') ?>
                                    </small>
                                </div>
                                <span class="badge <?= $examen['activo'] ? 'bg-success' : 'bg-warning' ?> estado-badge">
                                    <?= $examen['activo'] ? 'Activo' : 'Borrador' ?>
                                </span>
                            </div>
                            
                            <div class="card-body">
                                <?php if ($examen['descripcion']): ?>
                                    <p class="card-text text-muted small">
                                        <?= htmlspecialchars(substr($examen['descripcion'], 0, 100)) ?>
                                        <?= strlen($examen['descripcion']) > 100 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="border-end">
                                            <div class="h6 mb-1"><?= $examen['total_preguntas'] ?? 0 ?></div>
                                            <small class="text-muted">Preguntas</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border-end">
                                            <div class="h6 mb-1">
                                                <?= $examen['tiempo_limite'] ? $examen['tiempo_limite'] . ' min' : 'Sin límite' ?>
                                            </div>
                                            <small class="text-muted">Tiempo</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="h6 mb-1"><?= $examen['intentos_permitidos'] ?? '∞' ?></div>
                                        <small class="text-muted">Intentos</small>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        <?= htmlspecialchars($examen['nombre_curso'] ?? 'Sin curso') ?>
                                        <?php if ($examen['nombre_modulo']): ?>
                                            <br><i class="fas fa-puzzle-piece me-1"></i>
                                            <?= htmlspecialchars($examen['nombre_modulo']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                
                                <?php if ($examen['fecha_inicio'] || $examen['fecha_fin']): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <?php if ($examen['fecha_inicio']): ?>
                                                <i class="fas fa-play me-1"></i>
                                                Desde: <?= date('d/m/Y H:i', strtotime($examen['fecha_inicio'])) ?>
                                                <br>
                                            <?php endif; ?>
                                            <?php if ($examen['fecha_fin']): ?>
                                                <i class="fas fa-stop me-1"></i>
                                                Hasta: <?= date('d/m/Y H:i', strtotime($examen['fecha_fin'])) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="<?= BASE_URL ?>/examenes/editar/<?= $examen['id_examen'] ?>" 
                                       class="btn btn-outline-primary btn-sm btn-action">
                                        <i class="fas fa-edit me-1"></i> Editar
                                    </a>
                                    <a href="<?= BASE_URL ?>/examenes/ver/<?= $examen['id_examen'] ?>" 
                                       class="btn btn-outline-info btn-sm btn-action">
                                        <i class="fas fa-eye me-1"></i> Ver
                                    </a>
                                    <a href="<?= BASE_URL ?>/examenes/resultados/<?= $examen['id_examen'] ?>" 
                                       class="btn btn-outline-success btn-sm btn-action">
                                        <i class="fas fa-chart-bar me-1"></i> Resultados
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm btn-action" 
                                            onclick="eliminarExamen(<?= $examen['id_examen'] ?>, '<?= htmlspecialchars($examen['titulo']) ?>')">
                                        <i class="fas fa-trash me-1"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function eliminarExamen(id, titulo) {
            if (confirm(`¿Estás seguro de que deseas eliminar el examen "${titulo}"?\n\nEsta acción no se puede deshacer.`)) {
                fetch(`<?= BASE_URL ?>/examenes/eliminar/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error al eliminar el examen: ' + (data.error || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el examen');
                });
            }
        }
    </script>
    
<?php 
require_once APP_PATH . '/vistas/parciales/footer_admin.php'; 
require_once APP_PATH . '/vistas/parciales/scripts_admin.php'; 
?>
