<?php
/**
 * Vista de listado de módulos para administrador
 * AUTOEXAM2 - Siguiendo el patrón de usuarios y cursos
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<!-- Título y estadísticas -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-puzzle-piece text-primary me-2"></i>
            Gestión de Módulos
        </h1>
        <p class="text-muted mb-0">Administra los módulos del sistema</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/modulos/nuevo" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Módulo
        </a>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-gradient rounded-3 p-3">
                            <i class="fas fa-puzzle-piece text-white fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fs-6 fw-semibold text-muted">Total Módulos</div>
                        <div class="fs-4 fw-bold text-dark"><?= $datos['total_registros'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-gradient rounded-3 p-3">
                            <i class="fas fa-chalkboard-teacher text-white fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fs-6 fw-semibold text-muted">Con Profesor</div>
                        <div class="fs-4 fw-bold text-dark"><?= count(array_filter($datos['modulos'], function($m) { return !empty($m['id_profesor']); })) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-gradient rounded-3 p-3">
                            <i class="fas fa-file-alt text-white fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fs-6 fw-semibold text-muted">Total Exámenes</div>
                        <div class="fs-4 fw-bold text-dark"><?= array_sum(array_column($datos['modulos'], 'total_examenes')) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-gradient rounded-3 p-3">
                            <i class="fas fa-exclamation-triangle text-white fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fs-6 fw-semibold text-muted">Sin Profesor</div>
                        <div class="fs-4 fw-bold text-dark"><?= count(array_filter($datos['modulos'], function($m) { return empty($m['id_profesor']); })) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mensajes -->
<?php if (isset($_SESSION['exito'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['exito']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['exito']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Filtros -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light border-0">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter me-2"></i>Filtros de búsqueda
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/modulos" class="row g-3">
            <div class="col-md-3">
                <label for="buscar" class="form-label">
                    <i class="fas fa-search me-1"></i>Buscar
                </label>
                <input type="text" class="form-control" id="buscar" name="buscar" 
                       value="<?= htmlspecialchars($datos['filtros']['buscar']) ?>" 
                       placeholder="Título, descripción o profesor">
            </div>
            
            <div class="col-md-2">
                <label for="profesor" class="form-label">
                    <i class="fas fa-chalkboard-teacher me-1"></i>Profesor
                </label>
                <select class="form-select" id="profesor" name="profesor">
                    <option value="">Todos</option>
                    <?php foreach ($datos['profesores'] as $profesor): ?>
                        <option value="<?= $profesor['id_usuario'] ?>" 
                                <?= $datos['filtros']['profesor'] == $profesor['id_usuario'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($profesor['apellidos'] . ', ' . $profesor['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="estado" class="form-label">
                    <i class="fas fa-toggle-on me-1"></i>Estado
                </label>
                <select class="form-select" id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="1" <?= (isset($datos['filtros']['estado']) && $datos['filtros']['estado'] == '1') ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= (isset($datos['filtros']['estado']) && $datos['filtros']['estado'] == '0') ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="limite" class="form-label">
                    <i class="fas fa-list me-1"></i>Mostrar
                </label>
                <select class="form-select" id="limite" name="limite">
                    <option value="5" <?= $datos['limite'] == 5 ? 'selected' : '' ?>>5</option>
                    <option value="10" <?= $datos['limite'] == 10 ? 'selected' : '' ?>>10</option>
                    <option value="15" <?= $datos['limite'] == 15 ? 'selected' : '' ?>>15</option>
                    <option value="20" <?= $datos['limite'] == 20 ? 'selected' : '' ?>>20</option>
                    <option value="50" <?= $datos['limite'] == 50 ? 'selected' : '' ?>>50</option>
                </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Buscar
                </button>
                <a href="<?= BASE_URL ?>/modulos" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Listado de módulos -->
<div class="card shadow-sm">
    <div class="card-header bg-light border-0">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Lista de módulos
            </h5>
            <?php if ($datos['total_registros'] > 0): ?>
                <span class="badge bg-primary rounded-pill">
                    <?= $datos['total_registros'] ?> módulo<?= $datos['total_registros'] != 1 ? 's' : '' ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (count($datos['modulos']) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 border">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="py-3 text-muted fw-semibold">
                                <a href="<?= BASE_URL ?>/modulos?<?= http_build_query(array_merge($datos['filtros'], ['ordenar_por' => 'id_modulo', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'id_modulo' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                    ID
                                    <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'id_modulo'): ?>
                                        <i class="ms-1 fas fa-sort-<?= $datos['filtros']['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="py-3 text-muted fw-semibold">
                                <a href="<?= BASE_URL ?>/modulos?<?= http_build_query(array_merge($datos['filtros'], ['ordenar_por' => 'titulo', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'titulo' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                    Módulo
                                    <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'titulo'): ?>
                                        <i class="ms-1 fas fa-sort-<?= $datos['filtros']['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="py-3 text-muted fw-semibold">
                                <a href="<?= BASE_URL ?>/modulos?<?= http_build_query(array_merge($datos['filtros'], ['ordenar_por' => 'apellidos', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'apellidos' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                    Profesor
                                    <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'apellidos'): ?>
                                        <i class="ms-1 fas fa-sort-<?= $datos['filtros']['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="py-3 text-muted fw-semibold">
                                <a href="<?= BASE_URL ?>/modulos?<?= http_build_query(array_merge($datos['filtros'], ['ordenar_por' => 'total_examenes', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'total_examenes' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                    Exámenes
                                    <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'total_examenes'): ?>
                                        <i class="ms-1 fas fa-sort-<?= $datos['filtros']['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="py-3 text-muted fw-semibold text-center">Estado</th>
                            <th class="py-3 text-muted fw-semibold text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datos['modulos'] as $modulo): ?>
                            <tr class="align-middle border-bottom">
                                <td class="py-3"><?= htmlspecialchars($modulo['id_modulo']) ?></td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-gradient rounded-3 me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 48px; height: 48px; min-width: 48px;">
                                            <i class="fas fa-puzzle-piece text-white"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($modulo['titulo']) ?></strong>
                                            <?php if (!empty($modulo['descripcion'])): ?>
                                                <small class="text-muted d-block"><?= htmlspecialchars(mb_substr($modulo['descripcion'], 0, 50)) . (mb_strlen($modulo['descripcion']) > 50 ? '...' : '') ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
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
                                </td>
                                <td class="py-3">
                                    <?php if ($modulo['total_examenes'] > 0): ?>
                                        <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle">
                                            <i class="fas fa-file-alt me-1"></i>
                                            <?= $modulo['total_examenes'] ?> examen<?= $modulo['total_examenes'] != 1 ? 'es' : '' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-light text-muted border border-secondary-subtle">
                                            <i class="fas fa-minus me-1"></i>
                                            Sin exámenes
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-center">
                                    <?php if (isset($modulo['activo']) && $modulo['activo'] == 1): ?>
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
                                </td>
                                <td class="py-3 text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= BASE_URL ?>/modulos/editar/<?= $modulo['id_modulo'] ?>" 
                                           class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                           data-bs-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit text-primary"></i>
                                        </a>
                                        
                                        <a href="<?= BASE_URL ?>/modulos/ver/<?= $modulo['id_modulo'] ?>" 
                                           class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                           data-bs-toggle="tooltip" title="Ver detalles">
                                            <i class="fas fa-eye text-info"></i>
                                        </a>
                                        
                                        <?php if (isset($modulo['activo']) && $modulo['activo'] == 1): ?>
                                            <a href="<?= BASE_URL ?>/modulos/desactivar/<?= $modulo['id_modulo'] ?>" 
                                               class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                               data-bs-toggle="tooltip" title="Desactivar módulo"
                                               onclick="return confirm('¿Está seguro de desactivar este módulo?')">
                                                <i class="fas fa-toggle-off text-warning"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>/modulos/activar/<?= $modulo['id_modulo'] ?>" 
                                               class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                               data-bs-toggle="tooltip" title="Activar módulo">
                                                <i class="fas fa-toggle-on text-success"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-light rounded-pill border px-2 shadow-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEliminar<?= $modulo['id_modulo'] ?>"
                                                title="Eliminar">
                                            <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Modal de confirmación de eliminación -->
                                    <div class="modal fade" id="modalEliminar<?= $modulo['id_modulo'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmar Eliminación</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    ¿Está seguro de que desea eliminar el módulo 
                                                    <strong><?= htmlspecialchars($modulo['titulo']) ?></strong>?
                                                    <br><br>
                                                    Esta acción no se puede deshacer.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <form method="POST" action="<?= BASE_URL ?>/modulos/eliminar/<?= $modulo['id_modulo'] ?>" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-puzzle-piece fa-3x text-muted"></i>
                </div>
                <h5 class="text-muted">No hay módulos registrados</h5>
                <p class="text-muted mb-4">Comienza creando tu primer módulo</p>
                <a href="<?= BASE_URL ?>/modulos/nuevo" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Crear primer módulo
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Paginación -->
<?php if ($datos['total_paginas'] > 1): ?>
    <nav aria-label="Paginación de módulos" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($datos['pagina_actual'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= BASE_URL ?>/modulos?<?= http_build_query(array_merge($datos['filtros'], ['pagina' => $datos['pagina_actual'] - 1, 'limite' => $datos['limite']])) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $datos['total_paginas']; $i++): ?>
                <li class="page-item <?= $i == $datos['pagina_actual'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>/modulos?<?= http_build_query(array_merge($datos['filtros'], ['pagina' => $i, 'limite' => $datos['limite']])) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <?php if ($datos['pagina_actual'] < $datos['total_paginas']): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= BASE_URL ?>/modulos?<?= http_build_query(array_merge($datos['filtros'], ['pagina' => $datos['pagina_actual'] + 1, 'limite' => $datos['limite']])) ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
