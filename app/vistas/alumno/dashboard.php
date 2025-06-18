<?php
/**
 * Dashboard de Alumno - AUTOEXAM2
 * 
 * Panel principal de control para alumnos
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Incluir cabecera
require_once APP_PATH . '/vistas/parciales/head_alumno.php';

// Incluir barra de navegación
require_once APP_PATH . '/vistas/parciales/navbar_alumno.php';
?>

<div class="container-fluid py-4">
    <!-- Cabecera y bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <!-- Avatar del alumno -->
                        <div class="me-4">
                            <?php if (!empty($_SESSION['foto'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($_SESSION['foto']) ?>" 
                                     class="rounded-circle border border-2 border-success shadow-sm" 
                                     width="80" height="80" alt="Avatar" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-purple rounded-circle d-flex align-items-center justify-content-center
                                            border border-2 border-light shadow-sm"
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    <i class="fas fa-user-graduate text-white"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <h2 class="card-title mb-0">Panel de alumno</h2>
                            <p class="text-muted mb-0">
                                Bienvenido/a, <?= htmlspecialchars($datos['usuario']['nombre'] . ' ' . $datos['usuario']['apellidos']) ?>
                            </p>
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-success">
                                <i class="fas fa-calendar-alt me-1"></i> <?= date('d/m/Y') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Exámenes activos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-hourglass-half text-purple me-2"></i> Exámenes activos
                        </h5>
                        <a href="<?= BASE_URL ?>/examenes/disponibles" class="btn btn-sm btn-outline-purple rounded-pill px-3">
                            Ver todos <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="lista-examenes-activos">
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-purple" role="status">
                                <span class="visually-hidden">Cargando exámenes...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Calificaciones y resultados -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-chart-line text-purple me-2"></i> Mis calificaciones
                        </h5>
                        <a href="<?= BASE_URL ?>/calificaciones" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            Ver historial completo <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Examen</th>
                                    <th>Módulo</th>
                                    <th>Fecha</th>
                                    <th>Calificación</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-calificaciones">
                                <tr>
                                    <td colspan="6" class="text-center p-3">
                                        <div class="spinner-border text-success" role="status">
                                            <span class="visually-hidden">Cargando calificaciones...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-star text-purple me-2"></i> Progreso académico
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="grafico-calificaciones-alumno" height="200"></canvas>
                </div>
                <div class="card-footer bg-light">
                    <div class="row text-center">
                        <div class="col">
                            <h5 class="mb-0" id="total-examenes-realizados">0</h5>
                            <small class="text-muted">Exámenes realizados</small>
                        </div>
                        <div class="col">
                            <h5 class="mb-0" id="nota-media-alumno">0.0</h5>
                            <small class="text-muted">Nota media</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mis cursos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-book-open text-success me-2"></i> Mis cursos
                        </h5>
                        <a href="<?= BASE_URL ?>/cursos/misCursos" class="btn btn-sm btn-outline-success rounded-pill px-3">
                            Ver todos <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="lista-mis-cursos">
                        <?php if(isset($cursos_alumno) && !empty($cursos_alumno)): ?>
                            <?php foreach($cursos_alumno as $index => $curso): ?>
                                <?php if($index < 3): ?>
                                <a href="<?= BASE_URL ?>/cursos/ver?id=<?= $curso['id_curso'] ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($curso['nombre_curso']) ?></h6>
                                        <?php if($curso['activo'] == 1): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-1 small text-muted">Profesor: <?= htmlspecialchars($curso['nombre_profesor'] . ' ' . $curso['apellidos_profesor']) ?></p>
                                </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center p-3">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Cargando cursos...</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Próximos exámenes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-calendar text-purple me-2"></i> Próximos exámenes
                    </h5>
                </div>
                <div class="card-body">
                    <div id="calendario-alumno" style="height: 300px">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Cargando calendario...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <a href="<?= BASE_URL ?>/calendario" class="btn btn-sm btn-primary rounded-pill px-3">
                        Ver calendario completo <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notificaciones -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-bell text-warning fa-2x me-3"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Recordatorio</h5>
                            <p class="card-text small mb-0">
                                Tienes un examen activo de Matemáticas que vence en 2 días.
                                <a href="<?= BASE_URL ?>/examenes/disponibles" class="ms-2">Ver examen</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir pie de página
require_once APP_PATH . '/vistas/parciales/footer_alumno.php';

// Incluir scripts
require_once APP_PATH . '/vistas/parciales/scripts_alumno.php';
?>

<script>
    // Script de inicialización para dashboard de alumno
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar calendario
        const calendarioEl = document.getElementById('calendario-alumno');
        
        // Añadir clase de estilo para alumno
        calendarioEl.classList.add('alumno-calendar');
        
        const calendario = new FullCalendar.Calendar(calendarioEl, {
            initialView: 'listWeek',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listWeek'
            },
            height: 300,
            events: [
                {
                    title: 'Examen Matemáticas',
                    start: '2025-06-20',
                    backgroundColor: '#7d50c4',
                    borderColor: '#6e46a8',
                    textColor: '#ffffff'
                },
                {
                    title: 'Examen Literatura',
                    start: '2025-06-22',
                    backgroundColor: '#34A853',
                    borderColor: '#2d8d47',
                    textColor: '#ffffff'
                },
                {
                    title: 'Examen Historia',
                    start: '2025-06-25',
                    backgroundColor: '#EA4335',
                    borderColor: '#d23829',
                    textColor: '#ffffff'
                }
            ],
            eventClick: function(info) {
                // Redireccionar a la página del examen
                alert(`Examen: ${info.event.title}`);
            }
        });
        calendario.render();
        
        // Unificar estilos de badges y botones con el panel de administración
        function unificarEstilosUI() {
            // Transformar todos los badges regulares a pill badges con borde
            document.querySelectorAll('.badge:not(.rounded-pill)').forEach(badge => {
                badge.classList.add('rounded-pill');
                
                // Aplicar estilos según el color de fondo
                if (badge.classList.contains('bg-primary')) {
                    badge.classList.remove('bg-primary');
                    badge.classList.add('bg-primary-subtle', 'text-primary', 'border', 'border-primary-subtle');
                } else if (badge.classList.contains('bg-success')) {
                    badge.classList.remove('bg-success');
                    badge.classList.add('bg-success-subtle', 'text-success', 'border', 'border-success-subtle');
                } else if (badge.classList.contains('bg-danger')) {
                    badge.classList.remove('bg-danger');
                    badge.classList.add('bg-danger-subtle', 'text-danger', 'border', 'border-danger-subtle');
                } else if (badge.classList.contains('bg-warning')) {
                    badge.classList.remove('bg-warning');
                    badge.classList.add('bg-warning-subtle', 'text-warning', 'border', 'border-warning-subtle');
                } else if (badge.classList.contains('bg-info')) {
                    badge.classList.remove('bg-info');
                    badge.classList.add('bg-info-subtle', 'text-info', 'border', 'border-info-subtle');
                } else if (badge.classList.contains('bg-secondary')) {
                    badge.classList.remove('bg-secondary');
                    badge.classList.add('bg-secondary-subtle', 'text-secondary', 'border', 'border-secondary-subtle');
                }
                // Conservamos el estilo púrpura especial para alumnos
            });
            
            // Transformar los botones de acción en botones redondos con iconos coloreados
            document.querySelectorAll('.table tbody a.btn, .table tbody button.btn, .card-footer a.btn:not(.rounded-pill):not(.btn-primary):not(.btn-outline-primary):not(.btn-outline-purple)').forEach(btn => {
                if (!btn.classList.contains('rounded-pill')) {
                    btn.classList.add('btn-light', 'rounded-pill', 'border', 'px-2', 'shadow-sm');
                    btn.classList.remove('btn-success', 'btn-danger', 'btn-warning', 'btn-info');
                    
                    // Colorear iconos dentro de los botones
                    const icon = btn.querySelector('i.fas, i.far, i.fab');
                    if (icon) {
                        // Detectar el tipo de acción basado en clases o texto del botón
                        if (btn.innerHTML.includes('Realizar') || icon.classList.contains('fa-edit')) {
                            icon.classList.add('text-purple');
                        } else if (btn.innerHTML.includes('Ver') || btn.innerHTML.includes('Mostrar') || icon.classList.contains('fa-eye')) {
                            icon.classList.add('text-info');
                        } else if (btn.innerHTML.includes('Descargar') || icon.classList.contains('fa-download')) {
                            icon.classList.add('text-success');
                        } else if (btn.innerHTML.includes('Historial') || icon.classList.contains('fa-history')) {
                            icon.classList.add('text-info');
                        } else if (btn.innerHTML.includes('Revisar') || icon.classList.contains('fa-check-double')) {
                            icon.classList.add('text-success');
                        }
                    }
                }
            });
            
            // Añadir tratamiento específico para los badges de estado
            document.querySelectorAll('[data-estado]').forEach(badge => {
                const estado = badge.getAttribute('data-estado');
                badge.classList.add('rounded-pill');
                
                if (estado === 'activo' || estado === '1') {
                    badge.className = 'badge rounded-pill bg-success-subtle text-success border border-success-subtle';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-check"></i> ' + badge.innerHTML;
                    }
                } else if (estado === 'inactivo' || estado === '0') {
                    badge.className = 'badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-times"></i> ' + badge.innerHTML;
                    }
                } else if (estado === 'pendiente') {
                    badge.className = 'badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-clock"></i> ' + badge.innerHTML;
                    }
                } else if (estado === 'completado') {
                    badge.className = 'badge rounded-pill bg-success-subtle text-success border border-success-subtle';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-check-double"></i> ' + badge.innerHTML;
                    }
                } else if (estado === 'revisado') {
                    badge.className = 'badge rounded-pill bg-info-subtle text-info border border-info-subtle';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-eye"></i> ' + badge.innerHTML;
                    }
                } else if (estado === 'suspendido') {
                    badge.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-times-circle"></i> ' + badge.innerHTML;
                    }
                } else if (estado === 'aprobado') {
                    badge.className = 'badge rounded-pill bg-success-subtle text-success border border-success-subtle';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-check-circle"></i> ' + badge.innerHTML;
                    }
                } else if (estado.toLowerCase().includes('min')) {
                    // Para tiempos restantes críticos
                    badge.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-hourglass-end"></i> ' + badge.innerHTML;
                    }
                } else if (estado.toLowerCase().includes('hora')) {
                    // Para tiempos restantes normales
                    badge.className = 'badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-hourglass-half"></i> ' + badge.innerHTML;
                    }
                } else if (estado.toLowerCase().includes('día')) {
                    // Para tiempos restantes amplios
                    badge.className = 'badge rounded-pill bg-success-subtle text-success border border-success-subtle';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-hourglass-start"></i> ' + badge.innerHTML;
                    }
                }
            });
            
            // Mejorar visualmente las tarjetas de exámenes activos
            document.querySelectorAll('.card .card-header').forEach(header => {
                if (!header.classList.contains('bg-light')) {
                    header.classList.add('bg-light');
                }
            });
        }
        
        // Cargar datos de exámenes activos
        setTimeout(() => {
            const listaExamenesActivos = document.getElementById('lista-examenes-activos');
            listaExamenesActivos.innerHTML = `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-light">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <i class="fas fa-square-root-alt text-white"></i>
                                </div>
                                <h6 class="mb-0">Ecuaciones de segundo grado</h6>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-book-open text-purple me-2 fa-fw"></i>
                                <span><strong>Módulo:</strong> Álgebra</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-chalkboard text-purple me-2 fa-fw"></i>
                                <span><strong>Curso:</strong> Matemáticas 3º ESO</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-calendar-day text-purple me-2 fa-fw"></i>
                                <span><strong>Fecha límite:</strong> 20/06/2025</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-hourglass-half text-danger me-2 fa-fw"></i>
                                <span><strong>Tiempo restante:</strong> <span class="badge" data-estado="2 días">2 días</span></span>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <a href="#" class="btn btn-purple text-white rounded-pill w-100">
                                <i class="fas fa-edit me-1"></i> Realizar Examen
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-light">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <i class="fas fa-language text-white"></i>
                                </div>
                                <h6 class="mb-0">Análisis sintáctico</h6>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-book-open text-purple me-2 fa-fw"></i>
                                <span><strong>Módulo:</strong> Gramática</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-chalkboard text-purple me-2 fa-fw"></i>
                                <span><strong>Curso:</strong> Lengua 3º ESO</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-calendar-day text-purple me-2 fa-fw"></i>
                                <span><strong>Fecha límite:</strong> 18/06/2025</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-hourglass-half text-warning me-2 fa-fw"></i>
                                <span><strong>Tiempo restante:</strong> <span class="badge" data-estado="12 horas">12 horas</span></span>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <a href="#" class="btn btn-purple text-white rounded-pill w-100">
                                <i class="fas fa-edit me-1"></i> Realizar Examen
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-light">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <i class="fas fa-flask text-white"></i>
                                </div>
                                <h6 class="mb-0">Elementos químicos</h6>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-book-open text-purple me-2 fa-fw"></i>
                                <span><strong>Módulo:</strong> Tabla periódica</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-chalkboard text-purple me-2 fa-fw"></i>
                                <span><strong>Curso:</strong> Química 4º ESO</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-calendar-day text-purple me-2 fa-fw"></i>
                                <span><strong>Fecha límite:</strong> 17/06/2025</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-hourglass-half text-danger me-2 fa-fw"></i>
                                <span><strong>Tiempo restante:</strong> <span class="badge" data-estado="30 min">30 min</span></span>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <a href="#" class="btn btn-danger text-white rounded-pill w-100">
                                <i class="fas fa-exclamation-triangle me-1"></i> Realizar ahora
                            </a>
                        </div>
                    </div>
                </div>
            `;
            
            // Aplicar los estilos unificados a los elementos recién agregados
            unificarEstilosUI();
            
            // Cargar calificaciones (simulación)
            const tablaCalificaciones = document.getElementById('tabla-calificaciones');
            tablaCalificaciones.innerHTML = `
                <tr>
                    <td>Historia del Arte</td>
                    <td>Arte Contemporáneo</td>
                    <td>15/06/2025</td>
                    <td>
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                            <i class="fas fa-check-circle"></i> 8.5
                        </span>
                    </td>
                    <td>
                        <span class="badge" data-estado="revisado">Revisado</span>
                    </td>
                    <td>
                        <a href="#" class="btn btn-sm"><i class="fas fa-eye"></i> Ver</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-download"></i> Descargar</a>
                    </td>
                </tr>
                <tr>
                    <td>Geometría Analítica</td>
                    <td>Vectores</td>
                    <td>10/06/2025</td>
                    <td>
                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle">
                            <i class="fas fa-exclamation-circle"></i> 4.8
                        </span>
                    </td>
                    <td>
                        <span class="badge" data-estado="suspendido">Suspendido</span>
                    </td>
                    <td>
                        <a href="#" class="btn btn-sm"><i class="fas fa-eye"></i> Ver</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-download"></i> Descargar</a>
                    </td>
                </tr>
                <tr>
                    <td>Literatura Medieval</td>
                    <td>La Celestina</td>
                    <td>05/06/2025</td>
                    <td>
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                            <i class="fas fa-check-circle"></i> 9.2
                        </span>
                    </td>
                    <td>
                        <span class="badge" data-estado="aprobado">Aprobado</span>
                    </td>
                    <td>
                        <a href="#" class="btn btn-sm"><i class="fas fa-eye"></i> Ver</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-download"></i> Descargar</a>
                    </td>
                </tr>
            `;
            
            // Actualizar contadores
            document.getElementById('total-examenes-realizados').innerHTML = '12';
            document.getElementById('nota-media-alumno').innerHTML = '7.8';
            
            // Volver a aplicar estilos a los nuevos elementos
            unificarEstilosUI();
            
            // Inicializar gráfico de calificaciones
            const ctx = document.getElementById('grafico-calificaciones-alumno').getContext('2d');
            const graficoCalificaciones = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Matemáticas', 'Lengua', 'Historia', 'Física', 'Química'],
                    datasets: [{
                        label: 'Nota media por asignatura',
                        data: [8.2, 7.5, 9.0, 7.0, 8.5],
                        backgroundColor: [
                            'rgba(66, 133, 244, 0.7)',
                            'rgba(219, 68, 55, 0.7)',
                            'rgba(244, 180, 0, 0.7)',
                            'rgba(15, 157, 88, 0.7)',
                            'rgba(138, 92, 209, 0.7)'
                        ],
                        borderColor: [
                            'rgba(66, 133, 244, 1)',
                            'rgba(219, 68, 55, 1)',
                            'rgba(244, 180, 0, 1)',
                            'rgba(15, 157, 88, 1)',
                            'rgba(138, 92, 209, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10
                        }
                    }
                }
            });
        }, 500);
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
    .btn-purple {
        background-color: #8a5cd1 !important;
        border-color: #8a5cd1 !important;
    }
    .btn-outline-purple {
        color: #8a5cd1;
        border-color: #8a5cd1;
    }
    .btn-outline-purple:hover {
        color: #fff;
        background-color: #8a5cd1;
        border-color: #8a5cd1;
    }
</style>
