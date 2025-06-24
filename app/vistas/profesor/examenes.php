<?php
/**
 * Vista de Gestión de Exámenes - AUTOEXAM2
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'profesor')) {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<?php require_once APP_PATH . '/vistas/parciales/head_profesor.php'; ?>

<body class="bg-light">
    <?php require_once APP_PATH . '/vistas/parciales/navbar_profesor.php'; ?>
    
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

    <div class="container-fluid px-4 py-4">
        <!-- Header principal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-file-alt text-primary me-2"></i>
                    Gestión de Exámenes
                </h1>
                <p class="text-muted mb-0">Crea y administra los exámenes de tus cursos</p>
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
                <a href="<?= BASE_URL ?>/examenes/exportar?<?= http_build_query($datos['filtros'] ?? []) ?>" 
                   class="btn btn-outline-success">
                    <i class="fas fa-file-export"></i> Exportar Filtrados
                </a>

                <!-- Nuevo examen -->
                <a href="<?= BASE_URL ?>/examenes/crear" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Examen
                </a>
                
                <!-- Importar exámenes -->
                <a href="<?= BASE_URL ?>/examenes/importar" class="btn btn-success">
                    <i class="fas fa-upload"></i> Importar
                </a>
                
                <!-- Estadísticas -->
                <a href="<?= BASE_URL ?>/examenes/estadisticas" class="btn btn-info">
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

                <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-primary-gradient">
                                    <i class="fas fa-file-alt text-white fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="stats-label">TOTAL EXÁMENES</div>
                                <div class="stats-value"><?= count($datos['examenes'] ?? []) ?></div>
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
                                    <i class="fas fa-play text-white fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="stats-label">ACTIVOS</div>
                                <div class="stats-value"><?= count(array_filter($datos['examenes'] ?? [], function($e) { return $e['estado'] === 'activo'; })) ?></div>
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
                                    <i class="fas fa-edit text-white fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="stats-label">BORRADORES</div>
                                <div class="stats-value"><?= count(array_filter($datos['examenes'] ?? [], function($e) { return $e['estado'] === 'borrador'; })) ?></div>
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
                                    <i class="fas fa-check text-white fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="stats-label">FINALIZADOS</div>
                                <div class="stats-value"><?= count(array_filter($datos['examenes'] ?? [], function($e) { return $e['estado'] === 'finalizado'; })) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <!-- Filtros y opciones -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-filter me-1"></i> Filtros y opciones
                    </div>
                    <div class="card-body">
                        <form method="GET" action="<?= BASE_URL ?>/examenes" class="row g-3" id="formFiltros">
                            <div class="col-md-4">
                                <label for="buscar" class="form-label"><i class="fas fa-search me-2"></i>Buscar</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control filtro-auto" id="buscar" name="buscar" 
                                           value="<?= htmlspecialchars($datos['filtros']['buscar'] ?? '') ?>" 
                                           placeholder="Título o descripción">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="curso" class="form-label"><i class="fas fa-book me-2"></i>Curso</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-book text-muted"></i></span>
                                    <select class="form-select filtro-auto" id="curso" name="curso">
                                        <option value="">Todos</option>
                                        <?php if (isset($datos['cursos'])): ?>
                                            <?php foreach ($datos['cursos'] as $curso): ?>
                                                <option value="<?= $curso['id_curso'] ?>" 
                                                        <?= ($datos['filtros']['curso'] ?? '') == $curso['id_curso'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($curso['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="estado" class="form-label"><i class="fas fa-toggle-on me-2"></i>Estado</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-toggle-on text-muted"></i></span>
                                    <select class="form-select filtro-auto" id="estado" name="estado">
                                        <option value="">Todos</option>
                                        <option value="borrador" <?= ($datos['filtros']['estado'] ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                                        <option value="activo" <?= ($datos['filtros']['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                                        <option value="finalizado" <?= ($datos['filtros']['estado'] ?? '') === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="tipo" class="form-label"><i class="fas fa-clipboard-list me-2"></i>Tipo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-clipboard-list text-muted"></i></span>
                                    <select class="form-select filtro-auto" id="tipo" name="tipo">
                                        <option value="">Todos</option>
                                        <option value="cuestionario" <?= ($datos['filtros']['tipo'] ?? '') === 'cuestionario' ? 'selected' : '' ?>>Cuestionario</option>
                                        <option value="test" <?= ($datos['filtros']['tipo'] ?? '') === 'test' ? 'selected' : '' ?>>Test</option>
                                        <option value="evaluacion" <?= ($datos['filtros']['tipo'] ?? '') === 'evaluacion' ? 'selected' : '' ?>>Evaluación</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <label for="por_pagina" class="form-label"><i class="fas fa-list-ol me-2"></i>Por página</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-list-ol text-muted"></i></span>
                                    <select class="form-select filtro-auto" id="por_pagina" name="por_pagina">
                                        <option value="5" <?= ($datos['por_pagina'] ?? 10) == 5 ? 'selected' : '' ?>>5</option>
                                        <option value="10" <?= ($datos['por_pagina'] ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                                        <option value="15" <?= ($datos['por_pagina'] ?? 10) == 15 ? 'selected' : '' ?>>15</option>
                                        <option value="20" <?= ($datos['por_pagina'] ?? 10) == 20 ? 'selected' : '' ?>>20</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <a href="<?= BASE_URL ?>/examenes" class="btn btn-light border shadow-sm rounded-pill w-100">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de exámenes -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>
                            Lista de Exámenes
                            <?php if (isset($datos['total_resultados'])): ?>
                                <span class="badge bg-secondary ms-2"><?= $datos['total_resultados'] ?> exámenes</span>
                            <?php endif; ?>
                        </h5>
                        <div>
                            <input type="checkbox" id="seleccionar_todos" class="form-check-input me-2">
                            <label for="seleccionar_todos" class="form-check-label small">Seleccionar todos</label>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (isset($datos['examenes']) && !empty($datos['examenes'])): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;">
                                                <input type="checkbox" id="select_all_header" class="form-check-input">
                                            </th>
                                            <th>Título</th>
                                            <th>Curso/Módulo</th>
                                            <th>Tipo</th>
                                            <th>Estado</th>
                                            <th>Preguntas</th>
                                            <th>Duración</th>
                                            <th>Intentos</th>
                                            <th>Fecha Creación</th>
                                            <th style="width: 150px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($datos['examenes'] as $examen): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input item-checkbox" 
                                                           value="<?= $examen['id_examen'] ?>">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars($examen['titulo']) ?></div>
                                                            <?php if (!empty($examen['descripcion'])): ?>
                                                                <div class="text-muted small"><?= htmlspecialchars(substr($examen['descripcion'], 0, 60)) ?>...</div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="small">
                                                        <div class="fw-bold"><?= htmlspecialchars($examen['curso_nombre'] ?? 'Sin asignar') ?></div>
                                                        <?php if (!empty($examen['modulo_nombre'])): ?>
                                                            <div class="text-muted"><?= htmlspecialchars($examen['modulo_nombre']) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= ucfirst($examen['tipo'] ?? 'General') ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $estado = $examen['estado'] ?? 'borrador';
                                                    $badge_class = [
                                                        'borrador' => 'bg-warning',
                                                        'activo' => 'bg-success',
                                                        'finalizado' => 'bg-danger'
                                                    ];
                                                    ?>
                                                    <span class="badge <?= $badge_class[$estado] ?? 'bg-secondary' ?>">
                                                        <?= ucfirst($estado) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info"><?= $examen['total_preguntas'] ?? 0 ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($examen['duracion_minutos']): ?>
                                                        <i class="fas fa-clock text-muted me-1"></i><?= $examen['duracion_minutos'] ?> min
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin límite</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= $examen['intentos_maximos'] ?? '∞' ?>
                                                </td>
                                                <td class="small text-muted">
                                                    <?= date('d/m/Y', strtotime($examen['fecha_creacion'])) ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="<?= BASE_URL ?>/examenes/ver/<?= $examen['id_examen'] ?>" 
                                                           class="btn btn-sm btn-outline-primary" title="Ver">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="<?= BASE_URL ?>/examenes/editar/<?= $examen['id_examen'] ?>" 
                                                           class="btn btn-sm btn-outline-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmarEliminar(<?= $examen['id_examen'] ?>)" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay exámenes disponibles</h5>
                                <p class="text-muted">Comienza creando tu primer examen</p>
                                <a href="<?= BASE_URL ?>/examenes/crear" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Crear Examen
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Paginación -->
                <?php if (isset($datos['total_paginas']) && $datos['total_paginas'] > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Mostrando <?= ($datos['pagina_actual'] - 1) * $datos['por_pagina'] + 1 ?> - 
                            <?= min($datos['pagina_actual'] * $datos['por_pagina'], $datos['total_resultados']) ?> 
                            de <?= $datos['total_resultados'] ?> exámenes
                        </div>
                        <nav aria-label="Paginación de exámenes">
                            <ul class="pagination mb-0">
                                <?php if ($datos['pagina_actual'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($datos['filtros'], ['pagina' => $datos['pagina_actual'] - 1])) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $datos['pagina_actual'] - 2); $i <= min($datos['total_paginas'], $datos['pagina_actual'] + 2); $i++): ?>
                                    <li class="page-item <?= $i == $datos['pagina_actual'] ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($datos['filtros'], ['pagina' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($datos['pagina_actual'] < $datos['total_paginas']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($datos['filtros'], ['pagina' => $datos['pagina_actual'] + 1])) ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEliminarLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar este examen? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                        <i class="fas fa-trash me-2"></i>Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filtros automáticos
        document.querySelectorAll('.filtro-auto').forEach(input => {
            input.addEventListener('input', function() {
                setTimeout(() => {
                    document.getElementById('formFiltros').submit();
                }, 500);
            });
            
            input.addEventListener('change', function() {
                document.getElementById('formFiltros').submit();
            });
        });

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
            boton.textContent = seleccionados > 0 ? `Acciones (${seleccionados})` : 'Acciones Masivas';
        }

        // Confirmación de eliminación
        let examenIdAEliminar = null;

        function confirmarEliminar(id) {
            examenIdAEliminar = id;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }

        document.getElementById('btnConfirmarEliminar').addEventListener('click', function() {
            if (examenIdAEliminar) {
                window.location.href = `<?= BASE_URL ?>/examenes/eliminar/${examenIdAEliminar}`;
            }
        });

        // Acciones masivas
        function accionMasiva(accion) {
            const seleccionados = Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value);
            
            if (seleccionados.length === 0) {
                alert('Por favor, selecciona al menos un examen.');
                return;
            }

            if (accion === 'exportar') {
                const ids = seleccionados.join(',');
                window.location.href = `<?= BASE_URL ?>/examenes/exportar?ids=${ids}`;
            } else if (accion === 'desactivar') {
                if (confirm(`¿Estás seguro de desactivar ${seleccionados.length} exámenes?`)) {
                    // Implementar lógica de desactivación masiva
                    console.log('Desactivar exámenes:', seleccionados);
                }
            }
        }

        // Tooltip para botones
        document.querySelectorAll('[title]').forEach(el => {
            new bootstrap.Tooltip(el);
        });
    </script>

    <?php require_once APP_PATH . '/vistas/parciales/footer_profesor.php'; ?>
</body>
</html>