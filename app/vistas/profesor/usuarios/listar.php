<?php
/**
 * Vista de Listar Alumnos - AUTOEXAM2 (Rol Profesor)
 * AUTOEXAM2 - Siguiendo el patrón unificado de vistas profesor
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
                <i class="fas fa-users text-primary me-2"></i>
                Gestión de Alumnos
            </h1>
            <p class="text-muted mb-0">Curso: <?= htmlspecialchars($datos['curso']['nombre']) ?></p>
        </div>
        <div class="d-flex gap-2">
            <!-- Acciones masivas -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                        id="accionesMasivas" data-bs-toggle="dropdown" 
                        aria-expanded="false" disabled>
                    <i class="fas fa-tasks"></i> Acciones Masivas
                </button>
                <ul class="dropdown-menu" aria-labelledby="accionesMasivas">
                    <li><a class="dropdown-item" href="#" onclick="accionMasiva('desactivar')">
                        <i class="fas fa-ban text-danger"></i> Desactivar Seleccionados
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="accionMasiva('exportar')">
                        <i class="fas fa-download text-success"></i> Exportar Seleccionados
                    </a></li>
                </ul>
            </div>

            <!-- Exportar todos -->
            <a href="<?= BASE_URL ?>/cursos/exportar-alumnos?id=<?= $datos['curso']['id_curso'] ?>&<?= http_build_query($datos['filtros'] ?? []) ?>" 
               class="btn btn-outline-success">
                <i class="fas fa-file-export"></i> Exportar Filtrados
            </a>

            <!-- Importar alumnos -->
            <a href="<?= BASE_URL ?>/cursos/importar-alumnos?id=<?= $datos['curso']['id_curso'] ?>" class="btn btn-success">
                <i class="fas fa-upload"></i> Importar
            </a>
            
            <!-- Nuevo alumno -->
            <a href="<?= BASE_URL ?>/usuarios/crear?curso=<?= $datos['curso']['id_curso'] ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Alumno
            </a>
            
            <!-- Volver a cursos -->
            <a href="<?= BASE_URL ?>/cursos" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Cursos
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
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stats-icon bg-primary-gradient">
                                <i class="fas fa-users text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stats-label">TOTAL ALUMNOS</div>
                            <div class="stats-value"><?= $datos['total_registros'] ?? count($datos['alumnos']) ?></div>
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
                                <i class="fas fa-check-circle text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stats-label">ACTIVOS</div>
                            <div class="stats-value"><?= count(array_filter($datos['alumnos'], function($a) { return $a['activo']; })) ?></div>
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
                                <i class="fas fa-pause-circle text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stats-label">INACTIVOS</div>
                            <div class="stats-value"><?= count(array_filter($datos['alumnos'], function($a) { return !$a['activo']; })) ?></div>
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
                                <i class="fas fa-graduation-cap text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stats-label">CON ACTIVIDAD</div>
                            <div class="stats-value"><?= count(array_filter($datos['alumnos'], function($a) { return isset($a['ultimo_acceso']) && $a['ultimo_acceso']; })) ?></div>
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
                           value="<?= htmlspecialchars($datos['filtros']['buscar'] ?? '') ?>" 
                           placeholder="Nombre, apellidos o correo"
                           onkeyup="filtrarAlumnos()">
                </div>
                
                <div class="col-md-2">
                    <label for="estado" class="form-label">
                        <i class="fas fa-toggle-on me-1"></i>Estado
                    </label>
                    <select class="form-select" id="estado" name="estado" onchange="filtrarAlumnos()">
                        <option value="">Todos</option>
                        <option value="1" <?= (isset($datos['filtros']['estado']) && $datos['filtros']['estado'] == '1') ? 'selected' : '' ?>>Activos</option>
                        <option value="0" <?= (isset($datos['filtros']['estado']) && $datos['filtros']['estado'] == '0') ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="limite" class="form-label">
                        <i class="fas fa-list me-1"></i>Mostrar
                    </label>
                    <select class="form-select" id="limite" name="limite" onchange="filtrarAlumnos()">
                        <option value="5" <?= ($datos['limite'] ?? 10) == 5 ? 'selected' : '' ?>>5</option>
                        <option value="10" <?= ($datos['limite'] ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                        <option value="15" <?= ($datos['limite'] ?? 10) == 15 ? 'selected' : '' ?>>15</option>
                        <option value="20" <?= ($datos['limite'] ?? 10) == 20 ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= ($datos['limite'] ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/cursos/alumnos?id=<?= $datos['curso']['id_curso'] ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Limpiar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de alumnos -->
    <div class="card data-table shadow-sm mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list text-primary me-2"></i>Lista de alumnos
                </h5>
                <?php if (($datos['total_registros'] ?? count($datos['alumnos'])) > 0): ?>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-primary rounded-pill">
                            <?= $datos['total_registros'] ?? count($datos['alumnos']) ?> alumno<?= ($datos['total_registros'] ?? count($datos['alumnos'])) != 1 ? 's' : '' ?>
                        </span>
                        <div>
                            <input type="checkbox" id="seleccionar_todos" class="form-check-input me-2">
                            <label for="seleccionar_todos" class="form-check-label small">Seleccionar todos</label>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($datos['alumnos'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 border">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th style="width: 30px;">
                                    <input type="checkbox" id="select_all_header" class="form-check-input">
                                </th>
                                <th class="py-3 text-muted fw-semibold">
                                    <a href="<?= BASE_URL ?>/cursos/alumnos?id=<?= $datos['curso']['id_curso'] ?>&<?= http_build_query(array_merge($datos['filtros'] ?? [], ['ordenar_por' => 'id_usuario', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'id_usuario' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                        ID
                                        <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'id_usuario'): ?>
                                            <i class="ms-1 fas fa-sort-<?= $datos['filtros']['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                        <?php else: ?>
                                            <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th class="py-3 text-muted fw-semibold">
                                    <a href="<?= BASE_URL ?>/cursos/alumnos?id=<?= $datos['curso']['id_curso'] ?>&<?= http_build_query(array_merge($datos['filtros'] ?? [], ['ordenar_por' => 'nombre', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'nombre' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                        Alumno
                                        <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'nombre'): ?>
                                            <i class="ms-1 fas fa-sort-<?= $datos['filtros']['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                        <?php else: ?>
                                            <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th class="py-3 text-muted fw-semibold">
                                    <a href="<?= BASE_URL ?>/cursos/alumnos?id=<?= $datos['curso']['id_curso'] ?>&<?= http_build_query(array_merge($datos['filtros'] ?? [], ['ordenar_por' => 'correo', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'correo' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                        Correo
                                        <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'correo'): ?>
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
                            <?php foreach ($datos['alumnos'] as $alumno): ?>
                                <tr class="align-middle border-bottom">
                                    <td>
                                        <input type="checkbox" class="form-check-input item-checkbox" 
                                               value="<?= $alumno['id_usuario'] ?>">
                                    </td>
                                    <td class="py-3"><?= htmlspecialchars($alumno['id_usuario']) ?></td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-gradient rounded-3 me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 48px; height: 48px; min-width: 48px;">
                                                <i class="fas fa-user-graduate text-white"></i>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></strong>
                                                <?php if (isset($alumno['ultimo_acceso']) && $alumno['ultimo_acceso']): ?>
                                                    <small class="text-muted d-block">Último acceso: <?= date('d/m/Y H:i', strtotime($alumno['ultimo_acceso'])) ?></small>
                                                <?php else: ?>
                                                    <small class="text-muted d-block">Sin accesos registrados</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3"><?= htmlspecialchars($alumno['correo']) ?></td>
                                    <td class="py-3 text-center">
                                        <?php if ($alumno['activo']): ?>
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
                                            <a href="<?= BASE_URL ?>/usuarios/editar/<?= $alumno['id_usuario'] ?>" 
                                               class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                               data-bs-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit text-primary"></i>
                                            </a>
                                            
                                            <?php if ($alumno['activo']): ?>
                                                <a href="<?= BASE_URL ?>/usuarios/desactivar/<?= $alumno['id_usuario'] ?>" 
                                                   class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                                   data-bs-toggle="tooltip" title="Desactivar alumno"
                                                   onclick="return confirm('¿Está seguro de desactivar este alumno?')">
                                                    <i class="fas fa-toggle-off text-warning"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= BASE_URL ?>/usuarios/activar/<?= $alumno['id_usuario'] ?>" 
                                                   class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                                   data-bs-toggle="tooltip" title="Activar alumno">
                                                    <i class="fas fa-toggle-on text-success"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="<?= BASE_URL ?>/usuarios/ver/<?= $alumno['id_usuario'] ?>" 
                                               class="btn btn-sm btn-light rounded-pill border px-2 shadow-sm" 
                                               data-bs-toggle="tooltip" title="Ver perfil">
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
                        <i class="fas fa-users fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No hay alumnos asignados a este curso</h5>
                    <p class="text-muted mb-4">Comienza añadiendo estudiantes para gestionar sus exámenes</p>
                    <a href="<?= BASE_URL ?>/usuarios/crear?curso=<?= $datos['curso']['id_curso'] ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Añadir primer alumno
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Paginación -->
    <?php if (($datos['total_paginas'] ?? 1) > 1): ?>
        <nav aria-label="Paginación de alumnos" class="mt-4">
            <ul class="pagination justify-content-center">
        <?php if (($datos['pagina_actual'] ?? 1) > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= BASE_URL ?>/cursos/alumnos?id=<?= $datos['curso']['id_curso'] ?>&<?= http_build_query(array_merge($datos['filtros'] ?? [], ['pagina' => ($datos['pagina_actual'] ?? 1) - 1, 'limite' => $datos['limite'] ?? 10])) ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= ($datos['total_paginas'] ?? 1); $i++): ?>
            <li class="page-item <?= $i == ($datos['pagina_actual'] ?? 1) ? 'active' : '' ?>">
                <a class="page-link" href="<?= BASE_URL ?>/cursos/alumnos?id=<?= $datos['curso']['id_curso'] ?>&<?= http_build_query(array_merge($datos['filtros'] ?? [], ['pagina' => $i, 'limite' => $datos['limite'] ?? 10])) ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <?php if (($datos['pagina_actual'] ?? 1) < ($datos['total_paginas'] ?? 1)): ?>
            <li class="page-item">
                <a class="page-link" href="<?= BASE_URL ?>/cursos/alumnos?id=<?= $datos['curso']['id_curso'] ?>&<?= http_build_query(array_merge($datos['filtros'] ?? [], ['pagina' => ($datos['pagina_actual'] ?? 1) + 1, 'limite' => $datos['limite'] ?? 10])) ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<script>
function filtrarAlumnos() {
    const buscar = document.getElementById('buscar').value;
    const estado = document.getElementById('estado').value;
    const limite = document.getElementById('limite').value;
    
    const params = new URLSearchParams();
    params.append('id', '<?= $datos['curso']['id_curso'] ?>');
    if (buscar) params.append('buscar', buscar);
    if (estado) params.append('estado', estado);
    if (limite) params.append('limite', limite);
    
    const url = '<?= BASE_URL ?>/cursos/alumnos?' + params.toString();
    window.location.href = url;
}

// Debounce para la búsqueda
let timeoutId;
const originalFiltrarAlumnos = filtrarAlumnos;
filtrarAlumnos = function() {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(originalFiltrarAlumnos, 500);
};

// Selección múltiple
document.getElementById('seleccionar_todos').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    actualizarBotonAccionesMasivas();
});

document.getElementById('select_all_header').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    document.getElementById('seleccionar_todos').checked = this.checked;
    actualizarBotonAccionesMasivas();
});

document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', actualizarBotonAccionesMasivas);
});

function actualizarBotonAccionesMasivas() {
    const seleccionados = document.querySelectorAll('.item-checkbox:checked').length;
    const boton = document.getElementById('accionesMasivas');
    boton.disabled = seleccionados === 0;
    boton.innerHTML = seleccionados > 0 ? `<i class="fas fa-tasks"></i> Acciones (${seleccionados})` : '<i class="fas fa-tasks"></i> Acciones Masivas';
}

// Acciones masivas
function accionMasiva(accion) {
    const seleccionados = Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value);
    
    if (seleccionados.length === 0) {
        alert('Por favor, selecciona al menos un alumno.');
        return;
    }

    if (accion === 'exportar') {
        const ids = seleccionados.join(',');
        window.location.href = `<?= BASE_URL ?>/cursos/exportar-alumnos?id=<?= $datos['curso']['id_curso'] ?>&ids=${ids}`;
    } else if (accion === 'desactivar') {
        if (confirm(`¿Estás seguro de desactivar ${seleccionados.length} alumnos?`)) {
            // Implementar lógica de desactivación masiva
            console.log('Desactivar alumnos:', seleccionados);
        }
    }
}

// Tooltip para botones
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});
</script>