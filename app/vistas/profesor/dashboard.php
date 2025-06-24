<?php
/**
 * Dashboard de Profesor - AUTOEXAM2
 * 
 * Panel principal de control para profesores
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// No necesitamos incluir cabecera ni navbar aquí
// ya que se incluyen desde el controlador
?>

<div class="container-fluid py-4">
    <!-- Cabecera y bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <!-- Avatar del profesor -->
                        <div class="me-4">
                            <?php if (!empty($_SESSION['foto'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($_SESSION['foto']) ?>" 
                                     class="rounded-circle border border-2 border-primary shadow-sm" 
                                     width="80" height="80" alt="Avatar" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center
                                            border border-2 border-light shadow-sm"
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    <i class="fas fa-user-circle text-white"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <h2 class="card-title mb-0">Panel de profesor</h2>
                            <p class="text-muted mb-0">
                                Bienvenido/a, <?= htmlspecialchars($datos['usuario']['nombre'] . ' ' . $datos['usuario']['apellidos']) ?>
                            </p>
                        </div>
                        <div class="ms-auto">
                            <div class="d-flex flex-column align-items-end">
                                <span class="badge bg-primary mb-2">
                                    <i class="fas fa-calendar-alt me-1"></i> <?= date('d/m/Y') ?>
                                </span>
                                <div class="btn-group">
                                    <a href="<?= BASE_URL ?>/examenes/crear" class="btn btn-sm btn-outline-primary rounded-pill me-2">
                                        <i class="fas fa-file-alt"></i> Crear examen
                                    </a>
                                    <a href="<?= BASE_URL ?>/usuarios/crear" class="btn btn-sm btn-outline-success rounded-pill">
                                        <i class="fas fa-user-plus"></i> Crear alumno
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Fila superior: Resumen y calendario -->
    <div class="row mb-4">
        <!-- Calendario y eventos -->
        <div class="col-md-8 mb-4 mb-md-0">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-calendar-alt text-primary me-2"></i> Calendario de exámenes
                        </h5>
                        <a href="<?= BASE_URL ?>/calendario" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fas fa-expand-alt me-1"></i> Ver completo
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendario-profesor" style="height: 350px">
                        <?php 
                        // Cargar eventos del calendario desde PHP
                        if (isset($datos['carga_via_api']) && $datos['carga_via_api']) {
                            // El calendario se inicializará con eventos desde JavaScript, pero mostramos estructura básica
                            echo '<div id="calendario-cargando" class="d-flex justify-content-center align-items-center h-100">
                                    <div class="text-center">
                                        <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                        <p class="text-muted mb-0">Inicializando calendario...</p>
                                    </div>
                                  </div>';
                        } else {
                            echo '<div class="d-flex justify-content-center align-items-center h-100">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando calendario...</span>
                                    </div>
                                  </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notificaciones y alertas -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-bell text-primary me-2"></i> Notificaciones
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="lista-notificaciones-profesor">
                        <?php 
                        if (isset($datos['carga_via_api']) && $datos['carga_via_api']) {
                            // Mostrar notificaciones básicas o mensaje de no hay notificaciones
                        ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">¡Bienvenido al sistema!</h6>
                                <small><?= date('d/m/Y') ?></small>
                            </div>
                            <p class="mb-1">Tu panel está listo para gestionar cursos y exámenes.</p>
                            <small class="text-muted">Sistema</small>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Recuerda</h6>
                                <small>Siempre</small>
                            </div>
                            <p class="mb-1">Mantén actualizados los datos de tus cursos y exámenes.</p>
                            <small class="text-muted">Consejo</small>
                        </div>
                        <?php 
                        } else {
                        ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Cargando notificaciones...</h6>
                            </div>
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="fas fa-check-double me-1"></i> Marcar todas como leídas
                        </button>
                        <a href="<?= BASE_URL ?>/notificaciones" class="btn btn-sm btn-primary rounded-pill px-3">
                            Ver todas <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cursos y módulos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-book-open text-primary me-2"></i> Cursos y módulos
                        </h5>
                        <div>
                            <a href="<?= BASE_URL ?>/examenes/crear" class="btn btn-sm btn-success rounded-pill px-3 me-2 shadow-sm">
                                <i class="fas fa-plus me-1"></i> Crear examen
                            </a>
                            <a href="<?= BASE_URL ?>/usuarios/crear" class="btn btn-sm btn-primary rounded-pill px-3 me-2 shadow-sm">
                                <i class="fas fa-user-plus me-1"></i> Crear alumno
                            </a>
                            <a href="<?= BASE_URL ?>/cursos" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Ver todos <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Curso</th>
                                    <th>Módulo</th>
                                    <th>Alumnos</th>
                                    <th>Exámenes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-cursos-profesor">
                                <?php 
                                // Mostrar datos reales desde PHP
                                if (isset($datos['carga_via_api']) && $datos['carga_via_api']) {
                                    // Cargar datos reales desde los modelos
                                    require_once APP_PATH . '/modelos/curso_modelo.php';
                                    require_once APP_PATH . '/modelos/examen_modelo.php';
                                    $cursoModelo = new Curso();
                                    $examenModelo = new Examen();
                                    $cursosReales = $cursoModelo->obtenerCursosPorProfesor($_SESSION['id_usuario']);
                                    
                                    if (!empty($cursosReales)):
                                        foreach ($cursosReales as $curso):
                                            // Contar alumnos del curso
                                            $numAlumnos = $cursoModelo->contarAlumnosPorCurso($curso['id_curso']);
                                            // Contar exámenes del curso
                                            $examenesDelCurso = $examenModelo->obtenerPorCurso($curso['id_curso']);
                                            $numExamenes = count($examenesDelCurso);
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-book text-primary fa-lg me-2"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($curso['nombre_curso']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($curso['descripcion'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info rounded-pill">
                                            <i class="fas fa-users me-1"></i> <?= $numAlumnos ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill">
                                            <i class="fas fa-file-alt me-1"></i> <?= $numExamenes ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/cursos/ver?id=<?= $curso['id_curso'] ?>" 
                                               class="btn btn-light rounded-pill border px-2 shadow-sm me-1" 
                                               title="Ver curso">
                                                <i class="fas fa-eye text-info"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/cursos/editar?id=<?= $curso['id_curso'] ?>" 
                                               class="btn btn-light rounded-pill border px-2 shadow-sm me-1" 
                                               title="Editar curso">
                                                <i class="fas fa-edit text-primary"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/cursos/alumnos?id=<?= $curso['id_curso'] ?>" 
                                               class="btn btn-light rounded-pill border px-2 shadow-sm" 
                                               title="Ver alumnos">
                                                <i class="fas fa-users text-success"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                        endforeach;
                                    else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center p-4">
                                        <div class="text-muted">
                                            <i class="fas fa-book fa-2x mb-2"></i>
                                            <p class="mb-0">No tienes cursos asignados aún</p>
                                            <small>Crea tu primer curso para comenzar</small>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    endif;
                                } else {
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center p-3">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando cursos...</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Exámenes recientes -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-file-alt text-primary me-2"></i> Exámenes recientes
                        </h5>
                        <a href="<?= BASE_URL ?>/examenes" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            Ver todos <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Título</th>
                                    <th>Módulo</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-examenes-recientes">
                                <?php 
                                // Mostrar exámenes reales desde PHP
                                if (isset($datos['carga_via_api']) && $datos['carga_via_api']) {
                                    if (!isset($examenModelo)) {
                                        require_once APP_PATH . '/modelos/examen_modelo.php';
                                        $examenModelo = new Examen();
                                    }
                                    $examenesReales = $examenModelo->obtenerPorProfesor($_SESSION['id_usuario']);
                                    
                                    // Ordenar por fecha más reciente y limitar a 5
                                    usort($examenesReales, function($a, $b) {
                                        return strtotime($b['fecha_inicio']) - strtotime($a['fecha_inicio']);
                                    });
                                    $examenesReales = array_slice($examenesReales, 0, 5);
                                    
                                    if (!empty($examenesReales)):
                                        foreach ($examenesReales as $examen):
                                            // Determinar estado del examen
                                            $ahora = new DateTime();
                                            $fechaInicio = new DateTime($examen['fecha_inicio']);
                                            $fechaFin = new DateTime($examen['fecha_fin']);
                                            
                                            if ($ahora < $fechaInicio) {
                                                $estado = 'pendiente';
                                                $estadoTexto = 'Pendiente';
                                                $estadoClase = 'bg-warning-subtle text-warning';
                                            } else if ($ahora >= $fechaInicio && $ahora <= $fechaFin) {
                                                $estado = 'activo';
                                                $estadoTexto = 'Activo';
                                                $estadoClase = 'bg-success-subtle text-success';
                                            } else {
                                                $estado = 'finalizado';
                                                $estadoTexto = 'Finalizado';
                                                $estadoClase = 'bg-secondary-subtle text-secondary';
                                            }
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-file-alt text-primary fa-lg me-2"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($examen['titulo']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars(substr($examen['descripcion'] ?? '', 0, 50)) ?><?= strlen($examen['descripcion'] ?? '') > 50 ? '...' : '' ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($examen['fecha_inicio'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $estadoClase ?> rounded-pill">
                                            <i class="fas fa-circle me-1"></i> <?= $estadoTexto ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/examenes/ver?id=<?= $examen['id_examen'] ?>" 
                                               class="btn btn-light rounded-pill border px-2 shadow-sm me-1" 
                                               title="Ver examen">
                                                <i class="fas fa-eye text-info"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/examenes/editar?id=<?= $examen['id_examen'] ?>" 
                                               class="btn btn-light rounded-pill border px-2 shadow-sm me-1" 
                                               title="Editar examen">
                                                <i class="fas fa-edit text-primary"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/examenes/resultados?id=<?= $examen['id_examen'] ?>" 
                                               class="btn btn-light rounded-pill border px-2 shadow-sm" 
                                               title="Ver resultados">
                                                <i class="fas fa-chart-bar text-success"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                        endforeach;
                                    else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center p-4">
                                        <div class="text-muted">
                                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                                            <p class="mb-0">No has creado exámenes aún</p>
                                            <small>Crea tu primer examen para evaluar a tus alumnos</small>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    endif;
                                } else {
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center p-3">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando exámenes...</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas personales -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-chart-pie text-primary me-2"></i> Estadísticas
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="grafico-estadisticas-profesor" height="200"></canvas>
                </div>
                <div class="card-footer bg-light">
                    <div class="row text-center">
                        <div class="col">
                            <h5 class="mb-0" id="total-examenes-creados">
                                <?php 
                                if (isset($datos['carga_via_api']) && $datos['carga_via_api']) {
                                    if (!isset($examenModelo)) {
                                        require_once APP_PATH . '/modelos/examen_modelo.php';
                                        $examenModelo = new Examen();
                                    }
                                    $totalExamenes = count($examenModelo->obtenerPorProfesor($_SESSION['id_usuario']));
                                    echo $totalExamenes;
                                } else {
                                    echo '0';
                                }
                                ?>
                            </h5>
                            <small class="text-muted">Exámenes creados</small>
                        </div>
                        <div class="col">
                            <h5 class="mb-0" id="total-examenes-pendientes">
                                <?php 
                                if (isset($datos['carga_via_api']) && $datos['carga_via_api']) {
                                    if (!isset($examenModelo)) {
                                        require_once APP_PATH . '/modelos/examen_modelo.php';
                                        $examenModelo = new Examen();
                                    }
                                    $examenes = $examenModelo->obtenerPorProfesor($_SESSION['id_usuario']);
                                    $pendientes = 0;
                                    $ahora = new DateTime();
                                    foreach ($examenes as $examen) {
                                        $fechaInicio = new DateTime($examen['fecha_inicio']);
                                        if ($ahora < $fechaInicio) {
                                            $pendientes++;
                                        }
                                    }
                                    echo $pendientes;
                                } else {
                                    echo '0';
                                }
                                ?>
                            </h5>
                            <small class="text-muted">Por corregir</small>
                        </div>
                        <div class="col">
                            <h5 class="mb-0" id="promedio-notas">
                                <?php 
                                if (isset($datos['carga_via_api']) && $datos['carga_via_api']) {
                                    echo '0.0'; // Por ahora mostrar 0.0, se puede implementar cálculo real después
                                } else {
                                    echo '0.0';
                                }
                                ?>
                            </h5>
                            <small class="text-muted">Nota media</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sugerencias IA -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-robot text-primary fa-2x me-3"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Sugerencias de IA disponibles</h5>
                            <p class="card-text small mb-0">
                                Hay 3 sugerencias de mejora para tus exámenes basadas en inteligencia artificial.
                                <a href="<?= BASE_URL ?>/examenes/sugerencias-ia" class="ms-2">Ver sugerencias</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir el JavaScript de la API para datos reales -->
<script src="<?= BASE_URL ?>/publico/recursos/js/profesor_dashboard_api.js"></script>

<script>
    // Script de inicialización para dashboard de profesor
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar calendario
        const calendarioEl = document.getElementById('calendario-profesor');
        
        if (calendarioEl) {
            // Limpiar contenido inicial
            calendarioEl.innerHTML = '';
            
            try {
                const calendario = new FullCalendar.Calendar(calendarioEl, {
                    initialView: 'dayGridMonth',
                    locale: 'es',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth'
                    },
                    height: 350,
                    events: async function(fetchInfo, successCallback, failureCallback) {
                        try {
                            const response = await fetch('<?= BASE_URL ?>/publico/api/profesor/index.php?ruta=calendario');
                            if (!response.ok) {
                                throw new Error('Error en la respuesta');
                            }
                            const eventos = await response.json();
                            successCallback(Array.isArray(eventos) ? eventos : []);
                        } catch (error) {
                            console.log('Error cargando eventos del calendario:', error);
                            // Mostrar calendario vacío en lugar de error
                            successCallback([]);
                        }
                    },
                    eventDidMount: function(info) {
                        info.el.setAttribute('title', info.event.title);
                    },
                    noEventsContent: 'No hay exámenes programados'
                });
                
                calendario.render();
            } catch (error) {
                console.error('Error inicializando calendario:', error);
                calendarioEl.innerHTML = `
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="text-center text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <p class="mb-0">Error al cargar el calendario</p>
                        </div>
                    </div>
                `;
            }
        }
        
        // Unificar estilos de badges y botones con el panel de administración
        function unificarEstilosUI() {
            // Los estilos ya están aplicados desde PHP, solo aplicar mejoras adicionales
            
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
        // Aplicar estilos inmediatamente
        unificarEstilosUI();
        
        // Re-aplicar después de 500ms para cualquier contenido dinámico
        setTimeout(unificarEstilosUI, 500);
    });
</script>

<!-- Estilos adicionales para el color morado (alumno) -->
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
