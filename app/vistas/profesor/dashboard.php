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
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando calendario...</span>
                            </div>
                        </div>
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
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Cargando notificaciones...</h6>
                            </div>
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
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
    
    <!-- Mis cursos y módulos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-book-open text-primary me-2"></i> Mis cursos y módulos
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
                                <tr>
                                    <td colspan="5" class="text-center p-3">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando cursos...</span>
                                        </div>
                                    </td>
                                </tr>
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
                                <tr>
                                    <td colspan="5" class="text-center p-3">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando exámenes...</span>
                                        </div>
                                    </td>
                                </tr>
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
                            <h5 class="mb-0" id="total-examenes-creados">0</h5>
                            <small class="text-muted">Exámenes creados</small>
                        </div>
                        <div class="col">
                            <h5 class="mb-0" id="total-examenes-pendientes">0</h5>
                            <small class="text-muted">Por corregir</small>
                        </div>
                        <div class="col">
                            <h5 class="mb-0" id="promedio-notas">0.0</h5>
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

<script>
    // Script de inicialización para dashboard de profesor
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar calendario
        const calendarioEl = document.getElementById('calendario-profesor');
        
        // Añadir clase de estilo para profesor
        calendarioEl.classList.add('profesor-calendar');
        
        const calendario = new FullCalendar.Calendar(calendarioEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            height: 350,
            events: [
                {
                    title: 'Examen Matemáticas',
                    start: '2025-06-20',
                    backgroundColor: '#4285F4',
                    borderColor: '#3266c2',
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
            });
            
            // Transformar los botones de acción en botones redondos con iconos coloreados
            document.querySelectorAll('.table tbody a.btn, .table tbody button.btn').forEach(btn => {
                if (!btn.classList.contains('rounded-pill')) {
                    btn.classList.add('btn-light', 'rounded-pill', 'border', 'px-2', 'shadow-sm');
                    btn.classList.remove('btn-primary', 'btn-success', 'btn-danger', 'btn-warning', 'btn-info');
                    
                    // Colorear iconos dentro de los botones
                    const icon = btn.querySelector('i.fas, i.far, i.fab');
                    if (icon) {
                        // Detectar el tipo de acción basado en clases o texto del botón
                        if (btn.innerHTML.includes('Editar') || icon.classList.contains('fa-edit') || icon.classList.contains('fa-pencil')) {
                            icon.classList.add('text-primary');
                        } else if (btn.innerHTML.includes('Ver') || btn.innerHTML.includes('Mostrar') || icon.classList.contains('fa-eye')) {
                            icon.classList.add('text-info');
                        } else if (btn.innerHTML.includes('Eliminar') || icon.classList.contains('fa-trash')) {
                            icon.classList.add('text-danger');
                        } else if (btn.innerHTML.includes('Descargar') || icon.classList.contains('fa-download')) {
                            icon.classList.add('text-success');
                        } else if (btn.innerHTML.includes('Historial') || icon.classList.contains('fa-history')) {
                            icon.classList.add('text-info');
                        } else if (btn.innerHTML.includes('Añadir') || icon.classList.contains('fa-plus')) {
                            icon.classList.add('text-success');
                        } else if (btn.innerHTML.includes('Bloquear') || icon.classList.contains('fa-ban')) {
                            icon.classList.add('text-danger');
                        }
                    }
                }
            });
            
            // Añadir tratamiento específico para los badges de rol
            document.querySelectorAll('[data-rol]').forEach(badge => {
                const rol = badge.getAttribute('data-rol');
                badge.classList.add('rounded-pill');
                
                if (rol === 'admin') {
                    badge.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle';
                    // Añadir icono si no existe
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-crown"></i> ' + badge.innerHTML;
                    }
                } else if (rol === 'profesor') {
                    badge.className = 'badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-chalkboard-teacher"></i> ' + badge.innerHTML;
                    }
                } else if (rol === 'alumno') {
                    badge.className = 'badge rounded-pill bg-purple text-white';
                    if (!badge.querySelector('i')) {
                        badge.innerHTML = '<i class="fas fa-user-graduate"></i> ' + badge.innerHTML;
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
                }
            });
        }
        
        // Cargar tabla de cursos (simulación)
        setTimeout(() => {
            // Añadir datos de ejemplo a la tabla de cursos
            const tablaCursos = document.getElementById('tabla-cursos-profesor');
            tablaCursos.innerHTML = `
                <tr>
                    <td>Matemáticas 3º ESO</td>
                    <td>Álgebra</td>
                    <td><span class="badge" data-estado="activo">28 alumnos</span></td>
                    <td><span class="badge" data-estado="pendiente">3 activos</span></td>
                    <td>
                        <a href="#" class="btn btn-sm"><i class="fas fa-eye"></i> Ver</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-edit"></i> Editar</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-plus"></i> Examen</a>
                    </td>
                </tr>
                <tr>
                    <td>Física 4º ESO</td>
                    <td>Electricidad</td>
                    <td><span class="badge" data-estado="activo">24 alumnos</span></td>
                    <td><span class="badge" data-estado="completado">2 activos</span></td>
                    <td>
                        <a href="#" class="btn btn-sm"><i class="fas fa-eye"></i> Ver</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-edit"></i> Editar</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-plus"></i> Examen</a>
                    </td>
                </tr>
                <tr>
                    <td>Química 2º Bachillerato</td>
                    <td>Química Orgánica</td>
                    <td><span class="badge" data-estado="activo">18 alumnos</span></td>
                    <td><span class="badge" data-estado="completado">0 activos</span></td>
                    <td>
                        <a href="#" class="btn btn-sm"><i class="fas fa-eye"></i> Ver</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-edit"></i> Editar</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-plus"></i> Examen</a>
                    </td>
                </tr>
            `;
            
            // Cargar exámenes recientes
            const tablaExamenes = document.getElementById('tabla-examenes-recientes');
            tablaExamenes.innerHTML = `
                <tr>
                    <td>Ecuaciones de segundo grado</td>
                    <td>Álgebra</td>
                    <td>20/06/2025</td>
                    <td><span class="badge" data-estado="pendiente">Pendiente</span></td>
                    <td>
                        <a href="#" class="btn btn-sm"><i class="fas fa-eye"></i> Ver</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-edit"></i> Editar</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-download"></i> Exportar</a>
                    </td>
                </tr>
                <tr>
                    <td>Leyes de Newton</td>
                    <td>Física Mecánica</td>
                    <td>15/06/2025</td>
                    <td><span class="badge" data-estado="activo">Activo</span></td>
                    <td>
                        <a href="#" class="btn btn-sm"><i class="fas fa-eye"></i> Ver</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-edit"></i> Editar</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-ban"></i> Cerrar</a>
                    </td>
                </tr>
                <tr>
                    <td>Oxidación-reducción</td>
                    <td>Química General</td>
                    <td>10/06/2025</td>
                    <td><span class="badge" data-estado="completado">Completado</span></td>
                    <td>
                        <a href="#" class="btn btn-sm"><i class="fas fa-eye"></i> Ver</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-chart-bar"></i> Estadísticas</a>
                        <a href="#" class="btn btn-sm"><i class="fas fa-download"></i> Exportar</a>
                    </td>
                </tr>
            `;
            
            // Actualizar contadores
            document.getElementById('total-examenes-creados').innerHTML = '15';
            document.getElementById('total-examenes-pendientes').innerHTML = '3';
            document.getElementById('promedio-notas').innerHTML = '7.4';
            
            // Aplicar los estilos unificados
            unificarEstilosUI();
            
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Inicializar gráfico de estadísticas
            const ctx = document.getElementById('grafico-estadisticas-profesor').getContext('2d');
            const graficoEstadisticas = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Aprobados', 'Suspensos', 'Pendientes'],
                    datasets: [{
                        data: [75, 15, 10],
                        backgroundColor: ['#34A853', '#EA4335', '#FBBC05'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }, 500);
        
        // Cargar notificaciones (simulación)
        setTimeout(() => {
            const listaNotificaciones = document.getElementById('lista-notificaciones-profesor');
            listaNotificaciones.innerHTML = `
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Nuevas entregas pendientes</h6>
                        <small class="text-muted">Hoy</small>
                    </div>
                    <p class="mb-1 small">Tienes 5 exámenes pendientes de corrección en Matemáticas 3º ESO.</p>
                    <small><span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle"><i class="fas fa-exclamation-circle"></i> Prioritario</span></small>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Recordatorio de examen</h6>
                        <small class="text-muted">Ayer</small>
                    </div>
                    <p class="mb-1 small">El examen "Leyes de Newton" cierra mañana a las 23:59.</p>
                    <small><span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle"><i class="fas fa-clock"></i> Recordatorio</span></small>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Nuevo estudiante asignado</h6>
                        <small class="text-muted">12/06/2025</small>
                    </div>
                    <p class="mb-1 small">Se ha añadido un nuevo estudiante a tu curso de Química 2º Bachillerato.</p>
                    <small><span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle"><i class="fas fa-info-circle"></i> Información</span></small>
                </a>
            `;
            
            // Aplicar los estilos unificados a los elementos recién agregados
            unificarEstilosUI();
        }, 700);
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
