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
        <div class="col-lg-2 col-md-3 col-sm-6">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users-cog me-2"></i> Administradores</h5>
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h2 class="display-4 mb-0" id="contador-admin">
                                <?= isset($datos['estadisticas']['conteo']['administradores']['activos']) ? $datos['estadisticas']['conteo']['administradores']['activos'] : 0 ?>
                            </h2>
                            <small class="text-light">Activos</small>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-primary-dark fs-6">
                                <?= isset($datos['estadisticas']['conteo']['administradores']['inactivos']) ? $datos['estadisticas']['conteo']['administradores']['inactivos'] : 0 ?> inactivos
                            </div>
                            <div class="small text-light mt-1">
                                Total: <?= isset($datos['estadisticas']['conteo']['administradores']['total']) ? $datos['estadisticas']['conteo']['administradores']['total'] : 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-primary border-top-0">
                    <a href="<?= BASE_URL ?>/usuarios?rol=admin" class="text-white text-decoration-none">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" 
                            title="Crear nuevo administrador" onclick="location.href='<?= BASE_URL ?>/usuarios/crear?rol=admin'">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6">
            <div class="card text-white bg-info shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chalkboard-teacher me-2"></i> Profesores</h5>
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h2 class="display-4 mb-0" id="contador-profesores">
                                <?= isset($datos['estadisticas']['conteo']['profesores']['activos']) ? $datos['estadisticas']['conteo']['profesores']['activos'] : 0 ?>
                            </h2>
                            <small class="text-light">Activos</small>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-info-dark fs-6">
                                <?= isset($datos['estadisticas']['conteo']['profesores']['inactivos']) ? $datos['estadisticas']['conteo']['profesores']['inactivos'] : 0 ?> inactivos
                            </div>
                            <div class="small text-light mt-1">
                                Total: <?= isset($datos['estadisticas']['conteo']['profesores']['total']) ? $datos['estadisticas']['conteo']['profesores']['total'] : 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-info border-top-0">
                    <a href="<?= BASE_URL ?>/usuarios?rol=profesor" class="text-white text-decoration-none">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" 
                            title="Crear nuevo profesor" onclick="location.href='<?= BASE_URL ?>/usuarios/crear?rol=profesor'">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-user-graduate me-2"></i> Alumnos</h5>
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h2 class="display-4 mb-0" id="contador-alumnos">
                                <?= isset($datos['estadisticas']['conteo']['alumnos']['activos']) ? $datos['estadisticas']['conteo']['alumnos']['activos'] : 0 ?>
                            </h2>
                            <small class="text-light">Activos</small>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-success-dark fs-6">
                                <?= isset($datos['estadisticas']['conteo']['alumnos']['inactivos']) ? $datos['estadisticas']['conteo']['alumnos']['inactivos'] : 0 ?> inactivos
                            </div>
                            <div class="small text-light mt-1">
                                Total: <?= isset($datos['estadisticas']['conteo']['alumnos']['total']) ? $datos['estadisticas']['conteo']['alumnos']['total'] : 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-success border-top-0">
                    <a href="<?= BASE_URL ?>/usuarios?rol=alumno" class="text-white text-decoration-none">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" 
                            title="Crear nuevo alumno" onclick="location.href='<?= BASE_URL ?>/usuarios/crear?rol=alumno'">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6">
            <div class="card text-white bg-secondary shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-book-open me-2"></i> Cursos</h5>
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h2 class="display-4 mb-0" id="contador-cursos">
                                <?= isset($datos['estadisticas']['conteo']['cursos_activos']['activos']) ? $datos['estadisticas']['conteo']['cursos_activos']['activos'] : 0 ?>
                            </h2>
                            <small class="text-light">Activos</small>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-secondary-dark fs-6">
                                <?= isset($datos['estadisticas']['conteo']['cursos_activos']['inactivos']) ? $datos['estadisticas']['conteo']['cursos_activos']['inactivos'] : 0 ?> inactivos
                            </div>
                            <div class="small text-light mt-1">
                                Total: <?= isset($datos['estadisticas']['conteo']['cursos_activos']['total']) ? $datos['estadisticas']['conteo']['cursos_activos']['total'] : 0 ?>
                            </div>
                        </div>
                    </div>
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
        <div class="col-lg-2 col-md-3 col-sm-6">
            <div class="card text-white bg-warning shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-puzzle-piece me-2"></i> Módulos</h5>
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h2 class="display-4 mb-0" id="contador-modulos">
                                <?= isset($datos['estadisticas']['conteo']['modulos']['activos']) ? $datos['estadisticas']['conteo']['modulos']['activos'] : 0 ?>
                            </h2>
                            <small class="text-light">Activos</small>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-warning-dark fs-6">
                                <?= isset($datos['estadisticas']['conteo']['modulos']['inactivos']) ? $datos['estadisticas']['conteo']['modulos']['inactivos'] : 0 ?> inactivos
                            </div>
                            <div class="small text-light mt-1">
                                Total: <?= isset($datos['estadisticas']['conteo']['modulos']['total']) ? $datos['estadisticas']['conteo']['modulos']['total'] : 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-warning border-top-0">
                    <a href="<?= BASE_URL ?>/modulos" class="text-white text-decoration-none">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" 
                            title="Crear nuevo módulo" onclick="location.href='<?= BASE_URL ?>/modulos/nuevo'">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6">
            <div class="card text-white bg-danger shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-file-alt me-2"></i> Exámenes</h5>
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h2 class="display-4 mb-0" id="contador-examenes">
                                <?= isset($datos['estadisticas']['conteo']['examenes']['activos']) ? $datos['estadisticas']['conteo']['examenes']['activos'] : 0 ?>
                            </h2>
                            <small class="text-light">Activos</small>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-danger-dark fs-6">
                                <?= isset($datos['estadisticas']['conteo']['examenes']['inactivos']) ? $datos['estadisticas']['conteo']['examenes']['inactivos'] : 0 ?> inactivos
                            </div>
                            <div class="small text-light mt-1">
                                Total: <?= isset($datos['estadisticas']['conteo']['examenes']['total']) ? $datos['estadisticas']['conteo']['examenes']['total'] : 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between bg-danger border-top-0">
                    <a href="<?= BASE_URL ?>/examenes" class="text-white text-decoration-none">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" 
                            title="Crear nuevo examen" onclick="location.href='<?= BASE_URL ?>/examenes/nuevo'">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas y acciones recientes -->
    <div class="row">
        <div class="col-md-7 mb-4">
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
                    <canvas id="graficoEstadisticas" height="340"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Acciones recientes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="acciones-recientes">
                        <?php if (!empty($datos['actividad_reciente'])): ?>
                            <?php foreach($datos['actividad_reciente'] as $actividad): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($actividad['descripcion']) ?></h6>
                                        <small><?= date('d/m/Y H:i', strtotime($actividad['fecha'])) ?></small>
                                    </div>
                                    <p class="mb-1">
                                        <span class="badge bg-primary"><?= ucfirst(str_replace('_', ' ', $actividad['accion'])) ?></span>
                                        <?php if (!empty($actividad['nombre'])): ?>
                                            por <?= htmlspecialchars($actividad['nombre'] . ' ' . $actividad['apellidos']) ?>
                                        <?php endif; ?>
                                    </p>
                                    <small class="text-muted"><?= ucfirst($actividad['modulo']) ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>No hay actividad reciente registrada
                            </div>
                        <?php endif; ?>
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
            <div class="card shadow-sm h-100 d-flex flex-column">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i> Accesos rápidos</h5>
                </div>
                <div class="card-body flex-grow-1 d-flex align-items-center">
                    <div class="row g-3 w-100">
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/usuarios/crear" class="btn btn-primary w-100 d-flex flex-column align-items-center justify-content-center py-3" style="min-height: 150px;">
                                <i class="fas fa-user-plus fa-2x mb-2"></i>
                                <span class="small">Nuevo usuario</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/cursos/nuevo" class="btn btn-info text-white w-100 d-flex flex-column align-items-center justify-content-center py-3" style="min-height: 145px;">
                                <i class="fas fa-book fa-2x mb-2"></i>
                                <span class="small">Nuevo curso</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/modulos/crear" class="btn btn-success w-100 d-flex flex-column align-items-center justify-content-center py-3" style="min-height: 145px;">
                                <i class="fas fa-puzzle-piece fa-2x mb-2"></i>
                                <span class="small">Nuevo módulo</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/configuracion" class="btn btn-secondary w-100 d-flex flex-column align-items-center justify-content-center py-3" style="min-height: 145px;">
                                <i class="fas fa-cogs fa-2x mb-2"></i>
                                <span class="small">Configuración</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/mantenimiento/backup" class="btn btn-warning w-100 d-flex flex-column align-items-center justify-content-center py-3" style="min-height: 145px;">
                                <i class="fas fa-database fa-2x mb-2"></i>
                                <span class="small">Backup</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/logs" class="btn btn-danger w-100 d-flex flex-column align-items-center justify-content-center py-3" style="min-height: 145px;">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <span class="small">Ver logs</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/sesiones-activas" class="btn btn-dark w-100 d-flex flex-column align-items-center justify-content-center py-3" style="min-height: 145px;">
                                <i class="fas fa-users-cog fa-2x mb-2"></i>
                                <span class="small">Sesiones</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="<?= BASE_URL ?>/mantenimiento" class="btn btn-light border w-100 d-flex flex-column align-items-center justify-content-center py-3" style="min-height: 145px;">
                                <i class="fas fa-tools fa-2x mb-2"></i>
                                <span class="small">Mantenimiento</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 d-flex flex-column">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-server me-2"></i> Estado del sistema</h5>
                </div>
                <div class="card-body flex-grow-1 d-flex align-items-center">
                    <div class="list-group w-100" id="estado-sistema">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-database me-2"></i> Base de datos
                            </div>
                            <?php 
                            $bd = $datos['estado_sistema']['base_datos'];
                            $badgeClass = $bd['estado'] === 'operativa' ? 'bg-success' : 'bg-danger';
                            ?>
                            <span class="badge <?= $badgeClass ?>" id="estado-bd"><?= ucfirst($bd['estado']) ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-hdd me-2"></i> Almacenamiento
                                <small class="text-muted d-block">(<?= $datos['estado_sistema']['almacenamiento']['libre'] ?>% libre)</small>
                            </div>
                            <?php 
                            $almacen = $datos['estado_sistema']['almacenamiento'];
                            $badgeClass = $almacen['estado'] === 'ok' ? 'bg-success' : ($almacen['estado'] === 'critico' ? 'bg-danger' : 'bg-warning');
                            ?>
                            <span class="badge <?= $badgeClass ?>" id="estado-almacenamiento"><?= ucfirst($almacen['estado']) ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-users me-2"></i> Sesiones activas
                            </div>
                            <span class="badge bg-info" id="sesiones-activas"><?= $datos['estado_sistema']['sesiones_activas'] ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user-clock me-2"></i> Usuarios online
                                <small class="text-muted d-block">(últimos 15 min)</small>
                            </div>
                            <span class="badge bg-primary" id="usuarios-online"><?= $datos['estado_sistema']['usuarios_online'] ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-alt me-2"></i> Exámenes activos
                            </div>
                            <span class="badge bg-secondary" id="examenes-activos"><?= $datos['estado_sistema']['examenes_activos'] ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-shield-alt me-2"></i> Seguridad
                                <small class="text-muted d-block">(<?= $datos['estado_sistema']['intentos_fallidos'] ?> intentos fallidos hoy)</small>
                            </div>
                            <?php 
                            $intentos = $datos['estado_sistema']['intentos_fallidos'];
                            $badgeClass = $intentos === 0 ? 'bg-success' : ($intentos < 5 ? 'bg-warning' : 'bg-danger');
                            ?>
                            <span class="badge <?= $badgeClass ?>" id="estado-seguridad"><?= $intentos === 0 ? 'Normal' : 'Alerta' ?></span>
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
