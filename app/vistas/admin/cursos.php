<?php
/**
 * Vista para la gestión de cursos (Administrador)
 * AUTOEXAM2 - 16/06/2025
 */

// Variables para la vista
$cursos = $resultado['cursos'];
$total_registros = $resultado['total'];
$paginas = $resultado['paginas'];
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;

// Opciones de límite
$opciones_limite = [5, 10, 15, 20, 50];

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
</style>

<!-- Título de la página -->
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-book me-2"></i> Gestión de Cursos</h1>
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
            <a href="<?= BASE_URL ?>/cursos/exportar?<?= http_build_query($_GET) ?>" class="btn btn-outline-success">
                <i class="fas fa-file-export"></i> Exportar Filtrados
            </a>
            
            <!-- Nuevo curso -->
            <a href="<?= BASE_URL ?>/cursos/nuevo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Curso
            </a>
            
            <!-- Estadísticas -->
            <a href="<?= BASE_URL ?>/cursos/estadisticas" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Estadísticas
            </a>
        </div>
    </div>

    <!-- Alertas y mensajes -->
    <?php if(isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show" role="alert">
        <?= $_SESSION['mensaje'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
    unset($_SESSION['mensaje']); 
    unset($_SESSION['tipo_mensaje']);
    endif; 
    ?>

    <!-- Filtros y opciones -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i> Filtros y opciones
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/cursos" method="GET" class="row g-3" id="formFiltros">
                <!-- Filtro por nombre -->
                <div class="col-md-4">
                    <label for="nombre" class="form-label"><i class="fas fa-search me-2"></i>Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control filtro-auto" id="nombre" name="nombre" 
                               value="<?= isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '' ?>"
                               placeholder="Nombre del curso">
                    </div>
                </div>
                
                <!-- Filtro por profesor -->
                <div class="col-md-3">
                    <label for="profesor" class="form-label"><i class="fas fa-user-tie me-2"></i>Profesor</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-user-tie text-muted"></i></span>
                        <select class="form-select filtro-auto" id="profesor" name="profesor">
                            <option value="">Todos</option>
                            <?php foreach ($profesores as $prof): ?>
                            <option value="<?= $prof['id_usuario'] ?>" 
                                <?= (isset($_GET['profesor']) && $_GET['profesor'] == $prof['id_usuario']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prof['apellidos'] . ', ' . $prof['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Filtro por estado -->
                <div class="col-md-2">
                    <label for="activo" class="form-label"><i class="fas fa-toggle-on me-2"></i>Estado</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-toggle-on text-muted"></i></span>
                        <select class="form-select filtro-auto" id="activo" name="activo">
                            <option value="">Todos</option>
                            <option value="1" <?= (isset($_GET['activo']) && $_GET['activo'] == '1') ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= (isset($_GET['activo']) && $_GET['activo'] == '0') ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
                
                <!-- Registros por página -->
                <div class="col-md-2">
                    <label for="limite" class="form-label"><i class="fas fa-list-ol me-2"></i>Por página</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-list-ol text-muted"></i></span>
                        <select class="form-select filtro-auto" id="limite" name="limite">
                            <?php foreach ($opciones_limite as $opcion): ?>
                            <option value="<?= $opcion ?>" <?= $limite == $opcion ? 'selected' : '' ?>><?= $opcion ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Botón limpiar -->
                <div class="col-md-1 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/cursos" class="btn btn-light border shadow-sm rounded-pill w-100">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de cursos -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="fas fa-book text-primary me-2"></i> Cursos 
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 rounded-pill"><?= $total_registros ?> total</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (count($cursos) > 0): ?>
            <div class="table-responsive">
                <!-- Formulario para acciones masivas -->
                <form id="formAccionMasiva" method="POST" action="<?= BASE_URL ?>/cursos/accion-masiva">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="accion" id="accion_masiva" value="">
                    
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
                                    <a href="<?= BASE_URL ?>/cursos?<?= http_build_query(array_merge($_GET, ['ordenar_por' => 'id_curso', 'orden' => (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] == 'id_curso' && isset($_GET['orden']) && $_GET['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                        ID
                                        <?php if (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] == 'id_curso'): ?>
                                            <i class="ms-1 fas fa-sort-<?= $_GET['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                        <?php else: ?>
                                            <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                            <th class="py-3 text-muted fw-semibold">
                                <a href="<?= BASE_URL ?>/cursos?<?= http_build_query(array_merge($_GET, ['ordenar_por' => 'nombre_curso', 'orden' => (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] == 'nombre_curso' && isset($_GET['orden']) && $_GET['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                    Nombre del Curso
                                    <?php if (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] == 'nombre_curso'): ?>
                                        <i class="ms-1 fas fa-sort-<?= $_GET['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="py-3 text-muted fw-semibold">
                                <a href="<?= BASE_URL ?>/cursos?<?= http_build_query(array_merge($_GET, ['ordenar_por' => 'id_profesor', 'orden' => (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] == 'id_profesor' && isset($_GET['orden']) && $_GET['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                    Profesor
                                    <?php if (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] == 'id_profesor'): ?>
                                        <i class="ms-1 fas fa-sort-<?= $_GET['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="py-3 text-muted fw-semibold">
                                <a href="<?= BASE_URL ?>/cursos?<?= http_build_query(array_merge($_GET, ['ordenar_por' => 'activo', 'orden' => (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] == 'activo' && isset($_GET['orden']) && $_GET['orden'] == 'ASC') ? 'DESC' : 'ASC'])) ?>" class="text-decoration-none text-muted d-flex align-items-center">
                                    Estado
                                    <?php if (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] == 'activo'): ?>
                                        <i class="ms-1 fas fa-sort-<?= $_GET['orden'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php else: ?>
                                        <i class="ms-1 fas fa-sort text-muted opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="py-3 text-muted fw-semibold text-center">Alumnos</th>
                            <th class="py-3 text-muted fw-semibold text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cursos as $curso): ?>
                        <tr class="<?= $curso['activo'] ? 'align-middle border-bottom' : 'align-middle border-bottom bg-light' ?>">
                            <td class="py-3">
                                <div class="form-check">
                                    <input class="form-check-input curso-checkbox" 
                                           type="checkbox" 
                                           name="cursos[]" 
                                           value="<?= $curso['id_curso'] ?>"
                                           id="curso_<?= $curso['id_curso'] ?>"
                                           onchange="toggleAccionesMasivas()">
                                    <label class="form-check-label" for="curso_<?= $curso['id_curso'] ?>">
                                        <span class="visually-hidden">Seleccionar curso</span>
                                    </label>
                                </div>
                            </td>
                            <td><?= $curso['id_curso'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-subtle text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                         style="width: 32px; height: 32px; min-width: 32px;">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($curso['nombre_curso']) ?></strong>
                                        <?php if (!empty($curso['descripcion'])): ?>
                                        <small class="curso-descripcion d-block"><?= mb_substr(htmlspecialchars($curso['descripcion']), 0, 50) . (mb_strlen($curso['descripcion']) > 50 ? '...' : '') ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <?= htmlspecialchars($curso['apellidos_profesor'] . ', ' . $curso['nombre_profesor']) ?>
                                </div>
                            </td>
                            <td>
                                <?php if($curso['activo'] == 1): ?>
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                                    <i class="fas fa-check"></i> Activo
                                </span>
                                <?php else: ?>
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    <i class="fas fa-times"></i> Inactivo
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/cursos/asignarAlumnos?id=<?= $curso['id_curso'] ?>" 
                                   class="btn btn-sm btn-info rounded-pill border px-3 shadow-sm"
                                   title="Gestionar alumnos">
                                    <i class="fas fa-user-graduate me-1"></i> <?= $curso['num_alumnos'] ?? '0' ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/cursos/ver?id=<?= $curso['id_curso'] ?>" 
                                       class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                       data-bs-toggle="tooltip" title="Ver curso">
                                        <i class="fas fa-eye text-info"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/cursos/editar?id=<?= $curso['id_curso'] ?>" 
                                       class="btn btn-sm btn-light rounded-pill border me-1 px-2 shadow-sm" 
                                       data-bs-toggle="tooltip" title="Editar curso">
                                        <i class="fas fa-edit text-primary"></i>
                                    </a>
                                    <?php if ($curso['activo'] == 1): ?>
                                    <!-- Botón para desactivar curso activo -->
                                    <button type="button" class="btn btn-sm btn-light rounded-pill border px-2 shadow-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#desactivarModal" 
                                            data-id="<?= $curso['id_curso'] ?>"
                                            data-nombre="<?= htmlspecialchars($curso['nombre_curso']) ?>"
                                            title="Desactivar curso">
                                        <i class="fas fa-power-off text-danger"></i>
                                    </button>
                                    <?php else: ?>
                                    <!-- Botón para activar curso inactivo -->
                                    <a href="<?= BASE_URL ?>/cursos/activar?id=<?= $curso['id_curso'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                                       class="btn btn-sm btn-light rounded-pill border px-2 shadow-sm"
                                       title="Activar curso" 
                                       onclick="return confirm('¿Está seguro que desea activar el curso <?= htmlspecialchars($curso['nombre_curso']) ?>?');">
                                        <i class="fas fa-power-off text-success"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No se encontraron cursos</h5>
                <p class="text-muted">Ajusta los filtros o crea un nuevo curso.</p>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-light border-top">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Mostrando <?= count($cursos) ?> de <?= $total_registros ?> cursos
                    (Página <?= $pagina_actual ?> de <?= max(1, $paginas) ?>)
                </div>
            
            <?php if ($paginas > 1): ?>
                <nav aria-label="Paginación de cursos">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Página anterior -->
                        <?php if ($pagina_actual > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= BASE_URL ?>/cursos?pagina=<?= $pagina_actual - 1 ?>&limite=<?= $limite ?>&<?= http_build_query(array_diff_key($_GET, ['pagina' => 1, 'limite' => 1])) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Páginas -->
                        <?php
                        $inicio = max(1, $pagina_actual - 2);
                        $fin = min($paginas, $pagina_actual + 2);
                        ?>

                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                        <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                            <a class="page-link border-0 rounded-pill mx-1 <?= $i == $pagina_actual ? 'bg-primary text-white' : 'text-primary' ?>" 
                               href="<?= BASE_URL ?>/cursos?pagina=<?= $i ?>&limite=<?= $limite ?>&<?= http_build_query(array_diff_key($_GET, ['pagina' => 1, 'limite' => 1])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Página siguiente -->
                    <?php if ($pagina_actual < $paginas): ?>
                        <li class="page-item">
                            <a class="page-link border-0 rounded-pill ms-1" href="<?= BASE_URL ?>/cursos?pagina=<?= $pagina_actual + 1 ?>&limite=<?= $limite ?>&<?= http_build_query(array_diff_key($_GET, ['pagina' => 1, 'limite' => 1])) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
</form>

<!-- Modal de confirmación para desactivar -->
<div class="modal fade" id="desactivarModal" tabindex="-1" aria-labelledby="desactivarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="desactivarModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Desactivación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea desactivar el curso <strong id="cursoNombre"></strong>?</p>
                <p>Esta acción:</p>
                <ul>
                    <li>Ocultará el curso de las interfaces</li>
                    <li>Desactivará módulos y exámenes asociados</li>
                    <li>Desasignará alumnos y desvinculará al profesor</li>
                </ul>
                <p class="mb-0"><strong>Esta acción no elimina datos de la base de datos.</strong></p>
            </div>
            <div class="modal-footer">
                <form action="<?= BASE_URL ?>/cursos/desactivar" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id_curso" id="idCursoEliminar">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Desactivar</button>
                </form>
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
            const campoBuscar = document.getElementById('nombre');
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

    /**
     * Variables para controlar selección
     */
    let botonAcciones = document.getElementById('accionesMasivas');
    let checkboxTodos = document.getElementById('seleccionarTodos');
    
    // Cargar estilos y script para las descripciones de cursos
    const linkCss = document.createElement('link');
    linkCss.rel = 'stylesheet';
    linkCss.href = '<?= BASE_URL ?>/recursos/css/cursos.css';
    document.head.appendChild(linkCss);

    const script = document.createElement('script');
    script.src = '<?= BASE_URL ?>/recursos/js/cursos.js';
    document.body.appendChild(script);
    
    // Inicializar estado al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        toggleAccionesMasivas();
        
        // Tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    /**
     * Seleccionar/deseleccionar todos los cursos
     */
    function toggleTodos() {
        let checkboxes = document.querySelectorAll('.curso-checkbox');
        
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
        let seleccionados = document.querySelectorAll('.curso-checkbox:checked');
        botonAcciones.disabled = seleccionados.length === 0;
        
        // Actualizar estado del checkbox "Seleccionar todos"
        let habilitados = document.querySelectorAll('.curso-checkbox:not(:disabled)');
        let seleccionadosHabilitados = document.querySelectorAll('.curso-checkbox:checked:not(:disabled)');
        
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
        let seleccionados = document.querySelectorAll('.curso-checkbox:checked');
        
        if (seleccionados.length === 0) {
            alert('Por favor, selecciona al menos un curso.');
            return;
        }

        let mensaje = '';
        if (accion === 'desactivar') {
            mensaje = `¿Está seguro de que desea desactivar ${seleccionados.length} curso(s)?`;
        } else if (accion === 'exportar') {
            mensaje = `¿Desea exportar ${seleccionados.length} curso(s) seleccionado(s)?`;
        }

        if (confirm(mensaje)) {
            document.getElementById('accion_masiva').value = accion;
            document.getElementById('formAccionMasiva').submit();
        }
    }
</script>
