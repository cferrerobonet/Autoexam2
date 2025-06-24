<?php
/**
 * Vista para la gestión de cursos (Profesor)
 * AUTOEXAM2 - 17/06/2025
 */

// Variables para la vista
$cursos = $resultado['cursos'];
$total_registros = $resultado['total'];
$paginas = $resultado['paginas'];
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;

// Opciones de límite
$opciones_limite = [5, 10, 15, 20, 50];

// Parámetros de ordenación
$orden_columna = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'id_curso';
$orden_direccion = isset($_GET['orden']) ? $_GET['orden'] : 'ASC';

// Generar token CSRF para formularios
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

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
    /* El estilo curso-descripcion ahora se define en /publico/recursos/css/cursos.css */
    .cursor-pointer {
        cursor: pointer;
    }
    .table a.sort-icon {
        color: #6c757d;
        text-decoration: none;
    }
    .table a.sort-icon.active {
        color: #0d6efd;
    }
    .table a.sort-icon:hover {
        color: #0a58ca;
    }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-book text-primary me-2"></i>
                Gestión de Cursos
            </h1>
            <p class="text-muted mb-0">Administra y organiza todos tus cursos</p>
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
            <a href="<?= BASE_URL ?>/cursos/exportar?<?= http_build_query(array_filter($_GET, function($key) { return $key != 'pagina'; }, ARRAY_FILTER_USE_KEY)) ?>" 
               class="btn btn-outline-success">
                <i class="fas fa-file-export"></i> Exportar Filtrados
            </a>

            <!-- Nuevo curso -->
            <a href="<?= BASE_URL ?>/cursos/nuevo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Curso
            </a>
        </div>
    </div>

    <!-- Mensajes de estado -->
    <?php if (isset($_SESSION['exito'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['exito']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['exito']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Mensajes legacy -->
    <?php if(isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-info-circle"></i> <?= $_SESSION['mensaje'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
    unset($_SESSION['mensaje']); 
    unset($_SESSION['tipo_mensaje']);
    endif; 
    ?>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stats-icon bg-primary-gradient">
                                <i class="fas fa-book text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stats-label">TOTAL CURSOS</div>
                            <div class="stats-value"><?= $total_registros ?></div>
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
                            <div class="stats-label">CURSOS ACTIVOS</div>
                            <div class="stats-value"><?= count(array_filter($cursos, function($c) { return $c['activo']; })) ?></div>
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
                                <i class="fas fa-users text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stats-label">TOTAL ALUMNOS</div>
                            <div class="stats-value"><?= array_sum(array_column($cursos, 'total_alumnos')) ?></div>
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
                                <i class="fas fa-puzzle-piece text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stats-label">TOTAL MÓDULOS</div>
                            <div class="stats-value"><?= array_sum(array_column($cursos, 'total_modulos')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y opciones -->
    <div class="card filters-card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="fas fa-filter text-primary me-2"></i> Filtros y opciones
            </h5>
        </div>
        <div class="card-body">
            <form id="filtroForm" action="<?= BASE_URL ?>/cursos" method="GET" class="row row-cols-lg-auto g-3 align-items-end justify-content-center">
                <!-- Filtro por nombre -->
                <div class="col-lg-4">
                    <label for="nombre" class="form-label d-flex align-items-center gap-1">
                        <i class="fas fa-font"></i> Nombre
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-book"></i></span>
                        <input type="text" class="form-control filtro-proactivo" id="nombre" name="nombre" 
                               value="<?= isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '' ?>"
                               placeholder="Buscar por nombre..."
                               autocomplete="off">
                        <?php if(isset($_GET['nombre']) && !empty($_GET['nombre'])): ?>
                        <button type="button" class="btn btn-outline-secondary borrar-filtro" data-target="nombre">
                            <i class="fas fa-times"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Filtro por estado -->
                <div class="col-lg-2">
                    <label for="activo" class="form-label d-flex align-items-center gap-1">
                        <i class="fas fa-toggle-on"></i> Estado
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                        <select class="form-select filtro-proactivo" id="activo" name="activo">
                            <option value="">Todos</option>
                            <option value="1" <?= (isset($_GET['activo']) && $_GET['activo'] == '1') ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= (isset($_GET['activo']) && $_GET['activo'] == '0') ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                        <?php if(isset($_GET['activo']) && $_GET['activo'] !== ''): ?>
                        <button type="button" class="btn btn-outline-secondary borrar-filtro" data-target="activo">
                            <i class="fas fa-times"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Registros por página -->
                <div class="col-lg-2">
                    <label for="limite" class="form-label d-flex align-items-center gap-1">
                        <i class="fas fa-list-ol"></i> Por página
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-sort-numeric-down"></i></span>
                        <select class="form-select filtro-proactivo" id="limite" name="limite">
                            <?php foreach ($opciones_limite as $opcion): ?>
                            <option value="<?= $opcion ?>" <?= $limite == $opcion ? 'selected' : '' ?>><?= $opcion ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Mantener parámetros de ordenación -->
                <?php if(isset($_GET['ordenar_por'])): ?>
                <input type="hidden" name="ordenar_por" value="<?= htmlspecialchars($_GET['ordenar_por']) ?>">
                <?php endif; ?>
                <?php if(isset($_GET['orden'])): ?>
                <input type="hidden" name="orden" value="<?= htmlspecialchars($_GET['orden']) ?>">
                <?php endif; ?>
                
                <!-- Botón para limpiar todos los filtros -->
                <div class="col-lg-2">
                    <a href="<?= BASE_URL ?>/cursos" class="btn btn-outline-secondary rounded-pill w-100">
                        <i class="fas fa-broom me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de cursos -->
    <div class="card data-table shadow-sm mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-table text-primary me-2"></i>Cursos
                </h5>
                <?php if (count($cursos) > 0): ?>
                    <span class="badge bg-primary rounded-pill">
                        <?= $total_registros ?> curso<?= $total_registros != 1 ? 's' : '' ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($cursos) > 0): ?>
            <form id="formSeleccion" method="POST" action="<?= BASE_URL ?>/cursos/accionMasiva">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="accion" id="accionMasiva" value="">
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" width="40">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                    </div>
                                </th>
                                <th scope="col" class="text-center" width="60">
                                    <a href="<?= BASE_URL ?>/cursos?<?= http_build_query(array_merge(array_filter($_GET, function($key) { return $key != 'pagina' && $key != 'ordenar_por' && $key != 'orden'; }, ARRAY_FILTER_USE_KEY), ['ordenar_por' => 'id_curso', 'orden' => ($orden_columna == 'id_curso' && $orden_direccion == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="sort-icon <?= $orden_columna == 'id_curso' ? 'active' : '' ?>">
                                        ID
                                        <?php if ($orden_columna == 'id_curso'): ?>
                                            <i class="fas fa-sort-<?= $orden_direccion == 'ASC' ? 'up' : 'down' ?> ms-1"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="<?= BASE_URL ?>/cursos?<?= http_build_query(array_merge(array_filter($_GET, function($key) { return $key != 'pagina' && $key != 'ordenar_por' && $key != 'orden'; }, ARRAY_FILTER_USE_KEY), ['ordenar_por' => 'nombre_curso', 'orden' => ($orden_columna == 'nombre_curso' && $orden_direccion == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="sort-icon <?= $orden_columna == 'nombre_curso' ? 'active' : '' ?>">
                                        Nombre del Curso
                                        <?php if ($orden_columna == 'nombre_curso'): ?>
                                            <i class="fas fa-sort-<?= $orden_direccion == 'ASC' ? 'up' : 'down' ?> ms-1"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th scope="col" class="text-center" width="100">
                                    <a href="<?= BASE_URL ?>/cursos?<?= http_build_query(array_merge(array_filter($_GET, function($key) { return $key != 'pagina' && $key != 'ordenar_por' && $key != 'orden'; }, ARRAY_FILTER_USE_KEY), ['ordenar_por' => 'activo', 'orden' => ($orden_columna == 'activo' && $orden_direccion == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="sort-icon <?= $orden_columna == 'activo' ? 'active' : '' ?>">
                                        Estado
                                        <?php if ($orden_columna == 'activo'): ?>
                                            <i class="fas fa-sort-<?= $orden_direccion == 'ASC' ? 'up' : 'down' ?> ms-1"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th scope="col" class="text-center" width="100">Alumnos</th>
                                <th scope="col" class="text-center" width="150">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($cursos as $curso): ?>
                            <tr class="<?= $curso['activo'] == 0 ? 'bg-light opacity-75' : '' ?>">
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input checkItem" type="checkbox" name="ids[]" value="<?= $curso['id_curso'] ?>">
                                    </div>
                                </td>
                                <td class="text-center fw-bold"><?= $curso['id_curso'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="ms-2">
                                            <h6 class="mb-0 <?= $curso['activo'] == 0 ? 'text-muted' : '' ?>">
                                                <?= htmlspecialchars($curso['nombre_curso']) ?>
                                                <?php if ($curso['activo'] == 0): ?>
                                                <span class="ms-2 small fst-italic">(desactivado)</span>
                                                <?php endif; ?>
                                            </h6>
                                            <?php if (!empty($curso['descripcion'])): ?>
                                            <small class="curso-descripcion"><?= mb_substr(htmlspecialchars($curso['descripcion']), 0, 50) . (mb_strlen($curso['descripcion']) > 50 ? '...' : '') ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if($curso['activo'] == 1): ?>
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                                        <i class="fas fa-check-circle me-1"></i>Activo
                                    </span>
                                    <?php else: ?>
                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="fas fa-ban me-1"></i>Inactivo
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/cursos/asignarAlumnos?id=<?= $curso['id_curso'] ?>" 
                                       class="badge rounded-pill bg-primary text-white border border-primary text-decoration-none" title="Gestionar alumnos">
                                        <i class="fas fa-user-graduate me-1"></i> <?= $curso['num_alumnos'] ?? '0' ?> alumnos
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group" aria-label="Acciones">
                                        <a href="<?= BASE_URL ?>/cursos/ver?id=<?= $curso['id_curso'] ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver curso">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/cursos/editar?id=<?= $curso['id_curso'] ?>" 
                                           class="btn btn-sm btn-outline-warning" title="Editar curso">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/cursos/alumnos?id=<?= $curso['id_curso'] ?>" 
                                           class="btn btn-sm btn-outline-success" title="Editar alumnos">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <?php if($curso['activo'] == 1): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                title="Desactivar curso" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#desactivarModal" 
                                                data-id="<?= $curso['id_curso'] ?>"
                                                data-nombre="<?= htmlspecialchars($curso['nombre_curso']) ?>">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                        <?php else: ?>
                                        <a href="<?= BASE_URL ?>/cursos/activar?id=<?= $curso['id_curso'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                                           class="btn btn-sm btn-outline-success" 
                                           title="Activar curso"
                                           onclick="return confirm('¿Está seguro que desea activar este curso?')">
                                            <i class="fas fa-power-off"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
                
            </form>
            
            <!-- Paginación -->
            <?php if ($paginas > 1): ?>
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Paginación">
                    <ul class="pagination">
                        <?php if($pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= BASE_URL ?>/cursos?pagina=1<?= http_build_query(array_filter($_GET, function($key) { return $key != 'pagina'; }, ARRAY_FILTER_USE_KEY)) ? '&' . http_build_query(array_filter($_GET, function($key) { return $key != 'pagina'; }, ARRAY_FILTER_USE_KEY)) : '' ?>" aria-label="Primera">
                                <span aria-hidden="true"><i class="fas fa-angle-double-left"></i></span>
                                <span class="visually-hidden">Primera</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?= BASE_URL ?>/cursos?pagina=<?= $pagina_actual - 1 ?><?= http_build_query(array_filter($_GET, function($key) { return $key != 'pagina'; }, ARRAY_FILTER_USE_KEY)) ? '&' . http_build_query(array_filter($_GET, function($key) { return $key != 'pagina'; }, ARRAY_FILTER_USE_KEY)) : '' ?>" aria-label="Anterior">
                                <span aria-hidden="true"><i class="fas fa-angle-left"></i></span>
                                <span class="visually-hidden">Anterior</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        $inicio = max(1, $pagina_actual - 2);
                        $fin = min($paginas, $pagina_actual + 2);
                        
                        // Si estamos cerca del inicio o final, ajustar el rango
                        if ($inicio === 1) {
                            $fin = min($paginas, 5);
                        }
                        if ($fin === $paginas) {
                            $inicio = max(1, $paginas - 4);
                        }
                        
                        for ($i = $inicio; $i <= $fin; $i++):
                        ?>
                        <li class="page-item <?= $pagina_actual == $i ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/cursos?pagina=<?= $i ?><?= http_build_query(array_filter($_GET, function($key) { return $key != 'pagina'; }, ARRAY_FILTER_USE_KEY)) ? '&' . http_build_query(array_filter($_GET, function($key) { return $key != 'pagina'; }, ARRAY_FILTER_USE_KEY)) : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if($pagina_actual < $paginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= BASE_URL ?>/cursos?pagina=<?= $pagina_actual + 1 ?><?= http_build_query(array_filter($_GET, function($key) { return $key != 'pagina'; }, ARRAY_FILTER_USE_KEY)) ? '&' . http_build_query(array_filter($_GET, function($key) { return $key != 'pagina'; }, ARRAY_FILTER_USE_KEY)) : '' ?>" aria-label="Siguiente">
                                <span aria-hidden="true"><i class="fas fa-angle-right"></i></span>
                                <span class="visually-hidden">Siguiente</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?= BASE_URL ?>/cursos?pagina=<?= $paginas ?><?= http_build_query(array_filter($_GET, function($key) { return $key != 'pagina'; }, ARRAY_FILTER_USE_KEY)) ? '&' . http_build_query(array_filter($_GET, function($key) { return $key != 'pagina'; }, ARRAY_FILTER_USE_KEY)) : '' ?>" aria-label="Última">
                                <span aria-hidden="true"><i class="fas fa-angle-double-right"></i></span>
                                <span class="visually-hidden">Última</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="alert alert-info mb-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle fa-2x me-3 text-primary"></i>
                    <div>
                        <strong>No se encontraron cursos</strong><br>
                        No tienes cursos asignados o no se encontraron cursos con los filtros seleccionados.
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-light text-muted small">
            <?php if ($total_registros > 0): ?>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table me-1"></i> 
                    Mostrando <strong><?= count($cursos) ?></strong> de <strong><?= $total_registros ?></strong> cursos
                </div>
                <div>
                    <i class="fas fa-file-alt me-1"></i>
                    Página <strong><?= $pagina_actual ?></strong> de <strong><?= $paginas ?></strong>
                </div>
            </div>
            <?php else: ?>
            <div class="text-center">
                <i class="fas fa-info-circle me-1"></i> 
                No se encontraron cursos
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de confirmación para desactivar -->
<div class="modal fade" id="desactivarModal" tabindex="-1" aria-labelledby="desactivarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="desactivarModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Desactivación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="text-danger me-3">
                        <i class="fas fa-ban fa-2x"></i>
                    </div>
                    <div>
                        <p class="mb-0 font-weight-bold">¿Está seguro que desea desactivar el curso <strong id="cursoNombre"></strong>?</p>
                    </div>
                </div>
                <div class="alert alert-warning">
                    <p class="mb-2">Esta acción:</p>
                    <ul class="mb-0">
                        <li>Ocultará el curso de las interfaces</li>
                        <li>Desactivará módulos y exámenes asociados</li>
                        <li>Desasignará alumnos y desvinculará al profesor</li>
                    </ul>
                </div>
                <p class="mb-0 text-danger"><strong>Esta acción no elimina datos de la base de datos.</strong></p>
            </div>
            <div class="modal-footer bg-light">
                <form action="<?= BASE_URL ?>/cursos/desactivar" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id_curso" id="idCursoEliminar">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger rounded-pill">
                        <i class="fas fa-power-off me-1"></i> Desactivar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para acciones masivas -->
<div class="modal fade" id="accionMasivaModal" tabindex="-1" aria-labelledby="accionMasivaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="accionMasivaModalLabel">
                    <i class="fas fa-tasks me-2"></i> Confirmar Acción
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="accionMasivaContent">
                    <!-- El contenido se cargará dinámicamente según la acción -->
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" id="confirmarAccionMasiva" class="btn btn-primary rounded-pill">
                    <i class="fas fa-check me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal para desactivar curso
        const desactivarModal = document.getElementById('desactivarModal');
        if (desactivarModal) {
            desactivarModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const nombre = button.getAttribute('data-nombre');
                
                document.getElementById('idCursoEliminar').value = id;
                document.getElementById('cursoNombre').textContent = nombre;
            });
        }
        
        // Checkbox "Seleccionar todos"
        const checkAll = document.getElementById('checkAll');
        const checkItems = document.querySelectorAll('.checkItem');
        const accionesMasivas = document.getElementById('accionesMasivas');
        
        if (checkAll && checkItems.length > 0 && accionesMasivas) {
            checkAll.addEventListener('change', function() {
                const isChecked = this.checked;
                
                checkItems.forEach(item => {
                    item.checked = isChecked;
                });
                
                // Habilitar/deshabilitar botón de acciones masivas
                actualizarEstadoAccionesMasivas();
            });
            
            checkItems.forEach(item => {
                item.addEventListener('change', actualizarEstadoAccionesMasivas);
            });
            
            function actualizarEstadoAccionesMasivas() {
                const checkedCount = document.querySelectorAll('.checkItem:checked').length;
                accionesMasivas.disabled = checkedCount === 0;
            }
        }
    });
    
    // Función para manejar acciones masivas
    function accionMasiva(accion) {
        const checkboxesSeleccionados = document.querySelectorAll('.checkItem:checked');
        const formSeleccion = document.getElementById('formSeleccion');
        const accionMasivaInput = document.getElementById('accionMasiva');
        const accionMasivaModal = new bootstrap.Modal(document.getElementById('accionMasivaModal'));
        const accionMasivaContent = document.getElementById('accionMasivaContent');
        const confirmarBtn = document.getElementById('confirmarAccionMasiva');
        
        if (checkboxesSeleccionados.length === 0) {
            alert('Debe seleccionar al menos un curso para realizar esta acción.');
            return;
        }
        
        // Configurar el modal según la acción
        let titulo, contenido, colorBoton, iconoBoton, textoBoton;
        
        switch(accion) {
            case 'desactivar':
                titulo = 'Desactivar Cursos';
                contenido = `
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-danger me-3">
                            <i class="fas fa-ban fa-2x"></i>
                        </div>
                        <div>
                            <p class="mb-0">¿Está seguro que desea <strong>desactivar ${checkboxesSeleccionados.length} curso(s)</strong>?</p>
                        </div>
                    </div>
                    <div class="alert alert-warning">
                        <p class="mb-2">Esta acción:</p>
                        <ul class="mb-0">
                            <li>Ocultará los cursos de las interfaces</li>
                            <li>Desactivará módulos y exámenes asociados</li>
                            <li>Desasignará alumnos y desvinculará profesores</li>
                        </ul>
                    </div>
                `;
                colorBoton = 'btn-danger';
                iconoBoton = 'ban';
                textoBoton = 'Desactivar';
                break;
                
            case 'exportar':
                titulo = 'Exportar Cursos';
                contenido = `
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-success me-3">
                            <i class="fas fa-file-export fa-2x"></i>
                        </div>
                        <div>
                            <p class="mb-0">¿Está seguro que desea <strong>exportar ${checkboxesSeleccionados.length} curso(s)</strong>?</p>
                        </div>
                    </div>
                    <p>Se generará un archivo CSV con la información de los cursos seleccionados.</p>
                `;
                colorBoton = 'btn-success';
                iconoBoton = 'file-export';
                textoBoton = 'Exportar';
                break;
                
            default:
                return;
        }
        
        document.getElementById('accionMasivaModalLabel').innerHTML = `<i class="fas fa-tasks me-2"></i> ${titulo}`;
        accionMasivaContent.innerHTML = contenido;
        confirmarBtn.className = `btn rounded-pill ${colorBoton}`;
        confirmarBtn.innerHTML = `<i class="fas fa-${iconoBoton} me-1"></i> ${textoBoton}`;
        
        // Configurar el botón de confirmación
        confirmarBtn.onclick = function() {
            accionMasivaInput.value = accion;
            formSeleccion.action = `${BASE_URL}/cursos/accionMasiva`;
            formSeleccion.submit();
        };
        
        accionMasivaModal.show();
    }
    
    // Cargar script de estilos para cursos
    const linkCss = document.createElement('link');
    linkCss.rel = 'stylesheet';
    linkCss.href = '<?= BASE_URL ?>/recursos/css/cursos.css';
    document.head.appendChild(linkCss);

    // Cargar script para las descripciones de cursos
    const script = document.createElement('script');
    script.src = '<?= BASE_URL ?>/recursos/js/cursos.js';
    document.body.appendChild(script);

    // Filtros proactivos
    document.addEventListener('DOMContentLoaded', function() {
        // Variables para el tiempo de espera
        let timeoutId = null;
        
        // Elementos del formulario de filtros
        const filtrosProactivos = document.querySelectorAll('.filtro-proactivo');
        const filtroForm = document.getElementById('filtroForm');
        
        // Eventos para los filtros proactivos
        filtrosProactivos.forEach(filtro => {
            filtro.addEventListener('input', function(e) {
                // Cancelar el timeout anterior si existe
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }
                
                // Configurar un nuevo timeout
                timeoutId = setTimeout(() => {
                    filtroForm.submit();
                }, 800); // Esperar 800ms antes de enviar el formulario
            });
            
            // Para selects, aplicar cambio inmediatamente
            if (filtro.tagName === 'SELECT') {
                filtro.addEventListener('change', function(e) {
                    filtroForm.submit();
                });
            }
        });
        
        // Botones para borrar filtros individualmente
        const botonesBorrarFiltro = document.querySelectorAll('.borrar-filtro');
        botonesBorrarFiltro.forEach(boton => {
            boton.addEventListener('click', function(e) {
                const target = this.getAttribute('data-target');
                if (target) {
                    const input = document.getElementById(target);
                    if (input) {
                        if (input.tagName === 'SELECT') {
                            input.selectedIndex = 0;
                        } else {
                            input.value = '';
                        }
                        filtroForm.submit();
                    }
                }
            });
        });
    });
</script>
