<?php
/**
 * Vista de Gestión del Banco de Preguntas - Profesor - AUTOEXAM2
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'profesor')) {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}

// Variables para la vista
$total_registros = count($preguntas);
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;

// Opciones de límite
$opciones_limite = [10, 20, 50, 100];

// Generar token CSRF para formularios
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php require_once APP_PATH . '/vistas/parciales/head_profesor.php'; ?>

<body class="bg-light">
    <?php require_once APP_PATH . '/vistas/parciales/navbar_profesor.php'; ?>

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
    </style>

    <!-- Título de la página -->
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-question-circle me-2"></i> Banco de Preguntas</h1>
            <div class="d-flex gap-2">
                <!-- Acciones masivas -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                            id="accionesMasivas" data-bs-toggle="dropdown" 
                            aria-expanded="false" disabled>
                        <i class="fas fa-tasks"></i> Acciones Masivas
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="accionesMasivas">
                        <li><a class="dropdown-item" href="#" onclick="accionMasiva('eliminar')">
                            <i class="fas fa-trash text-danger"></i> Eliminar Seleccionadas
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="accionMasiva('exportar')">
                            <i class="fas fa-download text-success"></i> Exportar Seleccionadas
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="accionMasiva('duplicar')">
                            <i class="fas fa-copy text-info"></i> Duplicar Seleccionadas
                        </a></li>
                    </ul>
                </div>

                <!-- Exportar todos -->
                <a href="<?= BASE_URL ?>/banco-preguntas/exportar?<?= http_build_query($_GET) ?>" class="btn btn-outline-success">
                    <i class="fas fa-file-export"></i> Exportar Filtradas
                </a>
                
                <!-- Nueva pregunta -->
                <a href="<?= BASE_URL ?>/banco-preguntas/crear" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Pregunta
                </a>
                
                <!-- Importar preguntas -->
                <a href="<?= BASE_URL ?>/banco-preguntas/importar" class="btn btn-success">
                    <i class="fas fa-upload"></i> Importar
                </a>
                
                <!-- Estadísticas -->
                <a href="<?= BASE_URL ?>/banco-preguntas/estadisticas" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> Estadísticas
                </a>
            </div>
        </div>

            <!-- Mensajes -->
            <?php if (isset($_SESSION['mensaje_exito'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje_exito'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['mensaje_exito']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensaje_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['mensaje_error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['mensaje_error']); ?>
            <?php endif; ?>

        <!-- Filtros y opciones -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-filter me-1"></i> Filtros y opciones
            </div>
            <div class="card-body">
                <form method="GET" action="<?= BASE_URL ?>/banco-preguntas" class="row g-3" id="formFiltros">
                    <div class="col-md-3">
                        <label for="buscar" class="form-label"><i class="fas fa-search me-2"></i>Buscar</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control filtro-auto" id="buscar" name="buscar" 
                                   value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                                   placeholder="Enunciado, etiquetas..."
                                   data-bs-toggle="tooltip" data-bs-placement="top" 
                                   title="Los resultados se actualizan automáticamente mientras escribes">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="categoria" class="form-label"><i class="fas fa-tag me-2"></i>Categoría</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-tag text-muted"></i></span>
                            <select class="form-select filtro-auto" id="categoria" name="categoria"
                                    data-bs-toggle="tooltip" data-bs-placement="top" 
                                    title="Filtrar por categoría de la pregunta">
                                <option value="">Todas</option>
                                <option value="matematicas" <?= ($_GET['categoria'] ?? '') === 'matematicas' ? 'selected' : '' ?>>Matemáticas</option>
                                <option value="ciencias" <?= ($_GET['categoria'] ?? '') === 'ciencias' ? 'selected' : '' ?>>Ciencias</option>
                                <option value="lenguaje" <?= ($_GET['categoria'] ?? '') === 'lenguaje' ? 'selected' : '' ?>>Lenguaje</option>
                                <option value="historia" <?= ($_GET['categoria'] ?? '') === 'historia' ? 'selected' : '' ?>>Historia</option>
                                <option value="geografia" <?= ($_GET['categoria'] ?? '') === 'geografia' ? 'selected' : '' ?>>Geografía</option>
                                <option value="idiomas" <?= ($_GET['categoria'] ?? '') === 'idiomas' ? 'selected' : '' ?>>Idiomas</option>
                                <option value="tecnologia" <?= ($_GET['categoria'] ?? '') === 'tecnologia' ? 'selected' : '' ?>>Tecnología</option>
                                <option value="otra" <?= ($_GET['categoria'] ?? '') === 'otra' ? 'selected' : '' ?>>Otra</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="tipo" class="form-label"><i class="fas fa-list me-2"></i>Tipo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-list text-muted"></i></span>
                            <select class="form-select filtro-auto" id="tipo" name="tipo"
                                    data-bs-toggle="tooltip" data-bs-placement="top" 
                                    title="Filtrar por tipo de pregunta">
                                <option value="">Todos</option>
                                <option value="test" <?= ($_GET['tipo'] ?? '') === 'test' ? 'selected' : '' ?>>Test</option>
                                <option value="desarrollo" <?= ($_GET['tipo'] ?? '') === 'desarrollo' ? 'selected' : '' ?>>Desarrollo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="dificultad" class="form-label"><i class="fas fa-signal me-2"></i>Dificultad</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-signal text-muted"></i></span>
                            <select class="form-select filtro-auto" id="dificultad" name="dificultad"
                                    data-bs-toggle="tooltip" data-bs-placement="top" 
                                    title="Filtrar por nivel de dificultad">
                                <option value="">Todas</option>
                                <option value="facil" <?= ($_GET['dificultad'] ?? '') === 'facil' ? 'selected' : '' ?>>Fácil</option>
                                <option value="media" <?= ($_GET['dificultad'] ?? '') === 'media' ? 'selected' : '' ?>>Media</option>
                                <option value="dificil" <?= ($_GET['dificultad'] ?? '') === 'dificil' ? 'selected' : '' ?>>Difícil</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="por_pagina" class="form-label"><i class="fas fa-list-ol me-2"></i>Por página</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-list-ol text-muted"></i></span>
                            <select class="form-select filtro-auto" id="por_pagina" name="por_pagina">
                                <option value="10" <?= ($limite ?? 20) == 10 ? 'selected' : '' ?>>10</option>
                                <option value="20" <?= ($limite ?? 20) == 20 ? 'selected' : '' ?>>20</option>
                                <option value="50" <?= ($limite ?? 20) == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= ($limite ?? 20) == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <a href="<?= BASE_URL ?>/banco-preguntas" class="btn btn-light border shadow-sm rounded-pill w-100">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de preguntas -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0 d-flex align-items-center justify-content-between">
                    <span>
                        <i class="fas fa-question-circle text-primary me-2"></i> Banco de Preguntas
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 rounded-pill" id="contador-resultados">
                            <?= $total_registros ?> <?= $total_registros == 1 ? 'pregunta' : 'preguntas' ?>
                        </span>
                    </span>
                    <?php if (!empty($_GET) && array_filter($_GET)): ?>
                        <small class="text-muted">
                            <i class="fas fa-filter me-1"></i>Filtros aplicados
                        </small>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($preguntas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron preguntas</h5>
                        <p class="text-muted">Ajusta los filtros o crea una nueva pregunta.</p>
                        <a href="<?= BASE_URL ?>/banco-preguntas/crear" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva Pregunta
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Formulario para acciones masivas -->
                    <form id="formAccionMasiva" method="POST" action="<?= BASE_URL ?>/banco-preguntas/accion-masiva">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="accion" id="accion_masiva" value="">
                        
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 border">
                                <thead class="bg-light border-bottom">
                                    <tr>
                                        <th width="40" class="py-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="seleccionar_todos" onchange="toggleSeleccionarTodos(this)">
                                                <label class="form-check-label" for="seleccionar_todos">
                                                    <span class="visually-hidden">Seleccionar todos</span>
                                                </label>
                                            </div>
                                        </th>
                                        <th class="py-3 text-muted fw-semibold">Enunciado</th>
                                        <th class="py-3 text-muted fw-semibold">Tipo</th>
                                        <th class="py-3 text-muted fw-semibold">Categoría</th>
                                        <th class="py-3 text-muted fw-semibold">Dificultad</th>
                                        <th class="py-3 text-muted fw-semibold">Autor</th>
                                        <th class="py-3 text-muted fw-semibold">Fecha</th>
                                        <th class="py-3 text-muted fw-semibold text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($preguntas as $pregunta): ?>
                                        <tr>
                                            <td class="py-3">
                                                <div class="form-check">
                                                    <input class="form-check-input pregunta-checkbox" type="checkbox" 
                                                           value="<?= $pregunta['id_pregunta'] ?>" 
                                                           id="pregunta_<?= $pregunta['id_pregunta'] ?>"
                                                           onchange="actualizarAccionesMasivas()">
                                                    <label class="form-check-label" for="pregunta_<?= $pregunta['id_pregunta'] ?>">
                                                        <span class="visually-hidden">Seleccionar pregunta</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="py-3">
                                                <div class="fw-semibold mb-1">
                                                    <?= htmlspecialchars(substr($pregunta['enunciado'], 0, 80)) ?><?= strlen($pregunta['enunciado']) > 80 ? '...' : '' ?>
                                                </div>
                                                <?php if (!empty($pregunta['etiquetas'])): ?>
                                                    <div class="mt-1">
                                                        <?php 
                                                        $etiquetas = explode(',', $pregunta['etiquetas']);
                                                        foreach (array_slice($etiquetas, 0, 3) as $etiqueta): 
                                                        ?>
                                                            <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars(trim($etiqueta)) ?></span>
                                                        <?php endforeach; ?>
                                                        <?php if (count($etiquetas) > 3): ?>
                                                            <span class="badge bg-secondary">+<?= count($etiquetas) - 3 ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3">
                                                <span class="badge bg-<?= $pregunta['tipo'] == 'test' ? 'info' : 'warning' ?> rounded-pill">
                                                    <i class="fas fa-<?= $pregunta['tipo'] == 'test' ? 'list-ul' : 'edit' ?> me-1"></i>
                                                    <?= ucfirst($pregunta['tipo']) ?>
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                <span class="text-muted">
                                                    <?= htmlspecialchars(ucfirst($pregunta['categoria'] ?? 'Sin categoría')) ?>
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                <span class="badge bg-<?= 
                                                    ($pregunta['dificultad'] ?? 'media') == 'facil' ? 'success' : 
                                                    (($pregunta['dificultad'] ?? 'media') == 'media' ? 'warning' : 'danger') 
                                                ?> rounded-pill">
                                                    <i class="fas fa-signal me-1"></i>
                                                    <?= ucfirst($pregunta['dificultad'] ?? 'Media') ?>
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-user-circle text-muted me-2"></i>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($pregunta['nombre_profesor'] ?? 'Sistema') ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td class="py-3">
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($pregunta['fecha_creacion'])) ?>
                                                </small>
                                            </td>
                                            <td class="py-3 text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= BASE_URL ?>/banco-preguntas/ver/<?= $pregunta['id_pregunta'] ?>" 
                                                       class="btn btn-outline-info btn-sm" title="Ver detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>/banco-preguntas/editar/<?= $pregunta['id_pregunta'] ?>" 
                                                       class="btn btn-outline-warning btn-sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle dropdown-toggle-split" 
                                                            data-bs-toggle="dropdown" title="Más opciones">
                                                        <span class="visually-hidden">Toggle Dropdown</span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="#" 
                                                               onclick="duplicarPregunta(<?= $pregunta['id_pregunta'] ?>)">
                                                                <i class="fas fa-copy text-info"></i> Duplicar
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" 
                                                               onclick="usarEnExamen(<?= $pregunta['id_pregunta'] ?>)">
                                                                <i class="fas fa-plus-circle text-success"></i> Usar en examen
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" 
                                                               onclick="eliminarPregunta(<?= $pregunta['id_pregunta'] ?>)">
                                                                <i class="fas fa-trash"></i> Eliminar
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        </div>

    </div>

    <!-- Scripts personalizados -->
    <script>
        // Filtros proactivos
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            const filtrosAuto = document.querySelectorAll('.filtro-auto');
            let timeoutId;
            
            filtrosAuto.forEach(filtro => {
                // Para selects - cambio inmediato
                if (filtro.tagName === 'SELECT') {
                    filtro.addEventListener('change', function() {
                        aplicarFiltros();
                    });
                }
                
                // Para inputs de texto - con delay para evitar muchas consultas
                if (filtro.tagName === 'INPUT') {
                    filtro.addEventListener('input', function() {
                        clearTimeout(timeoutId);
                        
                        // Mostrar indicador de "escribiendo"
                        mostrarIndicadorEscribiendo(true);
                        
                        timeoutId = setTimeout(() => {
                            mostrarIndicadorEscribiendo(false);
                            aplicarFiltros();
                        }, 800); // 800ms de delay para dar tiempo a escribir
                    });
                }
            });
            
            function aplicarFiltros() {
                // Mostrar indicador de carga
                mostrarIndicadorCarga(true);
                
                // Enviar formulario
                document.getElementById('formFiltros').submit();
            }
            
            function mostrarIndicadorCarga(mostrar) {
                const btnLimpiar = document.querySelector('.btn-light.border');
                if (mostrar) {
                    btnLimpiar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Filtrando...';
                    btnLimpiar.disabled = true;
                } else {
                    btnLimpiar.innerHTML = '<i class="fas fa-times"></i> Limpiar';
                    btnLimpiar.disabled = false;
                }
            }
            
            function mostrarIndicadorEscribiendo(mostrar) {
                const iconoBuscar = document.querySelector('#buscar').previousElementSibling.querySelector('i');
                if (mostrar) {
                    iconoBuscar.className = 'fas fa-keyboard text-warning';
                } else {
                    iconoBuscar.className = 'fas fa-search text-muted';
                }
            }
        });

        // Selección masiva
        function toggleSeleccionarTodos(checkbox) {
            const checkboxes = document.querySelectorAll('.pregunta-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
            actualizarAccionesMasivas();
        }

        function actualizarAccionesMasivas() {
            const checkboxes = document.querySelectorAll('.pregunta-checkbox:checked');
            const botonAcciones = document.getElementById('accionesMasivas');
            
            if (checkboxes.length > 0) {
                botonAcciones.disabled = false;
                botonAcciones.innerHTML = `<i class="fas fa-tasks"></i> Acciones Masivas (${checkboxes.length})`;
            } else {
                botonAcciones.disabled = true;
                botonAcciones.innerHTML = '<i class="fas fa-tasks"></i> Acciones Masivas';
            }
        }

        // Acciones masivas
        function accionMasiva(accion) {
            const checkboxes = document.querySelectorAll('.pregunta-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('Selecciona al menos una pregunta');
                return;
            }

            let mensaje = '';
            switch(accion) {
                case 'eliminar':
                    mensaje = `¿Eliminar ${checkboxes.length} pregunta(s) seleccionada(s)?`;
                    break;
                case 'exportar':
                    mensaje = `¿Exportar ${checkboxes.length} pregunta(s) seleccionada(s)?`;
                    break;
                case 'duplicar':
                    mensaje = `¿Duplicar ${checkboxes.length} pregunta(s) seleccionada(s)?`;
                    break;
            }

            if (confirm(mensaje)) {
                const form = document.getElementById('formAccionMasiva');
                document.getElementById('accion_masiva').value = accion;
                
                // Agregar IDs seleccionados
                checkboxes.forEach(checkbox => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = checkbox.value;
                    form.appendChild(input);
                });
                
                form.submit();
            }
        }

        // Acciones individuales
        function duplicarPregunta(id) {
            if (confirm('¿Duplicar esta pregunta?')) {
                window.location.href = `<?= BASE_URL ?>/banco-preguntas/duplicar/${id}`;
            }
        }

        function usarEnExamen(id) {
            // Redireccionar a selección de examen
            window.location.href = `<?= BASE_URL ?>/examenes/agregar-pregunta/${id}`;
        }

        function eliminarPregunta(id) {
            if (confirm('¿Está seguro de eliminar esta pregunta? Esta acción no se puede deshacer.')) {
                window.location.href = `<?= BASE_URL ?>/banco-preguntas/eliminar/${id}`;
            }
        }
    </script>

    <?php require_once APP_PATH . '/vistas/parciales/footer_profesor.php'; ?>
    <?php require_once APP_PATH . '/vistas/parciales/scripts_profesor.php'; ?>
</body>
</html>
