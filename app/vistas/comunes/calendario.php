<?php
/**
 * Vista de Calendario de Exámenes - AUTOEXAM2
 * 
 * Muestra el calendario completo de exámenes para todos los roles
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Asegurarnos de que el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ' . BASE_URL . '/autenticacion/iniciar');
    exit;
}

// Incluir el head según el rol del usuario
$rolUsuario = $_SESSION['rol'] ?? 'alumno';
switch ($rolUsuario) {
    case 'admin':
        require_once APP_PATH . '/vistas/parciales/head_admin.php';
        break;
    case 'profesor':
        require_once APP_PATH . '/vistas/parciales/head_profesor.php';
        break;
    case 'alumno':
    default:
        require_once APP_PATH . '/vistas/parciales/head_alumno.php';
        break;
}
?>

<body class="bg-light">
    <?php 
    // Incluir la barra de navegación según el rol
    switch ($rolUsuario) {
        case 'admin':
            require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
            break;
        case 'profesor':
            require_once APP_PATH . '/vistas/parciales/navbar_profesor.php';
            break;
        case 'alumno':
        default:
            require_once APP_PATH . '/vistas/parciales/navbar_alumno.php';
            break;
    }
    ?>
    
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="h3 mb-0">
                                <i class="fas fa-calendar-alt me-2 <?= ($rolUsuario === 'alumno') ? 'text-purple' : 'text-primary' ?>"></i> 
                                Calendario de Exámenes
                            </h1>
                            <div class="d-flex gap-2">
                                <?php if ($rolUsuario === 'profesor' || $rolUsuario === 'admin'): ?>
                                <a href="<?= BASE_URL ?>/examenes/crear" class="btn btn-success btn-sm rounded-pill shadow-sm">
                                    <i class="fas fa-plus me-1"></i> Nuevo Examen
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2 <?= ($rolUsuario === 'alumno') ? 'text-purple' : 'text-primary' ?>"></i> 
                            Filtros
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="calendario-filtros">
                            <div class="mb-3">
                                <label for="filtro-tipo" class="form-label">Tipo de eventos</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="examenes" id="filtro-examenes" checked>
                                    <label class="form-check-label" for="filtro-examenes">
                                        Exámenes
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="entregas" id="filtro-entregas" checked>
                                    <label class="form-check-label" for="filtro-entregas">
                                        Fechas límite de entrega
                                    </label>
                                </div>
                            </div>
                            
                            <?php if ($rolUsuario === 'profesor' || $rolUsuario === 'admin'): ?>
                            <div class="mb-3">
                                <label for="filtro-curso" class="form-label">Curso</label>
                                <select class="form-select form-select-sm" id="filtro-curso">
                                    <option value="todos" selected>Todos los cursos</option>
                                    <option value="1">Matemáticas 3º ESO</option>
                                    <option value="2">Física 4º ESO</option>
                                    <option value="3">Química 2º Bachillerato</option>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="filtro-modulo" class="form-label">Módulo</label>
                                <select class="form-select form-select-sm" id="filtro-modulo">
                                    <option value="todos" selected>Todos los módulos</option>
                                    <option value="1">Álgebra</option>
                                    <option value="2">Geometría</option>
                                    <option value="3">Electricidad</option>
                                </select>
                            </div>
                            
                            <button type="button" id="btn-aplicar-filtros" class="btn btn-sm <?= ($rolUsuario === 'alumno') ? 'btn-purple' : 'btn-primary' ?> w-100">
                                <i class="fas fa-check me-1"></i> Aplicar filtros
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar me-2 <?= ($rolUsuario === 'alumno') ? 'text-purple' : 'text-primary' ?>"></i>
                            Calendario
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="calendario-completo" class="<?= $rolUsuario ?>-calendar"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles del evento (modal) -->
        <div class="modal fade" id="modal-detalles-evento" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detalles-evento-titulo">Detalles del Evento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="detalles-evento-cuerpo">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <a href="#" id="btn-ver-detalles" class="btn btn-primary">Ver detalles</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php 
    // Incluir el footer según el rol
    switch ($rolUsuario) {
        case 'admin':
            require_once APP_PATH . '/vistas/parciales/footer_admin.php';
            break;
        case 'profesor':
            require_once APP_PATH . '/vistas/parciales/footer_profesor.php';
            break;
        case 'alumno':
        default:
            require_once APP_PATH . '/vistas/parciales/footer_alumno.php';
            break;
    }
    
    // Incluir los scripts según el rol
    switch ($rolUsuario) {
        case 'admin':
            require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
            break;
        case 'profesor':
            require_once APP_PATH . '/vistas/parciales/scripts_profesor.php';
            break;
        case 'alumno':
        default:
            require_once APP_PATH . '/vistas/parciales/scripts_alumno.php';
            break;
    }
    ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarioEl = document.getElementById('calendario-completo');
            const rolUsuario = '<?= $rolUsuario ?>';
            
            const colorPrimario = rolUsuario === 'alumno' ? '#7d50c4' : '#4285F4';
            
            const calendario = new FullCalendar.Calendar(calendarioEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                height: 700,
                events: [
                    {
                        title: 'Examen Matemáticas - Ecuaciones',
                        start: '2025-06-20',
                        end: '2025-06-20',
                        backgroundColor: rolUsuario === 'alumno' ? '#7d50c4' : '#4285F4',
                        borderColor: rolUsuario === 'alumno' ? '#6e46a8' : '#3266c2',
                        textColor: '#ffffff',
                        extendedProps: {
                            tipo: 'examen',
                            modulo: 'Álgebra',
                            curso: 'Matemáticas 3º ESO',
                            descripcion: 'Examen sobre resolución de ecuaciones de segundo grado'
                        }
                    },
                    {
                        title: 'Examen Literatura - La Celestina',
                        start: '2025-06-22',
                        backgroundColor: '#34A853',
                        borderColor: '#2d8d47',
                        textColor: '#ffffff',
                        extendedProps: {
                            tipo: 'examen',
                            modulo: 'Literatura Medieval',
                            curso: 'Lengua 3º ESO',
                            descripcion: 'Examen sobre La Celestina y su contexto histórico'
                        }
                    },
                    {
                        title: 'Entrega trabajo Historia',
                        start: '2025-06-25',
                        backgroundColor: '#EA4335',
                        borderColor: '#d23829',
                        textColor: '#ffffff',
                        extendedProps: {
                            tipo: 'entrega',
                            modulo: 'Historia Moderna',
                            curso: 'Historia 4º ESO',
                            descripcion: 'Entrega del trabajo sobre la Revolución Francesa'
                        }
                    }
                ],
                eventClick: function(info) {
                    // Mostrar detalles del evento en el modal
                    const evento = info.event;
                    const props = evento.extendedProps;
                    
                    document.getElementById('detalles-evento-titulo').textContent = evento.title;
                    
                    let contenido = `
                        <p><strong>Fecha:</strong> ${moment(evento.start).format('DD/MM/YYYY HH:mm')}</p>
                        <p><strong>Tipo:</strong> ${props.tipo === 'examen' ? 'Examen' : 'Entrega'}</p>
                        <p><strong>Módulo:</strong> ${props.modulo}</p>
                        <p><strong>Curso:</strong> ${props.curso}</p>
                        <p><strong>Descripción:</strong> ${props.descripcion}</p>
                    `;
                    
                    document.getElementById('detalles-evento-cuerpo').innerHTML = contenido;
                    document.getElementById('btn-ver-detalles').href = `<?= BASE_URL ?>/examenes/detalles/${props.tipo === 'examen' ? 'examen' : 'entrega'}/1`;
                    
                    const modalDetalles = new bootstrap.Modal(document.getElementById('modal-detalles-evento'));
                    modalDetalles.show();
                }
            });
            
            calendario.render();
            
            // Aplicar filtros
            document.getElementById('btn-aplicar-filtros').addEventListener('click', function() {
                // Aquí iría la lógica para filtrar eventos
                // Esta es una simulación ya que no tenemos la API real
                
                const incluirExamenes = document.getElementById('filtro-examenes').checked;
                const incluirEntregas = document.getElementById('filtro-entregas').checked;
                
                // Simular actualización del calendario
                const alertaInfo = `<div class="alert alert-info alert-dismissible fade show" role="alert">
                    Filtros aplicados correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
                
                document.querySelector('.card-body').insertAdjacentHTML('afterbegin', alertaInfo);
            });
        });
    </script>
</body>
</html>
