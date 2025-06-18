<?php
/**
 * Dashboard de Administrador - AUTOEXAM2
 * 
 * Panel principal de control para administradores
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Incluir cabecera
require_once APP_PATH . '/vistas/parciales/head_admin.php';

// Incluir barra de navegación
require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
?>

<div class="container-fluid py-4">
    <!-- Cabecera y bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h2 class="card-title mb-0">Panel de administración</h2>
                            <p class="text-muted mb-0">
                                Bienvenido/a, <?= htmlspecialchars($datos['usuario']['nombre'] . ' ' . $datos['usuario']['apellidos']) ?>
                            </p>
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-primary">
                                <i class="fas fa-calendar-alt me-1"></i> <?= date('d/m/Y') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteo de usuarios -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users-cog me-2"></i> Administradores</h5>
                    <h2 class="display-4 mb-0" id="contador-admin">
                        <div class="spinner-border spinner-border-sm text-light" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-primary border-top-0">
                    <a href="<?= BASE_URL ?>/usuarios/administradores" class="text-white text-decoration-none">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" 
                            title="Crear nuevo administrador" onclick="location.href='<?= BASE_URL ?>/usuarios/crear?rol=admin'">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chalkboard-teacher me-2"></i> Profesores</h5>
                    <h2 class="display-4 mb-0" id="contador-profesores">
                        <div class="spinner-border spinner-border-sm text-light" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-info border-top-0">
                    <a href="<?= BASE_URL ?>/usuarios/profesores" class="text-white text-decoration-none">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" 
                            title="Crear nuevo profesor" onclick="location.href='<?= BASE_URL ?>/usuarios/crear?rol=profesor'">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-user-graduate me-2"></i> Alumnos</h5>
                    <h2 class="display-4 mb-0" id="contador-alumnos">
                        <div class="spinner-border spinner-border-sm text-light" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-success border-top-0">
                    <a href="<?= BASE_URL ?>/usuarios/alumnos" class="text-white text-decoration-none">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" 
                            title="Crear nuevo alumno" onclick="location.href='<?= BASE_URL ?>/usuarios/crear?rol=alumno'">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-book-open me-2"></i> Cursos Activos</h5>
                    <h2 class="display-4 mb-0" id="contador-cursos">
                        <div class="spinner-border spinner-border-sm text-light" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-secondary border-top-0">
                    <a href="<?= BASE_URL ?>/cursos" class="text-white text-decoration-none">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" 
                            title="Crear nuevo curso" onclick="location.href='<?= BASE_URL ?>/cursos/nuevo'">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas y acciones recientes -->
    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Estadísticas globales</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                    id="periodoEstadisticas" data-bs-toggle="dropdown" aria-expanded="false">
                                Último mes
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="periodoEstadisticas">
                                <li><a class="dropdown-item" href="#">Última semana</a></li>
                                <li><a class="dropdown-item" href="#">Último mes</a></li>
                                <li><a class="dropdown-item" href="#">Último trimestre</a></li>
                                <li><a class="dropdown-item" href="#">Último año</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="graficoEstadisticas" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Acciones recientes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="acciones-recientes">
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Cargando actividad reciente...</h6>
                            </div>
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= BASE_URL ?>/actividad" class="text-decoration-none">
                        Ver todo el historial <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos directos y estado del sistema -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i> Accesos rápidos</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/usuarios/crear" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-user-plus fa-2x mb-2"></i>
                                <span>Nuevo usuario</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/cursos/nuevo" class="btn btn-info text-white w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-book fa-2x mb-2"></i>
                                <span>Nuevo curso</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/modulos/crear" class="btn btn-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-puzzle-piece fa-2x mb-2"></i>
                                <span>Nuevo módulo</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/configuracion" class="btn btn-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-cogs fa-2x mb-2"></i>
                                <span>Configuración</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/mantenimiento/backup" class="btn btn-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-database fa-2x mb-2"></i>
                                <span>Backup</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/logs" class="btn btn-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <span>Ver logs</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/sesiones-activas" class="btn btn-dark w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-users-cog fa-2x mb-2"></i>
                                <span>Sesiones</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/mantenimiento" class="btn btn-light border w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-tools fa-2x mb-2"></i>
                                <span>Mantenimiento</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-server me-2"></i> Estado del sistema</h5>
                </div>
                <div class="card-body">
                    <div class="list-group" id="estado-sistema">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-envelope me-2"></i> Servidor SMTP
                            </div>
                            <span class="badge bg-success" id="estado-smtp">Operativo</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-database me-2"></i> Base de datos
                            </div>
                            <span class="badge bg-success" id="estado-bd">Operativa</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-hdd me-2"></i> Almacenamiento
                                <small class="text-muted">(75% libre)</small>
                            </div>
                            <span class="badge bg-info" id="estado-almacenamiento">Ok</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-archive me-2"></i> Último backup
                            </div>
                            <span class="badge bg-warning text-dark" id="estado-backup">Hace 3 días</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-robot me-2"></i> Módulos IA
                            </div>
                            <span class="badge bg-success" id="estado-ia">Activos</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-shield-alt me-2"></i> Seguridad
                                <small class="text-muted">(intentos fallidos)</small>
                            </div>
                            <span class="badge bg-success" id="estado-seguridad">Normal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir pie de página
require_once APP_PATH . '/vistas/parciales/footer_admin.php';

// Incluir scripts
require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
?>

<script>
    // Cargar datos para los contadores
    document.addEventListener('DOMContentLoaded', function() {
        // Simular carga de datos (aquí irían las llamadas AJAX reales)
        setTimeout(() => {
            document.getElementById('contador-admin').innerHTML = '3';
            document.getElementById('contador-profesores').innerHTML = '12';
            document.getElementById('contador-alumnos').innerHTML = '145';
            document.getElementById('contador-cursos').innerHTML = '8';
            
            // Cargar acciones recientes
            const accionesRecientes = document.getElementById('acciones-recientes');
            accionesRecientes.innerHTML = `
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Nuevo usuario creado</h6>
                        <small class="text-muted">Hace 30 min</small>
                    </div>
                    <p class="mb-1 small">María López (alumno) ha sido registrado</p>
                    <small>Por: Admin</small>
                </div>
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Curso modificado</h6>
                        <small class="text-muted">Hace 2 horas</small>
                    </div>
                    <p class="mb-1 small">Matemáticas 3º ESO - Añadido nuevo módulo</p>
                    <small>Por: Admin</small>
                </div>
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Backup realizado</h6>
                        <small class="text-muted">Hace 3 días</small>
                    </div>
                    <p class="mb-1 small">Backup automático completo: BD y archivos</p>
                    <small>Por: Sistema</small>
                </div>
            `;
            
            // Inicializar gráfico de estadísticas
            const ctx = document.getElementById('graficoEstadisticas').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Exámenes creados',
                        data: [12, 19, 3, 5, 2, 3],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    }, {
                        label: 'Exámenes realizados',
                        data: [7, 11, 5, 8, 3, 7],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }, 1000);
    });
</script>
