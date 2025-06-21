<?php
/**
 * Vista de exámenes disponibles para alumnos
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 21/06/2025
 */

// Verificar sesión y permisos de alumno
if (!isset($_SESSION['usuario_logueado']) || $_SESSION['rol'] !== 'alumno') {
    header("Location: " . BASE_URL . "/autenticacion/login");
    exit;
}

$titulo_pagina = 'Mis Exámenes';
require_once __DIR__ . '/../comunes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">📝 Mis Exámenes</h1>
            <p class="text-muted">Exámenes disponibles y historial de intentos</p>
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

    <div class="row">
        <!-- Exámenes disponibles -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list"></i> 
                        Exámenes Disponibles (<?= count($examenes_disponibles) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($examenes_disponibles)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay exámenes disponibles</h5>
                            <p class="text-muted">No tienes exámenes pendientes por realizar en este momento.</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($examenes_disponibles as $examen): ?>
                                <?php 
                                $puede_realizar = $examen['intentos_permitidos'] == null || $examen['intentos_realizados'] < $examen['intentos_permitidos'];
                                $tiempo_restante = '';
                                if ($examen['fecha_fin']) {
                                    $tiempo_fin = strtotime($examen['fecha_fin']);
                                    $tiempo_actual = time();
                                    if ($tiempo_fin > $tiempo_actual) {
                                        $diferencia = $tiempo_fin - $tiempo_actual;
                                        $dias = floor($diferencia / 86400);
                                        $horas = floor(($diferencia % 86400) / 3600);
                                        if ($dias > 0) {
                                            $tiempo_restante = "$dias días, $horas horas";
                                        } else {
                                            $tiempo_restante = "$horas horas";
                                        }
                                    }
                                }
                                ?>
                                <div class="col-md-6">
                                    <div class="card border-left-primary h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">
                                                    <?= htmlspecialchars($examen['titulo']) ?>
                                                </h6>
                                                <?php if ($examen['total_preguntas'] > 0): ?>
                                                    <span class="badge bg-primary"><?= $examen['total_preguntas'] ?> preguntas</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="card-text text-muted small mb-2">
                                                <strong><?= htmlspecialchars($examen['nombre_curso']) ?></strong><br>
                                                <?= htmlspecialchars($examen['nombre_modulo']) ?>
                                            </p>
                                            
                                            <?php if (!empty($examen['descripcion'])): ?>
                                                <p class="card-text small mb-3">
                                                    <?= htmlspecialchars(substr($examen['descripcion'], 0, 100)) ?>
                                                    <?= strlen($examen['descripcion']) > 100 ? '...' : '' ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <!-- Información del examen -->
                                            <div class="small text-muted mb-3">
                                                <?php if ($examen['duracion_minutos']): ?>
                                                    <div><i class="fas fa-clock"></i> Duración: <?= $examen['duracion_minutos'] ?> minutos</div>
                                                <?php endif; ?>
                                                
                                                <?php if ($examen['intentos_permitidos']): ?>
                                                    <div>
                                                        <i class="fas fa-redo"></i> 
                                                        Intentos: <?= $examen['intentos_realizados'] ?>/<?= $examen['intentos_permitidos'] ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div>
                                                        <i class="fas fa-redo"></i> 
                                                        Intentos: <?= $examen['intentos_realizados'] ?> (ilimitados)
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($tiempo_restante): ?>
                                                    <div><i class="fas fa-hourglass-half"></i> Tiempo restante: <?= $tiempo_restante ?></div>
                                                <?php endif; ?>
                                                
                                                <?php if ($examen['fecha_inicio']): ?>
                                                    <div><i class="fas fa-calendar-alt"></i> Disponible desde: <?= date('d/m/Y H:i', strtotime($examen['fecha_inicio'])) ?></div>
                                                <?php endif; ?>
                                                
                                                <?php if ($examen['fecha_fin']): ?>
                                                    <div><i class="fas fa-calendar-times"></i> Disponible hasta: <?= date('d/m/Y H:i', strtotime($examen['fecha_fin'])) ?></div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Botones de acción -->
                                            <div class="d-flex gap-2">
                                                <?php if ($puede_realizar): ?>
                                                    <a href="<?= BASE_URL ?>/examenes/realizar/<?= $examen['id_examen'] ?>" 
                                                       class="btn btn-primary btn-sm flex-grow-1">
                                                        <i class="fas fa-play"></i> 
                                                        <?= $examen['intentos_realizados'] > 0 ? 'Repetir' : 'Comenzar' ?>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm flex-grow-1" disabled>
                                                        <i class="fas fa-ban"></i> Sin intentos disponibles
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($examen['intentos_realizados'] > 0): ?>
                                                    <button class="btn btn-outline-info btn-sm" 
                                                            onclick="verHistorialExamen(<?= $examen['id_examen'] ?>)">
                                                        <i class="fas fa-history"></i> Historial
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Últimos resultados -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line"></i> Últimos Resultados
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($historial_intentos)): ?>
                        <p class="text-muted text-center">No has realizado exámenes aún</p>
                    <?php else: ?>
                        <?php foreach (array_slice($historial_intentos, 0, 5) as $intento): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div class="flex-grow-1">
                                    <div class="fw-medium small"><?= htmlspecialchars($intento['titulo_examen']) ?></div>
                                    <div class="text-muted small"><?= date('d/m/Y', strtotime($intento['fecha_fin'])) ?></div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?= $intento['calificacion'] >= 5 ? 'success' : 'danger' ?>">
                                        <?= number_format($intento['calificacion'], 1) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($historial_intentos) > 5): ?>
                            <div class="text-center mt-3">
                                <a href="<?= BASE_URL ?>/examenes/historial-examenes" class="btn btn-outline-primary btn-sm">
                                    Ver todo el historial
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Mis Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <?php 
                    $total_examenes = count($historial_intentos);
                    $aprobados = 0;
                    $suma_calificaciones = 0;
                    
                    foreach ($historial_intentos as $intento) {
                        if ($intento['calificacion'] >= 5) $aprobados++;
                        $suma_calificaciones += $intento['calificacion'];
                    }
                    
                    $promedio = $total_examenes > 0 ? $suma_calificaciones / $total_examenes : 0;
                    $porcentaje_aprobados = $total_examenes > 0 ? ($aprobados / $total_examenes) * 100 : 0;
                    ?>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <div class="h4 mb-1"><?= $total_examenes ?></div>
                                <div class="small text-muted">Exámenes realizados</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1"><?= $aprobados ?></div>
                            <div class="small text-muted">Aprobados</div>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <div class="h4 mb-1 text-primary"><?= number_format($promedio, 1) ?></div>
                                <div class="small text-muted">Promedio</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1 text-success"><?= number_format($porcentaje_aprobados, 0) ?>%</div>
                            <div class="small text-muted">Tasa de aprobación</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para historial de examen -->
<div class="modal fade" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Historial del Examen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoHistorial">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
                    <p class="mt-2">Cargando historial...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Ver historial de un examen específico
function verHistorialExamen(idExamen) {
    const modal = new bootstrap.Modal(document.getElementById('modalHistorial'));
    modal.show();
    
    fetch(`<?= BASE_URL ?>/examenes/historial-examen/${idExamen}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarHistorialExamen(data.intentos);
        } else {
            document.getElementById('contenidoHistorial').innerHTML = 
                '<div class="alert alert-danger">Error al cargar el historial</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('contenidoHistorial').innerHTML = 
            '<div class="alert alert-danger">Error de conexión</div>';
    });
}

// Mostrar historial de intentos
function mostrarHistorialExamen(intentos) {
    let html = '';
    
    if (intentos.length === 0) {
        html = '<div class="alert alert-info">No hay intentos registrados para este examen.</div>';
    } else {
        html = '<div class="table-responsive"><table class="table table-striped">';
        html += '<thead><tr><th>Intento</th><th>Fecha</th><th>Tiempo</th><th>Calificación</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>';
        
        intentos.forEach((intento, index) => {
            const fecha = new Date(intento.fecha_fin).toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            const tiempo = formatearTiempo(intento.tiempo_transcurrido);
            const calificacion = parseFloat(intento.calificacion).toFixed(1);
            const estado = calificacion >= 5 ? 'Aprobado' : 'Suspenso';
            const colorEstado = calificacion >= 5 ? 'success' : 'danger';
            
            html += `<tr>
                <td>${index + 1}</td>
                <td>${fecha}</td>
                <td>${tiempo}</td>
                <td><span class="badge bg-${colorEstado}">${calificacion}</span></td>
                <td><span class="badge bg-${colorEstado}">${estado}</span></td>
                <td>
                    <a href="<?= BASE_URL ?>/examenes/resultado/${intento.id_intento}" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i> Ver detalle
                    </a>
                </td>
            </tr>`;
        });
        
        html += '</tbody></table></div>';
    }
    
    document.getElementById('contenidoHistorial').innerHTML = html;
}

// Formatear tiempo en segundos a HH:MM:SS
function formatearTiempo(segundos) {
    const horas = Math.floor(segundos / 3600);
    const minutos = Math.floor((segundos % 3600) / 60);
    const segs = segundos % 60;
    
    return `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segs).padStart(2, '0')}`;
}
</script>

<?php require_once __DIR__ . '/../comunes/footer.php'; ?>
