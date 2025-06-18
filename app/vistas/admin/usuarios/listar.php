<?php
/**
 * Vista de Listado de Usuarios - AUTOEXAM2
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<?php require_once APP_PATH . '/vistas/parciales/head_admin.php'; ?>

<body class="bg-light">
    <?php require_once APP_PATH . '/vistas/parciales/navbar_admin.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Estilos personalizados -->
                <link rel="stylesheet" href="<?= BASE_URL ?>/recursos/css/cursos.css">
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
                </style>

                <!-- Header de la página -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-users"></i> Gestión de Usuarios</h1>
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
                        <a href="<?= BASE_URL ?>/usuarios/exportar?<?= http_build_query($datos['filtros']) ?>" 
                           class="btn btn-outline-success">
                            <i class="fas fa-file-export"></i> Exportar Filtrados
                        </a>

                        <!-- Nuevo usuario -->
                        <a href="<?= BASE_URL ?>/usuarios/crear" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Usuario
                        </a>
                        
                        <!-- Importar usuarios -->
                        <a href="<?= BASE_URL ?>/usuarios/importar" class="btn btn-success">
                            <i class="fas fa-upload"></i> Importar
                        </a>
                        
                        <!-- Estadísticas -->
                        <a href="<?= BASE_URL ?>/usuarios/estadisticas" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Estadísticas
                        </a>
                    </div>
                </div>

                <!-- Mensajes de estado -->
                <?php if (isset($_SESSION['exito'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['exito']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['exito']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Filtros y opciones -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-filter me-1"></i> Filtros y opciones
                    </div>
                    <div class="card-body">
                        <form method="GET" action="<?= BASE_URL ?>/usuarios" class="row g-3" id="formFiltros">
                            <div class="col-md-4">
                                <label for="buscar" class="form-label"><i class="fas fa-search me-2"></i>Buscar</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control filtro-auto" id="buscar" name="buscar" 
                                           value="<?= htmlspecialchars($datos['filtros']['buscar']) ?>" 
                                           placeholder="Nombre, apellidos o correo">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="rol" class="form-label"><i class="fas fa-user-tag me-2"></i>Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user-tag text-muted"></i></span>
                                    <select class="form-select filtro-auto" id="rol" name="rol">
                                        <option value="">Todos</option>
                                        <option value="admin" <?= $datos['filtros']['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="profesor" <?= $datos['filtros']['rol'] === 'profesor' ? 'selected' : '' ?>>Profesor</option>
                                    <option value="alumno" <?= $datos['filtros']['rol'] === 'alumno' ? 'selected' : '' ?>>Alumno</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="activo" class="form-label"><i class="fas fa-toggle-on me-2"></i>Estado</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-toggle-on text-muted"></i></span>
                                    <select class="form-select filtro-auto" id="activo" name="activo">
                                        <option value="">Todos</option>
                                        <option value="1" <?= $datos['filtros']['activo'] === '1' ? 'selected' : '' ?>>Activo</option>
                                        <option value="0" <?= $datos['filtros']['activo'] === '0' ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="por_pagina" class="form-label"><i class="fas fa-list-ol me-2"></i>Por página</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-list-ol text-muted"></i></span>
                                    <select class="form-select filtro-auto" id="por_pagina" name="por_pagina">
                                        <option value="5" <?= $datos['por_pagina'] == 5 ? 'selected' : '' ?>>5</option>
                                        <option value="10" <?= $datos['por_pagina'] == 10 ? 'selected' : '' ?>>10</option>
                                        <option value="15" <?= $datos['por_pagina'] == 15 ? 'selected' : '' ?>>15</option>
                                        <option value="20" <?= $datos['por_pagina'] == 20 ? 'selected' : '' ?>>20</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <a href="<?= BASE_URL ?>/usuarios" class="btn btn-light border shadow-sm rounded-pill w-100">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de usuarios -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-users text-primary me-2"></i> Usuarios 
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 rounded-pill"><?= $datos['total_usuarios'] ?> total</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($datos['usuarios'])): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No se encontraron usuarios</h5>
                                <p class="text-muted">Ajusta los filtros o crea un nuevo usuario.</p>
                            </div>
                        <?php else: ?>
                            <!-- Formulario para acciones masivas -->
                            <form id="formAccionMasiva" method="POST" action="<?= BASE_URL ?>/usuarios/accion-masiva">
                                <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                                <input type="hidden" name="accion" id="accion_masiva" value="">
                                
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 border">
                                        <thead class="bg-light border-bottom">
                                            <tr>
                                                <th width="40" class="py-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="seleccionarTodos" onchange="toggleTodos()">
                                                        <label class="form-check-label" for="seleccionarTodos">
                                                            <span class="visually-hidden">Seleccionar todos</span>
                                                        </label>
                                                    </div>
                                                </th>
                                                <th class="py-3 text-muted fw-semibold">
                                                    <a href="<?= BASE_URL ?>/usuarios?<?= http_build_query(array_merge($datos['filtros'], ['ordenar_por' => 'id_usuario', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'id_usuario' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                                        ID
                                                        <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'id_usuario'): ?>
                                                            <i class="ms-1 fas fa-sort-<?= $datos['filtros']['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                                        <?php else: ?>
                                                            <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                </th>
                                                <th class="py-3 text-muted fw-semibold">
                                                    <a href="<?= BASE_URL ?>/usuarios?<?= http_build_query(array_merge($datos['filtros'], ['ordenar_por' => 'apellidos', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'apellidos' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                                        Nombre
                                                        <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'apellidos'): ?>
                                                            <i class="ms-1 fas fa-sort-<?= $datos['filtros']['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                                        <?php else: ?>
                                                            <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                </th>
                                                <th class="py-3 text-muted fw-semibold">Correo</th>
                                                <th class="py-3 text-muted fw-semibold">
                                                    <a href="<?= BASE_URL ?>/usuarios?<?= http_build_query(array_merge($datos['filtros'], ['ordenar_por' => 'rol', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'rol' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                                        Rol
                                                        <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'rol'): ?>
                                                            <i class="ms-1 fas fa-sort-<?= $datos['filtros']['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                                        <?php else: ?>
                                                            <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                </th>
                                                <th class="py-3 text-muted fw-semibold">
                                                    <a href="<?= BASE_URL ?>/usuarios?<?= http_build_query(array_merge($datos['filtros'], ['ordenar_por' => 'activo', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'activo' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                                        Estado
                                                        <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'activo'): ?>
                                                            <i class="ms-1 fas fa-sort-<?= $datos['filtros']['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                                        <?php else: ?>
                                                            <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                </th>
                                                <th class="py-3 text-muted fw-semibold">
                                                    <a href="<?= BASE_URL ?>/usuarios?<?= http_build_query(array_merge($datos['filtros'], ['ordenar_por' => 'ultimo_acceso', 'orden' => (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'ultimo_acceso' && isset($datos['filtros']['orden']) && $datos['filtros']['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                                        Último Acceso
                                                        <?php if (isset($datos['filtros']['ordenar_por']) && $datos['filtros']['ordenar_por'] == 'ultimo_acceso'): ?>
                                                            <i class="ms-1 fas fa-sort-<?= $datos['filtros']['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                                        <?php else: ?>
                                                            <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                </th>
                                                <th class="py-3 text-muted fw-semibold text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($datos['usuarios'] as $usuario): ?>
                                                <tr class="<?= $usuario['activo'] ? 'align-middle border-bottom' : 'align-middle border-bottom bg-light' ?>">
                                                    <td class="py-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input usuario-checkbox" 
                                                                   type="checkbox" 
                                                                   name="usuarios[]" 
                                                                   value="<?= $usuario['id_usuario'] ?>"
                                                                   id="usuario_<?= $usuario['id_usuario'] ?>"
                                                                   onchange="toggleAccionesMasivas()"
                                                                   <?= $usuario['id_usuario'] == $_SESSION['id_usuario'] ? 'disabled' : '' ?>>
                                                            <label class="form-check-label" for="usuario_<?= $usuario['id_usuario'] ?>">
                                                                <span class="visually-hidden">Seleccionar usuario</span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!empty($usuario['foto'])): ?>
                                                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($usuario['foto']) ?>" 
                                                                     class="rounded-circle me-2" width="32" height="32" 
                                                                     alt="Avatar" style="object-fit: cover;">
                                                            <?php else: ?>
                                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                                     style="width: 32px; height: 32px; min-width: 32px;">
                                                                    <i class="fas fa-user text-white" style="font-size: 14px;"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div>
                                                                <strong><?= htmlspecialchars($usuario['apellidos'] . ', ' . $usuario['nombre']) ?></strong>
                                                                <?php if ($usuario['rol'] === 'alumno'): ?>
                                                                    <?php if (!empty($usuario['nombre_curso'])): ?>
                                                                        <small class="curso-descripcion d-block"><i class="fas fa-book-reader me-1"></i><?= htmlspecialchars($usuario['nombre_curso']) ?></small>
                                                                        <?php if (!empty($usuario['curso_descripcion'])): ?>
                                                                            <small class="text-muted d-block ps-3 fst-italic"><?= htmlspecialchars(mb_substr($usuario['curso_descripcion'], 0, 50)) . (mb_strlen($usuario['curso_descripcion']) > 50 ? '...' : '') ?></small>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                <td><?= htmlspecialchars($usuario['correo']) ?></td>
                                                <td>
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
                                                    <span class="badge rounded-pill <?= $rolClases[$usuario['rol']] ?>">
                                                        <i class="fas <?= $rolIconos[$usuario['rol']] ?>"></i>
                                                        <?= ucfirst($usuario['rol']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($usuario['activo']): ?>
                                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                                                            <i class="fas fa-check"></i> Activo
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                            <i class="fas fa-times"></i> Inactivo
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($usuario['ultimo_acceso']): ?>
                                                        <small><?= date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) ?></small>
                                                    <?php else: ?>
                                                        <small class="text-muted">Nunca</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?= BASE_URL ?>/usuarios/editar/<?= $usuario['id_usuario'] ?>" 
                                                           class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                                           data-bs-toggle="tooltip" title="Editar">
                                                            <i class="fas fa-edit text-primary"></i>
                                                        </a>
                                                        
                                                        <a href="<?= BASE_URL ?>/usuarios/historial/<?= $usuario['id_usuario'] ?>" 
                                                           class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                                           data-bs-toggle="tooltip" title="Ver Historial">
                                                            <i class="fas fa-history text-info"></i>
                                                        </a>
                                                        
                                                        <?php 
                                                        $esAdminPrincipal = ($usuario['id_usuario'] == 1 || 
                                                                          ($usuario['rol'] == 'admin' && 
                                                                          $usuario['correo'] == 'no_contestar@autoexam.epla.es'));
                                                        
                                                        // Mostrar botón según el estado del usuario
                                                        if ($usuario['activo'] && 
                                                            $usuario['id_usuario'] != $_SESSION['id_usuario'] && 
                                                            !$esAdminPrincipal): ?>
                                                            <!-- Botón para desactivar usuario activo -->
                                                            <button type="button" class="btn btn-sm btn-light rounded-pill border px-2 shadow-sm" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#modalDesactivar<?= $usuario['id_usuario'] ?>"
                                                                    title="Desactivar">
                                                                <i class="fas fa-ban text-danger"></i>
                                                            </button>
                                                        <?php elseif (!$usuario['activo']): ?>
                                                            <!-- Botón para activar usuario inactivo -->
                                                            <button type="button" class="btn btn-sm btn-light rounded-pill border px-2 shadow-sm" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#modalActivar<?= $usuario['id_usuario'] ?>"
                                                                    title="Activar">
                                                                <i class="fas fa-check-circle text-success"></i>
                                                            </button>
                                                        <?php elseif ($esAdminPrincipal): ?>
                                                            <!-- Botón deshabilitado para admin principal -->
                                                            <button type="button" class="btn btn-sm btn-light rounded-pill border px-2 shadow-sm" 
                                                                    disabled
                                                                    title="Protegido - Administrador Principal">
                                                                <i class="fas fa-shield-alt text-secondary"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Modal de confirmación de desactivación -->
                                                    <?php if ($usuario['activo'] && $usuario['id_usuario'] != $_SESSION['id_usuario']): ?>
                                                        <div class="modal fade" id="modalDesactivar<?= $usuario['id_usuario'] ?>" tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Confirmar Desactivación</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        ¿Está seguro de que desea desactivar al usuario 
                                                                        <strong><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?></strong>?
                                                                        <br><br>
                                                                        El usuario no podrá acceder al sistema hasta ser reactivado.
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                        <form method="POST" action="<?= BASE_URL ?>/usuarios/desactivar/<?= $usuario['id_usuario'] ?>" class="d-inline">
                                                                            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                                                                            <button type="submit" class="btn btn-danger">Desactivar</button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Modal de confirmación de activación -->
                                                    <?php if (!$usuario['activo']): ?>
                                                        <div class="modal fade" id="modalActivar<?= $usuario['id_usuario'] ?>" tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Confirmar Activación</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        ¿Está seguro de que desea activar al usuario 
                                                                        <strong><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?></strong>?
                                                                        <br><br>
                                                                        El usuario podrá acceder nuevamente al sistema.
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                        <form method="POST" action="<?= BASE_URL ?>/usuarios/activar/<?= $usuario['id_usuario'] ?>" class="d-inline">
                                                                            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                                                                            <button type="submit" class="btn btn-success">Activar</button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            </form>

                            <!-- Paginación -->
                                <div class="card-footer bg-light border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Mostrando <?= count($datos['usuarios']) ?> de <?= $datos['total_usuarios'] ?> usuarios
                                            (Página <?= $datos['pagina_actual'] ?> de <?= max(1, $datos['total_paginas']) ?>)
                                        </div>
                                    
                                    <?php if ($datos['total_paginas'] > 1): ?>
                                        <nav aria-label="Paginación de usuarios">
                                            <ul class="pagination pagination-sm mb-0">
                                                <!-- Página anterior -->
                                                <?php if ($datos['pagina_actual'] > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="<?= BASE_URL ?>/usuarios?pagina=<?= $datos['pagina_actual'] - 1 ?>&por_pagina=<?= $datos['por_pagina'] ?>&<?= http_build_query(array_diff_key($datos['filtros'], ['ordenar_por' => 1, 'orden' => 1])) ?><?= isset($datos['filtros']['ordenar_por']) ? '&ordenar_por=' . $datos['filtros']['ordenar_por'] . '&orden=' . $datos['filtros']['orden'] : '' ?>">
                                                            <i class="fas fa-chevron-left"></i>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                <!-- Páginas -->
                                                <?php
                                                $inicio = max(1, $datos['pagina_actual'] - 2);
                                                $fin = min($datos['total_paginas'], $datos['pagina_actual'] + 2);
                                                ?>

                                            <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                                <li class="page-item <?= $i == $datos['pagina_actual'] ? 'active' : '' ?>">
                                                    <a class="page-link border-0 rounded-pill mx-1 <?= $i == $datos['pagina_actual'] ? 'bg-primary text-white' : 'text-primary' ?>" 
                                                       href="<?= BASE_URL ?>/usuarios?pagina=<?= $i ?>&por_pagina=<?= $datos['por_pagina'] ?>&<?= http_build_query(array_diff_key($datos['filtros'], ['ordenar_por' => 1, 'orden' => 1])) ?><?= isset($datos['filtros']['ordenar_por']) ? '&ordenar_por=' . $datos['filtros']['ordenar_por'] . '&orden=' . $datos['filtros']['orden'] : '' ?>">
                                                        <?= $i ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>

                                            <!-- Página siguiente -->
                                            <?php if ($datos['pagina_actual'] < $datos['total_paginas']): ?>
                                                <li class="page-item">
                                                    <a class="page-link border-0 rounded-pill ms-1" href="<?= BASE_URL ?>/usuarios?pagina=<?= $datos['pagina_actual'] + 1 ?>&por_pagina=<?= $datos['por_pagina'] ?>&<?= http_build_query(array_diff_key($datos['filtros'], ['ordenar_por' => 1, 'orden' => 1])) ?><?= isset($datos['filtros']['ordenar_por']) ? '&ordenar_por=' . $datos['filtros']['ordenar_por'] . '&orden=' . $datos['filtros']['orden'] : '' ?>">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once APP_PATH . '/vistas/parciales/footer_admin.php'; ?>
    <?php require_once APP_PATH . '/vistas/parciales/scripts_admin.php'; ?>

    <!-- JavaScript para acciones masivas -->
    <script>
        // Variables globales
        let checkboxes = document.querySelectorAll('.usuario-checkbox');
        let botonAcciones = document.getElementById('accionesMasivas');
        let checkboxTodos = document.getElementById('seleccionarTodos');

        /**
         * Alterna la selección de todos los checkboxes
         */
        function toggleTodos() {
            checkboxes.forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.checked = checkboxTodos.checked;
                }
            });
            toggleAccionesMasivas();
        }

        /**
         * Habilita/deshabilita el botón de acciones masivas
         */
        function toggleAccionesMasivas() {
            let seleccionados = document.querySelectorAll('.usuario-checkbox:checked');
            botonAcciones.disabled = seleccionados.length === 0;
            
            // Actualizar estado del checkbox "Seleccionar todos"
            let habilitados = document.querySelectorAll('.usuario-checkbox:not(:disabled)');
            let seleccionadosHabilitados = document.querySelectorAll('.usuario-checkbox:checked:not(:disabled)');
            
            if (seleccionadosHabilitados.length === 0) {
                checkboxTodos.indeterminate = false;
                checkboxTodos.checked = false;
            } else if (seleccionadosHabilitados.length === habilitados.length) {
                checkboxTodos.indeterminate = false;
                checkboxTodos.checked = true;
            } else {
                checkboxTodos.indeterminate = true;
            }
        }

        /**
         * Ejecuta una acción masiva
         */
        function accionMasiva(accion) {
            let seleccionados = document.querySelectorAll('.usuario-checkbox:checked');
            
            if (seleccionados.length === 0) {
                alert('Por favor, selecciona al menos un usuario.');
                return;
            }

            let mensaje = '';
            if (accion === 'desactivar') {
                mensaje = `¿Está seguro de que desea desactivar ${seleccionados.length} usuario(s)?`;
            } else if (accion === 'exportar') {
                mensaje = `¿Desea exportar ${seleccionados.length} usuario(s) seleccionado(s)?`;
            }

            if (confirm(mensaje)) {
                document.getElementById('accion_masiva').value = accion;
                document.getElementById('formAccionMasiva').submit();
            }
        }

        // JavaScript para filtros automáticos
        document.getElementById('formFiltros').addEventListener('change', function() {
            // Esperar un momento para agrupar cambios
            setTimeout(() => {
                this.submit();
            }, 300);
        });

        // Inicializar estado al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            toggleAccionesMasivas();
            
            // Tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Configurar filtros automáticos
            configurarFiltrosAutomaticos();
        });
        
        /**
         * Configura los campos de filtro para que se apliquen automáticamente
         */
        function configurarFiltrosAutomaticos() {
            // Obtener todos los elementos con la clase filtro-auto
            const filtros = document.querySelectorAll('.filtro-auto');
            
            // Variable para almacenar el temporizador de debounce para el campo de texto
            let buscarTimeout;
            
            // Agregar event listeners según el tipo de elemento
            filtros.forEach(function(filtro) {
                if (filtro.tagName === 'SELECT') {
                    // Para los selectores, aplicar el filtro inmediatamente al cambiar
                    filtro.addEventListener('change', function() {
                        aplicarFiltros();
                    });
                } else if (filtro.tagName === 'INPUT' && filtro.type === 'text') {
                    // Para campos de texto, utilizar debounce para evitar demasiadas búsquedas
                    filtro.addEventListener('input', function() {
                        // Limpiar el temporizador anterior si existe
                        clearTimeout(buscarTimeout);
                        
                        // Establecer un nuevo temporizador (500ms de espera)
                        buscarTimeout = setTimeout(function() {
                            // Solo aplicar si hay al menos 3 caracteres o ninguno
                            const texto = filtro.value.trim();
                            if (texto === '' || texto.length >= 3) {
                                aplicarFiltros();
                            }
                        }, 500); // Esperar 500ms después de que el usuario deje de escribir
                    });
                    
                    // También aplicar al presionar Enter solo si cumple los requisitos
                    filtro.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault(); // Evitar el envío del formulario
                            clearTimeout(buscarTimeout);
                            
                            // Solo aplicar si hay al menos 3 caracteres o ninguno
                            const texto = filtro.value.trim();
                            if (texto === '' || texto.length >= 3) {
                                aplicarFiltros();
                            } else if (texto.length > 0 && texto.length < 3) {
                                alert('Por favor, ingresa al menos 3 caracteres para buscar o deja el campo vacío.');
                            }
                        }
                    });
                }
            });
        }
        
        /**
         * Aplica los filtros enviando el formulario
         */
        function aplicarFiltros() {
            try {
                // Verificar el campo de búsqueda antes de enviar
                const campoBuscar = document.getElementById('buscar');
                if (campoBuscar) {
                    const textoBuscar = campoBuscar.value.trim();
                    
                    // Si tiene texto pero menos de 3 caracteres, no aplicar filtro
                    if (textoBuscar.length > 0 && textoBuscar.length < 3) {
                        alert('Por favor, ingresa al menos 3 caracteres para buscar o deja el campo vacío.');
                        return; // No enviar el formulario
                    }
                }
                
                // Enviar formulario directamente sin manipular los valores
                document.getElementById('formFiltros').submit();
            } catch (error) {
                console.error('Error al aplicar filtros:', error);
                alert('Ocurrió un error al aplicar los filtros. Por favor, inténtalo nuevamente.');
            }
        }
    </script>
</body>
</html>
