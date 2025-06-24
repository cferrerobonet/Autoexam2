<?php
/**
 * Vista de listado de módulos para profesor
 * AUTOEXAM2 - Siguiendo el patrón de usuarios y cursos
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'profesor') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<div class="container-fluid px-4 py-4">
        <!-- Header principal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 fw-bold text-dark mb-2">
                    <i class="fas fa-puzzle-piece text-primary me-2"></i>
                    Gestión de Módulos
                </h1>
                <p class="text-muted mb-0">Gestiona tus módulos asignados y sus contenidos</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/modulos/nuevo" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nuevo Módulo
                </a>
            </div>
        </div>

        <!-- Mensajes de estado -->
        <?php if (isset($_SESSION['exito'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['exito']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-primary-gradient">
                                    <i class="fas fa-puzzle-piece text-white fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="stats-label">TOTAL MÓDULOS</div>
                                <div class="stats-value"><?= $datos['total_registros'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-success-gradient">
                                    <i class="fas fa-file-alt text-white fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="stats-label">TOTAL EXÁMENES</div>
                                <div class="stats-value"><?= array_sum(array_column($datos['modulos'], 'total_examenes')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-info-gradient">
                                    <i class="fas fa-check-circle text-white fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="stats-label">ACTIVOS</div>
                                <div class="stats-value"><?= count(array_filter($datos['modulos'], function($m) { return $m['total_examenes'] > 0; })) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-warning-gradient">
                                    <i class="fas fa-exclamation-triangle text-white fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="stats-label">SIN EXÁMENES</div>
                                <div class="stats-value"><?= count(array_filter($datos['modulos'], function($m) { return $m['total_examenes'] == 0; })) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros y opciones -->
        <div class="card filters-card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter text-primary me-2"></i>Filtros de búsqueda
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="buscar" class="form-label">
                            <i class="fas fa-search me-1"></i>Buscar
                        </label>
                        <input type="text" class="form-control" id="buscar" name="buscar" 
                               value="<?= htmlspecialchars($datos['filtros']['buscar']) ?>" 
                               placeholder="Título o descripción del módulo"
                               onkeyup="filtrarModulos()">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="estado" class="form-label">
                            <i class="fas fa-toggle-on me-1"></i>Estado
                        </label>
                        <select class="form-select" id="estado" name="estado" onchange="filtrarModulos()">
                            <option value="">Todos</option>
                            <option value="1" <?= (isset($datos['filtros']['estado']) && $datos['filtros']['estado'] == '1') ? 'selected' : '' ?>>Activos</option>
                            <option value="0" <?= (isset($datos['filtros']['estado']) && $datos['filtros']['estado'] == '0') ? 'selected' : '' ?>>Inactivos</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="limite" class="form-label">
                            <i class="fas fa-list me-1"></i>Mostrar
                        </label>
                        <select class="form-select" id="limite" name="limite" onchange="filtrarModulos()">
                            <option value="5" <?= $datos['limite'] == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $datos['limite'] == 10 ? 'selected' : '' ?>>10</option>
                            <option value="15" <?= $datos['limite'] == 15 ? 'selected' : '' ?>>15</option>
                            <option value="20" <?= $datos['limite'] == 20 ? 'selected' : '' ?>>20</option>
                            <option value="50" <?= $datos['limite'] == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="<?= BASE_URL ?>/modulos" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de módulos -->
        <div class="card data-table shadow-sm mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list text-primary me-2"></i>Lista de módulos
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
                                        
                                        <a href="<?= BASE_URL ?>/examenes/nuevo?modulo=<?= $modulo['id_modulo'] ?>" 
                                           class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                           data-bs-toggle="tooltip" title="Crear examen">
                                            <i class="fas fa-plus text-success"></i>
                                        </a>
                                        
                                        <a href="<?= BASE_URL ?>/modulos/ver/<?= $modulo['id_modulo'] ?>" 
                                           class="btn btn-sm btn-light rounded-pill border px-2 shadow-sm" 
                                           data-bs-toggle="tooltip" title="Ver detalles">
                                            <i class="fas fa-eye text-info"></i>
                                        </a>
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
                <h5 class="text-muted">No tienes módulos asignados</h5>
                <p class="text-muted mb-4">Comienza creando tu primer módulo para organizar tus exámenes</p>
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

</div>

<script>
function filtrarModulos() {
    const buscar = document.getElementById('buscar').value;
    const estado = document.getElementById('estado').value;
    const limite = document.getElementById('limite').value;
    
    const params = new URLSearchParams();
    if (buscar) params.append('buscar', buscar);
    if (estado) params.append('estado', estado);
    if (limite) params.append('limite', limite);
    
    const url = '<?= BASE_URL ?>/modulos' + (params.toString() ? '?' + params.toString() : '');
    window.location.href = url;
}

// Debounce para la búsqueda
let timeoutId;
const originalFiltrarModulos = filtrarModulos;
filtrarModulos = function() {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(originalFiltrarModulos, 500);
};
</script>
</div>
