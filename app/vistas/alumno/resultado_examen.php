<?php
/**
 * Vista de resultados del examen para alumnos
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

// Verificar que se han proporcionado los datos
if (!isset($intento) || !isset($examen)) {
    header("Location: " . BASE_URL . "/inicio");
    exit;
}

$titulo_pagina = 'Resultado del Examen: ' . $examen['titulo'];
require_once __DIR__ . '/../comunes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Encabezado con resultado -->
    <div class="card mb-4 <?= $intento['calificacion'] >= 5 ? 'border-success' : 'border-danger' ?>">
        <div class="card-header <?= $intento['calificacion'] >= 5 ? 'bg-success text-white' : 'bg-danger text-white' ?>">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="mb-0">
                        <i class="fas fa-<?= $intento['calificacion'] >= 5 ? 'check-circle' : 'times-circle' ?>"></i>
                        Resultado del Examen
                    </h3>
                    <h5 class="mb-0"><?= htmlspecialchars($examen['titulo']) ?></h5>
                    <small><?= htmlspecialchars($examen['nombre_curso']) ?> - <?= htmlspecialchars($examen['nombre_modulo']) ?></small>
                </div>
                <div class="col-auto text-end">
                    <div class="display-4 fw-bold"><?= number_format($intento['calificacion'], 1) ?></div>
                    <div><?= $intento['calificacion'] >= 5 ? 'APROBADO' : 'SUSPENSO' ?></div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-2">
                    <div class="mb-2">
                        <i class="fas fa-question-circle fa-2x text-primary"></i>
                    </div>
                    <h6>Preguntas</h6>
                    <div class="fs-4 fw-bold"><?= $intento['total_preguntas'] ?></div>
                </div>
                <div class="col-md-2">
                    <div class="mb-2">
                        <i class="fas fa-check fa-2x text-success"></i>
                    </div>
                    <h6>Correctas</h6>
                    <div class="fs-4 fw-bold text-success"><?= $intento['preguntas_correctas'] ?></div>
                </div>
                <div class="col-md-2">
                    <div class="mb-2">
                        <i class="fas fa-times fa-2x text-danger"></i>
                    </div>
                    <h6>Incorrectas</h6>
                    <div class="fs-4 fw-bold text-danger"><?= $intento['total_preguntas'] - $intento['preguntas_correctas'] ?></div>
                </div>
                <div class="col-md-2">
                    <div class="mb-2">
                        <i class="fas fa-percentage fa-2x text-info"></i>
                    </div>
                    <h6>Porcentaje</h6>
                    <div class="fs-4 fw-bold text-info">
                        <?= $intento['total_preguntas'] > 0 ? round(($intento['preguntas_correctas'] / $intento['total_preguntas']) * 100) : 0 ?>%
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-2">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                    <h6>Tiempo</h6>
                    <div class="fs-6 fw-bold text-warning">
                        <?php
                        $horas = floor($intento['tiempo_transcurrido'] / 3600);
                        $minutos = floor(($intento['tiempo_transcurrido'] % 3600) / 60);
                        $segundos = $intento['tiempo_transcurrido'] % 60;
                        echo sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
                        ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-2">
                        <i class="fas fa-calendar fa-2x text-secondary"></i>
                    </div>
                    <h6>Fecha</h6>
                    <div class="fs-6 fw-bold text-secondary">
                        <?= date('d/m/Y H:i', strtotime($intento['fecha_fin'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="mb-0">¿Qué quieres hacer ahora?</h6>
                </div>
                <div class="col-auto">
                    <div class="btn-group">
                        <a href="<?= BASE_URL ?>/inicio" class="btn btn-outline-primary">
                            <i class="fas fa-home"></i> Volver al Inicio
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="verDetalle()">
                            <i class="fas fa-eye"></i> Ver Detalle
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="imprimirResultado()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <?php if ($examen['intentos_permitidos'] == null || $intento['numero_intento'] < $examen['intentos_permitidos']): ?>
                            <a href="<?= BASE_URL ?>/examenes/realizar/<?= $examen['id_examen'] ?>" 
                               class="btn btn-warning" onclick="return confirm('¿Quieres realizar el examen otra vez?')">
                                <i class="fas fa-redo"></i> Repetir Examen
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de respuestas (inicialmente oculto) -->
    <div id="detalle-respuestas" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list-alt"></i> Detalle de Respuestas
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($respuestas_alumno)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No hay respuestas registradas.
                    </div>
                <?php else: ?>
                    <?php 
                    $pregunta_actual = null;
                    $numero_pregunta = 0;
                    $respuestas_agrupadas = [];
                    
                    // Agrupar respuestas por pregunta
                    foreach ($respuestas_alumno as $respuesta) {
                        $respuestas_agrupadas[$respuesta['id_pregunta']][] = $respuesta;
                    }
                    ?>
                    
                    <?php foreach ($respuestas_agrupadas as $id_pregunta => $respuestas_pregunta): ?>
                        <?php 
                        $primera_respuesta = $respuestas_pregunta[0];
                        $numero_pregunta++;
                        $es_correcta = false;
                        
                        // Para preguntas tipo test, verificar si todas las respuestas son correctas
                        if ($primera_respuesta['tipo_pregunta'] == 'test') {
                            $es_correcta = true;
                            foreach ($respuestas_pregunta as $resp) {
                                if (!$resp['correcta']) {
                                    $es_correcta = false;
                                    break;
                                }
                            }
                        }
                        ?>
                        
                        <div class="card mb-3 border-<?= $primera_respuesta['tipo_pregunta'] == 'test' ? ($es_correcta ? 'success' : 'danger') : 'info' ?>">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        Pregunta <?= $numero_pregunta ?>
                                        <span class="badge bg-<?= $primera_respuesta['tipo_pregunta'] == 'test' ? 'primary' : 'info' ?>">
                                            <?= ucfirst($primera_respuesta['tipo_pregunta']) ?>
                                        </span>
                                    </h6>
                                    <?php if ($primera_respuesta['tipo_pregunta'] == 'test'): ?>
                                        <span class="badge bg-<?= $es_correcta ? 'success' : 'danger' ?> fs-6">
                                            <i class="fas fa-<?= $es_correcta ? 'check' : 'times' ?>"></i>
                                            <?= $es_correcta ? 'Correcta' : 'Incorrecta' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning fs-6">
                                            <i class="fas fa-clock"></i> Pendiente de revisión
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Enunciado -->
                                <div class="mb-3">
                                    <strong>Enunciado:</strong>
                                    <div class="mt-1"><?= $primera_respuesta['enunciado'] ?></div>
                                </div>
                                
                                <?php if ($primera_respuesta['tipo_pregunta'] == 'test'): ?>
                                    <!-- Respuestas tipo test -->
                                    <div class="mb-3">
                                        <strong>Tu respuesta:</strong>
                                        <div class="mt-1">
                                            <?php foreach ($respuestas_pregunta as $resp): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked disabled>
                                                    <label class="form-check-label <?= $resp['correcta'] ? 'text-success fw-bold' : 'text-danger' ?>">
                                                        <?= htmlspecialchars($resp['texto_respuesta_correcta']) ?>
                                                        <?php if ($resp['correcta']): ?>
                                                            <i class="fas fa-check text-success ms-1"></i>
                                                        <?php else: ?>
                                                            <i class="fas fa-times text-danger ms-1"></i>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Respuesta de desarrollo -->
                                    <div class="mb-3">
                                        <strong>Tu respuesta:</strong>
                                        <div class="mt-1">
                                            <div class="border p-3 rounded bg-light">
                                                <?= nl2br(htmlspecialchars($primera_respuesta['texto_respuesta'] ?? 'Sin respuesta')) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Esta pregunta será evaluada manualmente por tu profesor.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mensaje de feedback -->
    <?php if ($intento['calificacion'] >= 5): ?>
        <div class="alert alert-success">
            <h5><i class="fas fa-trophy"></i> ¡Felicidades!</h5>
            <p class="mb-0">Has aprobado el examen con una calificación de <strong><?= number_format($intento['calificacion'], 1) ?></strong>. ¡Buen trabajo!</p>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> Resultado insuficiente</h5>
            <p class="mb-0">
                Tu calificación es de <strong><?= number_format($intento['calificacion'], 1) ?></strong>. 
                Te recomendamos revisar el material del curso y, si es posible, repetir el examen.
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
// Mostrar/ocultar detalle de respuestas
function verDetalle() {
    const detalle = document.getElementById('detalle-respuestas');
    const boton = event.target.closest('button');
    
    if (detalle.style.display === 'none') {
        detalle.style.display = 'block';
        boton.innerHTML = '<i class="fas fa-eye-slash"></i> Ocultar Detalle';
        
        // Scroll suave al detalle
        detalle.scrollIntoView({ behavior: 'smooth' });
    } else {
        detalle.style.display = 'none';
        boton.innerHTML = '<i class="fas fa-eye"></i> Ver Detalle';
    }
}

// Imprimir resultado
function imprimirResultado() {
    // Crear ventana de impresión
    const ventanaImpresion = window.open('', '_blank');
    
    const contenido = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Resultado del Examen - <?= htmlspecialchars($examen['titulo']) ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .resultado { background: <?= $intento['calificacion'] >= 5 ? '#d4edda' : '#f8d7da' ?>; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .stats { display: flex; justify-content: space-around; margin: 20px 0; }
                .stat { text-align: center; }
                .stat-value { font-size: 24px; font-weight: bold; }
                .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
                @media print { 
                    .no-print { display: none; }
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Resultado del Examen</h1>
                <h2><?= htmlspecialchars($examen['titulo']) ?></h2>
                <p><?= htmlspecialchars($examen['nombre_curso']) ?> - <?= htmlspecialchars($examen['nombre_modulo']) ?></p>
                <p><strong>Estudiante:</strong> <?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellidos']) ?></p>
                <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($intento['fecha_fin'])) ?></p>
            </div>
            
            <div class="resultado">
                <h2 style="text-align: center; margin: 0;">
                    Calificación: <?= number_format($intento['calificacion'], 1) ?>/10
                    (<?= $intento['calificacion'] >= 5 ? 'APROBADO' : 'SUSPENSO' ?>)
                </h2>
            </div>
            
            <div class="stats">
                <div class="stat">
                    <div>Preguntas Totales</div>
                    <div class="stat-value"><?= $intento['total_preguntas'] ?></div>
                </div>
                <div class="stat">
                    <div>Respuestas Correctas</div>
                    <div class="stat-value"><?= $intento['preguntas_correctas'] ?></div>
                </div>
                <div class="stat">
                    <div>Porcentaje de Acierto</div>
                    <div class="stat-value"><?= $intento['total_preguntas'] > 0 ? round(($intento['preguntas_correctas'] / $intento['total_preguntas']) * 100) : 0 ?>%</div>
                </div>
                <div class="stat">
                    <div>Tiempo Empleado</div>
                    <div class="stat-value">
                        <?php
                        $horas = floor($intento['tiempo_transcurrido'] / 3600);
                        $minutos = floor(($intento['tiempo_transcurrido'] % 3600) / 60);
                        $segundos = $intento['tiempo_transcurrido'] % 60;
                        echo sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p>AUTOEXAM2 - Sistema de Evaluación Online</p>
                <p>Documento generado el <?= date('d/m/Y H:i:s') ?></p>
            </div>
        </body>
        </html>
    `;
    
    ventanaImpresion.document.write(contenido);
    ventanaImpresion.document.close();
    ventanaImpresion.print();
}

// Limpiar localStorage del examen completado
<?php if (isset($_SESSION['examen_completado'])): ?>
localStorage.removeItem('examen_<?= $examen['id_examen'] ?>');
<?php unset($_SESSION['examen_completado']); ?>
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../comunes/footer.php'; ?>
